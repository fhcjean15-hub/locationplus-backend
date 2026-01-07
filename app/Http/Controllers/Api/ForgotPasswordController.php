<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // ---------------------------------------------------------
    // 1️⃣ ENVOI EMAIL + OTP
    // ---------------------------------------------------------
    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                "success" => false,
                "message" => "Aucun utilisateur trouvé avec cet email."
            ], 404);
        }

        // Générer un OTP à 6 chiffres
        $otp = rand(100000, 999999);

        // Stocker en DB
        PasswordOtp::create([
            'email'      => $request->email,
            'otp'        => $otp,
            'expires_at' => Carbon::now()->addMinutes(10)
        ]);

        // Envoyer email (version simple)
        Mail::raw("
        Bonjour,

        Vous avez demandé à réinitialiser votre mot de passe.

        Votre code de vérification est : $otp

        Ce code est valable pendant 10 minutes.  
        Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer ce message.

        Cordialement,  
        L’équipe Support
        ", function ($msg) use ($request) {
            $msg->to($request->email)
                ->subject('Code de vérification – Réinitialisation du mot de passe');
        });


        return response()->json(["success" => true]);
    }

    // ---------------------------------------------------------
    // 2️⃣ VÉRIFICATION OTP
    // ---------------------------------------------------------
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required'
        ]);

        $record = PasswordOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$record) {
            return response()->json([
                "verified" => false,
                "message"  => "Code incorrect."
            ], 422);
        }

        if (Carbon::parse($record->expires_at)->isPast()) {
            return response()->json([
                "verified" => false,
                "message"  => "Le code a expiré."
            ], 410);
        }

        return response()->json(["verified" => true]);
    }

    // ---------------------------------------------------------
    // 3️⃣ RESET PASSWORD
    // ---------------------------------------------------------
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email',
            'otp'                   => 'required',
            'password'              => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        // Vérifier OTP valide
        $otpRecord = PasswordOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpRecord) {
            return response()->json([
                "reset" => false,
                "message" => "OTP invalide."
            ], 422);
        }

        if (Carbon::parse($otpRecord->expires_at)->isPast()) {
            return response()->json([
                "reset" => false,
                "message" => "Code expiré."
            ], 410);
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer OTP
        $otpRecord->delete();

        return response()->json(["reset" => true]);
    }
}
