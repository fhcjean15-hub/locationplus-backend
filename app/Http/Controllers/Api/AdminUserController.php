<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\UserResource;

class AdminUserController extends Controller
{
    public function updateActivated(Request $request, User $user)
    {
        // ------------------ VALIDATION ------------------
        $data = $request->validate([
            'activated' => 'required|in:0,1,true,false',
        ]);

        // ------------------ NORMALISATION ------------------
        $activated = filter_var($data['activated'], FILTER_VALIDATE_BOOLEAN);

        // ------------------ UPDATE User ------------------
        $user->update([
            'activated' => $activated,
        ]);

        // ------------------ DÉTERMINER LE NOM ------------------
        switch ($user->account_type) {
            case 'admin':
                $greetingName = 'Administrateur';
                break;
            case 'entreprise':
                $greetingName = $user->company_name ?? '-';
                break;
            default:
                $greetingName = $user->full_name ?? '-';
                break;
        }

        // ------------------ NOTIFICATION PROPRIÉTAIRE ------------------
        if ($user->email) {
            if ($user->activated) {
                // COMPTE ACTIVÉ
                Mail::raw(
                    "Bonjour {$greetingName},

    Votre compte a été activé par l’administrateur.
    Vous pouvez désormais accéder à toutes les fonctionnalités de la plateforme.

    Cordialement,  
    L’équipe Support AfricaLocation",
                    function ($message) use ($user) {
                        $message
                            ->to($user->email)
                            ->subject('Activation de votre compte');
                    }
                );
            } else {
                // COMPTE DÉSACTIVÉ
                Mail::raw(
                    "Bonjour {$greetingName},

    Votre compte a été désactivé par l’administrateur.
    Pour plus d’informations, veuillez contacter le support.

    Cordialement,  
    L’équipe Support AfricaLocation",
                    function ($message) use ($user) {
                        $message
                            ->to($user->email)
                            ->subject('Désactivation de votre compte');
                    }
                );
            }
        } else {
            \Log::warning("Impossible d'envoyer le mail : l'utilisateur {$user->id} n'a pas d'email.");
        }

        // ------------------ RESPONSE ------------------
        return response()->json([
            'message' => 'Le statut de l’utilisateur a été mis à jour. Un email lui a été envoyé si disponible.',
            'data'    => new UserResource($user),
        ], 200);
    }

}
