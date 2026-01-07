<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationCollection;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié'
            ], 401);
        }

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return new NotificationCollection($notifications);
    }

    /**
     * Store a newly created notification.
     * (Généralement utilisé par le système interne, pas par un user.)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'  => 'nullable|exists:users,id', // destinataire
            'type'     => 'required|in:paiement,demande,signalement,admin_action,compte_validé,compte_rejeté',
            'payload'  => 'nullable|array',
        ]);

        $user = null;
        if (!empty($validated['user_id'])) {
            $user = \App\Models\User::findOrFail($validated['user_id']);
        }

        $payload = $validated['payload'] ?? [];

        // ⚡ Gestion spécifique pour compte_validé / compte_rejeté
        if (in_array($validated['type'], ['compte_validé', 'compte_rejeté']) && $user) {
            $user->verified_documents = $validated['type'] === 'compte_validé';
            $user->save();

            $payload = array_merge($payload, [
                'title' => $validated['type'] === 'compte_validé' ? 'Compte Validé' : 'Compte Rejeté',
                'note' => $validated['type'] === 'compte_validé'
                    ? 'Votre compte a été validé par africaLocation.'
                    : 'Votre compte a été rejeté. Veuillez vérifier vos documents.',
                'timestamp' => now()->toDateTimeString(),
                'sender_id' => auth()->id(), // admin ou utilisateur connecté
            ]);
        } elseif ($validated['type'] === 'demande' && !$user) {
            $payload = array_merge($payload, [
                'title' => 'Nouvelle demande',
                'note' => "Une demande a été soumise par un utilisateur non connecté.",
                'timestamp' => now()->toDateTimeString(),
                'contact' => "email/numero",
                'sender_id' => $request->ip(),
            ]);
        } else {
            // Pour les autres types ou utilisateurs connectés
            if (auth()->check()) {
                $payload['sender_id'] = auth()->id();
            }
        }

        $notification = Notification::create([
            'user_id' => $user?->id,
            'type'    => $validated['type'],
            'payload' => $payload,
        ]);

        return new NotificationResource($notification);
    }

    public function storeSignalement(Request $request)
    {
        $validated = $request->validate([
            'note'    => 'required|string|min:5',
            'payload' => 'nullable|array',
        ]);

        $authUser = $request->user(); // peut être null

        // 🔥 Payload de base
        $payload = array_merge($validated['payload'] ?? [], [
            'title'     => 'Nouveau signalement',
            'note'      => $validated['note'],
            'timestamp' => now()->toDateTimeString(),
            'platform'  => 'mobile',
            'ip'        => $request->ip(),
        ]);

        // 🔹 Si utilisateur connecté → on garde les infos
        if ($authUser) {
            $payload['sender'] = [
                'id'    => $authUser->id,
                'name'  => $authUser->account_type === 'company'
                    ? $authUser->company_name
                    : $authUser->full_name,
                'email' => $authUser->email,
            ];
            $payload['sender_id'] = $authUser->id;
        } else {
            // 🔹 Utilisateur non connecté
            $payload['sender'] = [
                'type' => 'anonyme',
            ];
        }


        $admins = \App\Models\User::where('account_type', 'admin')->get();
        foreach ($admins as $admin) {
            // 🔥 Signalement → DESTINÉ AUX ADMINS
            $notification = Notification::create([
                'user_id' => $admin->id, // 👈 null = global / admin
                'type'    => 'signalement',
                'payload' => $payload,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Signalement envoyé avec succès',
            'data'    => new NotificationResource($notification),
        ], 201);
    }


    /**
     * Display the specified notification.
     */
    public function show(string $id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->findOrFail($id);

        return new NotificationResource($notification);
    }


    public function getLastVerificationNotification($userId)
    {
        // Les types d'examens
        $types = ['compte_validé', 'compte_rejeté'];

        $notification = Notification::where('user_id', $userId)
            ->whereIn('type', $types)
            ->latest()
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $notification,
            "has_verification" => $notification !== null
        ]);
    }




    /**
     * Mark a notification as read.
     */
    public function update(Request $request, string $id)
    {
        // On récupère la notification pour l'utilisateur connecté
        $notification = Notification::findOrFail($id);

        // Validation des champs que l'on peut modifier
        $validated = $request->validate([
            'type'    => 'nullable|in:paiement,demande,signalement,admin_action,compte_validé,compte_rejeté',
            'payload' => 'nullable|array',
            'read'    => 'nullable|boolean',
        ]);

        // ⚡ Vérifier si l'utilisateur connecté est admin
        $isAdmin = auth()->user()->account_type === 'admin';

        // Si admin : il peut modifier type, payload et read
        if ($isAdmin) {
            if (isset($validated['type'])) {
                $notification->type = $validated['type'];
            }

            if (isset($validated['payload'])) {
                $notification->payload = $validated['payload'];
            }

            if (isset($validated['read'])) {
                $notification->read = $validated['read'];
            }
        } else {
            // Si ce n'est pas admin : l'utilisateur peut seulement marquer la notification comme lue
            $notification->read = true;
        }

        $notification->save();

        // ⚡ Synchroniser verified_documents si la notification concerne compte_validé / compte_rejeté
        if (in_array($notification->type, ['compte_validé', 'compte_rejeté'])) {
            $user = $notification->user;
            $user->verified_documents = $notification->type === 'compte_validé';
            $user->save();
        }

        return new NotificationResource($notification);
    }



    /**
     * Remove the specified notification.
     */
    public function destroy(string $id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'message' => 'Notification supprimée avec succès.',
        ]);
    }
}
