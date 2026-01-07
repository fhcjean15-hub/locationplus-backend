<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bien;
use App\Http\Resources\BienResource;
use App\Models\Notification;

class AdminBienController extends Controller
{
    public function updateActif(Request $request, Bien $bien)
    {
        // ------------------ VALIDATION ------------------
        $data = $request->validate([
            'actif' => 'required|in:0,1,true,false',
        ]);

        // ------------------ NORMALISATION ------------------
        $actif = filter_var($data['actif'], FILTER_VALIDATE_BOOLEAN);

        // ------------------ UPDATE BIEN ------------------
        $bien->update([
            'actif' => $actif,
        ]);

        // ------------------ NOTIFICATION PROPRIÉTAIRE ------------------
        $admin = $request->user(); // admin connecté
        $owner = $bien->user;      // propriétaire du bien

        if ($owner) {
            Notification::create([
                'user_id' => $owner->id,
                'type'    => 'admin_action',
                'payload' => [
                    'bien_id'   => $bien->id,
                    'actif'     => $actif,
                    'admin_id'  => $admin->id,
                    'admin_email' => $admin->email,
                    'message'   => $actif
                        ? "Votre bien « {$bien->title} » a été activé par l’administrateur."
                        : "Votre bien « {$bien->title} » a été désactivé par l’administrateur.",
                ],
                'read' => false,
            ]);
        }

        // ------------------ RESPONSE ------------------
        return new BienResource($bien);
    }

}
