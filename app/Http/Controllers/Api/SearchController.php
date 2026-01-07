<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bien;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->input('q');

        if (!$q) {
            return response()->json([
                'data' => [],
            ]);
        }

        // ---------------- BIENS ----------------
        $biens = Bien::where(function ($query) use ($q) {
            $query
                ->where('title', 'LIKE', "%$q%")
                ->orWhere('description', 'LIKE', "%$q%")
                ->orWhere('transaction_type', 'LIKE', "%$q%")
                ->orWhere('price', 'LIKE', "%$q%");
        })
        ->with('user')
        ->get()
        ->map(function ($bien) {
            return [
                'type' => 'bien',
                'data' => $bien,
            ];
        });

        // ---------------- ENTREPRISES ----------------
        $companies = User::where('account_type', 'entreprise')
            ->where(function ($query) use ($q) {
                $query
                    ->where('company_name', 'LIKE', "%$q%")
                    ->orWhere('email', 'LIKE', "%$q%");
            })
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'entreprise',
                    'data' => $user,
                ];
            });

        // ---------------- PARTICULIERS ----------------
        $users = User::where('account_type', 'particulier')
            ->where(function ($query) use ($q) {
                $query
                    ->where('full_name', 'LIKE', "%$q%")
                    ->orWhere('email', 'LIKE', "%$q%");
            })
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'particulier',
                    'data' => $user,
                ];
            });

        return response()->json([
            'data' => $biens
                ->merge($companies)
                ->merge($users)
                ->values(),
        ]);
    }
}

