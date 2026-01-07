<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    // ----------------------------------------------------------
    // REGISTER AGENT
    // ----------------------------------------------------------
    public function registerAgent(Request $request)
    {
        // 1. VALIDATION
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users',
            'numero'            => 'required|string|max:20',
            'categorie_id'      => 'required|integer|exists:account_categories,id',
            'password'          => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // 1. LOG avant création
        Log::info('Données reçues pour création d’un utilisateur :', [
            'name'  => $request->name,
            'email'         => $request->email,
            'numero'         => $request->numero,
            'account_category_id' => $request->categorie_id,
            'password' => $request->password,
        ]);

        // 2. CRÉATION USER
        $user = User::create([
            'full_name'          => $request->name,
            'email'         => $request->email,
            'phone'        => $request->numero,
            'account_category_id'  => $request->categorie_id,
            'account_type'          => 'particulier',
            'password'      => Hash::make($request->password),
        ]);

        // 3. AUTO LOGIN (TOKEN)
        $token = $user->createToken('API TOKEN')->plainTextToken;

        // 4. RESPONSE
        return response()->json([
            'success' => true,
            'message' => 'Agent créé avec succès',
            'token'   => $token,
            'user'    => $user
        ], 201);
    }

    // ----------------------------------------------------------
    // REGISTER AGENCE
    // ----------------------------------------------------------
    public function registerAgence(Request $request)
    {
        // 1. VALIDATION
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users',
            'numero'            => 'required|string|max:20',
            'categorie_id'      => 'required|integer|exists:account_categories,id',
            'password'          => 'required|min:6|confirmed',
        ]);
        

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // 1. LOG avant création
        Log::info('Données reçues pour création d’un utilisateur :', [
            'name'  => $request->name,
            'email'         => $request->email,
            'numero'         => $request->numero,
            'account_category_id' => $request->categorie_id,
            'password' => $request->password,
        ]);

        // 2. CRÉATION USER
        $user = User::create([
            'company_name'  => $request->name, // Nom entreprise
            'email'         => $request->email,
            'phone'        => $request->numero,
            'account_category_id'  => $request->categorie_id,
            'account_type'          => 'entreprise',
            'password'      => Hash::make($request->password),
        ]);

        // 3. AUTO LOGIN (TOKEN)
        $token = $user->createToken('API TOKEN')->plainTextToken;

        // 4. RESPONSE
        return response()->json([
            'success' => true,
            'message' => 'Agence créée avec succès',
            'token'   => $token,
            'user'    => $user
        ], 201);
    }
}
