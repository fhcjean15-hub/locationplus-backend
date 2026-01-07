<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\ReservationCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ReservationCreatedMail; // Crée un Mailable
use App\Models\User;


class ReservationController extends Controller
{
    /**
     * 📌 Liste des réservations
     * - user connecté → user_id
     * - invité → tracking_token
     * - owner → owner_id
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['bien']) // 👈 CHARGEMENT DU BIEN
            ->latest();

        // -----------------------------
        // UTILISATEUR CONNECTÉ
        // - réservations faites
        // - réservations reçues (propriétaire)
        // -----------------------------
        if ($request->filled('user_id')) {
            $userId = $request->user_id;

            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                ->orWhere('owner_id', $userId);
            });
        }

        // -----------------------------
        // MODE INVITÉ
        // -----------------------------
        if ($request->filled('tracking_token')) {
            $query->where('tracking_token', $request->tracking_token);
        }

        return new ReservationCollection(
            $query->paginate(10)
        );
    }

    /**
     * 📌 Création d'une réservation
     * - connecté → user_id
     * - invité → tracking_token généré automatiquement (Model)
     */
    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'bien_id' => 'required|integer',
    //         'user_id' => 'nullable|uuid',
    //         'owner_id' => 'required|uuid',

    //         'client_name' => 'required|string|max:255',
    //         'client_email' => 'required|email',
    //         'client_phone' => 'required|string|max:30',

    //         'category' => 'required|string',
    //         'transaction_type' => 'required|string',
    //         'reservation_type' => 'required|string',
    //         'price' => 'required|numeric',

    //         'start_date' => 'nullable|date',
    //         'end_date' => 'nullable|date',
    //         'visit_date' => 'nullable|date',

    //         'message' => 'nullable|string',
    //     ]);

    //     $reservation = Reservation::create($data);

    //     // Récupérer l'email du propriétaire
    //     $owner = User::find($data['owner_id']);

    //     if ($owner && $owner->email) {
    //         // Envoi du mail
    //         Mail::to($owner->email)->send(new ReservationCreatedMail($reservation));
    //     }

    //     return response()->json([
    //         'reservation'    => new ReservationResource($reservation),
    //         'tracking_token' => $reservation->tracking_token, // 👈 CRUCIAL pour invité
    //     ], 201);
    // }
    public function store(Request $request)
    {
        $data = $request->validate([
            'bien_id' => 'required|integer',
            'user_id' => 'nullable|uuid',
            'owner_id' => 'required|uuid',

            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_phone' => 'required|string|max:30',

            'category' => 'required|string',
            'transaction_type' => 'required|string',
            'reservation_type' => 'required|string',
            'price' => 'required|numeric',

            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'visit_date' => 'nullable|date',

            'message' => 'nullable|string',
        ]);

        // ---------------------------------------------------------
        // CAS 1 : UTILISATEUR CONNECTÉ
        // ---------------------------------------------------------
        if (!empty($data['user_id'])) {
            $data['tracking_token'] = null;
        }
        // ---------------------------------------------------------
        // CAS 2 : INVITÉ (NON CONNECTÉ)
        // ---------------------------------------------------------
        else {
            $data['tracking_token'] = (string) Str::uuid();
            $data['user_id'] = null;
        }

        $reservation = Reservation::create($data);

        // ---------------------------------------------------------
        // ENVOI EMAIL AU PROPRIÉTAIRE
        // ---------------------------------------------------------
        $owner = User::find($data['owner_id']);

        if ($owner && $owner->email) {
            Mail::to($owner->email)->send(
                new ReservationCreatedMail($reservation)
            );
        }

        return response()->json([
            'reservation'    => new ReservationResource($reservation),
            'tracking_token' => $reservation->tracking_token, // null si connecté
        ], 201);
    }



    /**
     * 📌 Détail d'une réservation
     * Sécurisable plus tard via policy
     */
    public function show(Reservation $reservation)
    {
        return new ReservationResource($reservation);
    }

    /**
     * 📌 Mise à jour du statut
     * (owner / admin uniquement en pratique)
     */
    public function update(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,expired',
        ]);

        $reservation->update($data);

        return new ReservationResource($reservation);
    }

    /**
     * 📌 Suppression
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return response()->json([
            'message' => 'Réservation supprimée',
        ]);
    }

    /**
     * 📌 Accès invité via tracking token (écran d’entrée)
     */
    public function guestReservations(Request $request)
    {
        $request->validate([
            'tracking_token' => 'required|uuid',
        ]);

        $reservations = Reservation::with(['bien'])
                            ->where(
                                'tracking_token',
                                $request->tracking_token
                            )->latest()->paginate(10);

        return new ReservationCollection($reservations);
    }
}
