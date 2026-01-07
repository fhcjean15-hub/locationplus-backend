<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentCollection;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * List all payments of the authenticated user.
     */
    public function index()
    {
        $payments = Payment::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return new PaymentCollection($payments);
    }

    /**
     * Create a new payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'  => 'required|exists:users,id',
            'amount'   => 'required|numeric|min:1',
            'method'   => 'nullable|in:mobile_money,card,cash',
            'transaction_reference' => 'nullable|string|max:120',
        ]);

        $payment = Payment::create([
            'user_id'               => $validated['user_id'],
            'amount'                => $validated['amount'],
            'method'                => $validated['method'] ?? null,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'status'                => 'pending',
        ]);

        return new PaymentResource($payment);
    }

    /**
     * Display a single payment.
     */
    public function show(string $id)
    {
        $payment = Payment::where('user_id', auth()->id())
            ->findOrFail($id);

        return new PaymentResource($payment);
    }

    /**
     * Update payment status (paid, failed, pending)
     */
    public function update(Request $request, string $id)
    {
        $payment = Payment::where('user_id', auth()->id())
            ->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,paid,failed',
        ]);

        $payment->update([
            'status' => $validated['status'],
        ]);

        return new PaymentResource($payment);
    }

    /**
     * Delete a payment.
     */
    public function destroy(string $id)
    {
        $payment = Payment::where('user_id', auth()->id())
            ->findOrFail($id);

        $payment->delete();

        return response()->json([
            'message' => 'Paiement supprimé avec succès.',
        ]);
    }
}
