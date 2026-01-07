<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminUserCreatedMail;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * GET /users
     */
    public function index()
    {
        $users = User::latest()->paginate(20);
        return UserResource::collection($users);
    }

    /**
     * POST /users
     * - Public : inscription classique
     * - Admin  : ajout rapide utilisateur
     */
    public function store(RegisterRequest $request)
    {
        // ❌ Empêcher la création d’admin par un non-admin
        if ($request->account_type === 'admin') {
            return response()->json([
                'errors' => 'Impossible de créer un administrateur.'
            ], 403);
        }

        $data = $request->validated();

        /**
         * ==========================
         * 👤 CAS UTILISATEUR PUBLIC
         * ==========================
         */
        $user = User::create([
            'full_name'            => $data['full_name'] ?? null,
            'company_name'         => $data['company_name'] ?? null,
            'email'                => $data['email'],
            'phone'                => $data['phone'] ?? null,
            'password'             => Hash::make($data['password']),
            'account_type'         => $data['account_type'],
            'account_category_id'  => $data['account_category_id'] ?? null,

            // Public → compte inactif jusqu’au paiement
            'activated'            => true,
            'payment_status'       => 'none',
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès. Veuillez effectuer le paiement pour activer votre compte.',
            'data'    => new UserResource($user),
        ], 201);
    }


     // -------------------- AJOUT PAR ADMIN --------------------
    public function storeAdmin(RegisterRequest $request)
    {
        $data = $request->validated();
        $plainPassword = $data['password'];

        try {
            // 1️⃣ Création de l'utilisateur
            $user = User::create([
                'email'        => $data['email'],
                'account_type' => $data['account_type'],
                'full_name'    => $data['full_name'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'phone'        => $data['phone'] ?? null,
                'password'     => Hash::make($plainPassword),
                'activated'    => true,
                'payment_status' => 'none',
            ]);

            // 2️⃣ Envoi de l’email
            Mail::to($user->email)->send(new AdminUserCreatedMail($user, $plainPassword));

        } catch (\Exception $e) {
            \Log::error('Erreur création utilisateur/admin: '.$e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création de l’utilisateur ou de l’envoi du mail.',
                'error'   => $e->getMessage(),
                'data'    => isset($user) ? new UserResource($user) : null,
            ], 500);
        }

        return response()->json([
            'message' => 'Utilisateur ajouté avec succès. Un email lui a été envoyé.',
            'data'    => new UserResource($user),
        ], 201);
    }



    /**
     * GET /users/{id}
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return new UserResource($user);
    }

    /**
     * PUT /users/{id}
     */

    public function update(UpdateProfileRequest $request, string $id)
    {
        $user = User::findOrFail($id);
        $currentUser = $request->user();
        $data = $request->validated();

        \Log::info('UpdateProfile - données reçues:', $data);

        // Copie pour comparer les changements
        $originalUser = $user->replicate();

        // ------------------ AVATAR ------------------
        if ($request->hasFile('avatar_url')) {
            if (!empty($user->avatar_url)) {
                $oldPath = str_replace(asset('storage') . '/', '', $user->avatar_url);
                Storage::disk('public')->delete($oldPath);
            }

            $avatar = $request->file('avatar_url');
            $extension = $avatar->extension();
            $filename = hash('sha256', time() . $user->id) . '.' . $extension;

            $avatar->storeAs('avatars', $filename, 'public');
            $data['avatar_url'] = asset('storage/avatars/' . $filename);
        }

        // ------------------ DOCUMENTS ------------------
        $documentPaths = [];
        if ($request->hasFile('documents_urls')) {
            if (!empty($user->documents_urls) && is_array($user->documents_urls)) {
                foreach ($user->documents_urls as $oldFileUrl) {
                    $oldPath = str_replace(asset('storage') . '/', '', $oldFileUrl);
                    Storage::disk('public')->delete($oldPath);
                }
            }

            foreach ($request->file('documents_urls') as $document) {
                $extension = $document->extension();
                $filename = hash('sha256', time() . uniqid()) . "." . $extension;
                $document->storeAs('user_documents', $filename, 'public');
                $documentPaths[] = asset('storage/user_documents/' . $filename);
            }
            $data['documents_urls'] = $documentPaths;
        }

        // ------------------ MOT DE PASSE ------------------
        if (!empty($data['password'])) {
            if (!isset($data['current_password']) || !\Hash::check($data['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'L’ancien mot de passe est incorrect.'
                ], 422);
            }
            $data['password'] = \Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        unset($data['current_password']);

        // ------------------ COLONNE ACTIVATED ------------------
        if (!$currentUser->is_admin && isset($data['activated'])) {
            // Le propriétaire ne peut pas modifier 'activated'
            unset($data['activated']);
        }

        // ------------------ MISE À JOUR ------------------
        $user->update($data);

        // ------------------ DÉTECTION DES CHANGEMENTS IMPORTANTS ------------------
        $fieldsRequiringValidation = [
            'ifu',
            'adresse',
            'ville',
            'account_category_id',
            'documents_urls',
        ];

        $changesDetected = false;
        foreach ($fieldsRequiringValidation as $field) {
            if ($originalUser->$field !== $user->$field) {
                $changesDetected = true;
                break;
            }
        }

        if ($changesDetected) {
            $user->verified_documents = false;
            $user->save();

            // Notification aux admins uniquement
            $admins = \App\Models\User::where('account_type', 'admin')->get();
            foreach ($admins as $admin) {
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'profile_update',
                    'payload' => [
                        'note' => "L’utilisateur {$user->email} a soumis une mise à jour nécessitant une validation.",
                        'user_id' => $user->id,
                        'timestamp' => now()->toDateTimeString(),
                        'changed_fields' => $user->getChanges(),
                    ],
                    'read' => false,
                ]);
            }
        }

        return response()->json([
            'message' => 'Utilisateur mis à jour.',
            'data' => new UserResource($user),
        ]);
    }



    /**
     * DELETE /users/{id}
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    /* =======================================================
     *               AUTHENTIFICATION
     * ======================================================= */

    /**
     * POST /login
     */
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->validated())) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken("auth")->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'token'   => $token,
            'data'    => new UserResource($user),
        ]);
    }

    /**
     * POST /logout
     */
    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    /**
     * GET /profile
     */
    public function profile()
    {
        return new UserResource(Auth::user());
    }

    /**
     * POST /users/documents
     */
    public function uploadDocuments(Request $request)
    {
        $request->validate([
            'documents.*' => 'required|file|max:5000|mimes:png,jpg,jpeg,pdf'
        ]);

        $user = Auth::user();
        $paths = [];

        foreach ($request->file('documents') as $file) {
            $paths[] = Storage::disk('public')->put('documents', $file);
        }

        $user->documents_urls = $paths;
        $user->verified_documents = false;
        $user->save();

        return response()->json([
            'message'   => 'Documents envoyés.',
            'documents' => $paths
        ]);
    }
}
