<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentCollection;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\User;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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


    
    /**
     * 🔥 Initier un paiement FedaPay (appelé par Flutter)
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'amount' => 'required|numeric|min:100',
            ]);

            // =========================
            // 1) Créer le paiement local
            // =========================
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'status' => 'pending',
                'transaction_reference' => null,
                'paid_at' => null,
            ]);

            // =========================
            // 2) Config FedaPay
            // =========================
            FedaPay::setApiKey(config('services.fedapay.secret_key'));
            FedaPay::setEnvironment(config('services.fedapay.mode'));

            Log::info('FedaPay configuré', [
                'mode' => config('services.fedapay.mode'),
            ]);

            // =========================
            // 3) Créer transaction FedaPay
            // =========================
            $transaction = Transaction::create([
                'description' => 'Abonnement annuel Location+',
                'amount' => $validated['amount'],
                'currency' => ['iso' => 'XOF'],
                'callback_url' => route('payment.success'),
                'cancel_url' => route('payment.cancel'),
                'metadata' => [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                ],
            ]);

            // Sauvegarde de la référence FedaPay
            $payment->transaction_reference = $transaction->id;
            $payment->save();

            // =========================
            // 4) Générer l'URL de paiement
            // =========================
            $token = $transaction->generateToken();
            $paymentUrl = $token->url ?? ($token->getUrl() ?? null);

            if (!$paymentUrl) {
                return response()->json([
                    'error' => 'Impossible de générer l’URL de paiement'
                ], 500);
            }

            // =========================
            // 5) Réponse Flutter
            // =========================
            return response()->json([
                'payment_id' => $payment->id,
                'fedapay_transaction_id' => $transaction->id,
                'payment_url' => $paymentUrl,
                'amount' => $validated['amount'],
            ], 201);

        } catch (\Exception $e) {

            Log::error('Erreur init paiement', [
                'exception' => $e
            ]);

            return response()->json([
                'error' => 'La création du paiement a échoué',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * 🔥 Webhook FedaPay
     */
    public function fedapayWebhook(Request $request)
    {
        $payload = $request->getContent();
        $rawHeader = $request->header('X-Fedapay-Signature');
        $secret = config('services.fedapay.webhook_secret');

        if (!$rawHeader) {
            Log::warning('Webhook FedaPay: signature manquante');
            return response()->json(['error' => 'Missing signature'], 403);
        }

        if (!preg_match('/t=(\d+),s=([a-f0-9]+)/', $rawHeader, $match)) {
            return response()->json(['error' => 'Invalid signature format'], 403);
        }

        $timestamp = $match[1];
        $signature = $match[2];

        $dataToSign = $timestamp . '.' . $payload;
        $computed = hash_hmac('sha256', $dataToSign, $secret);

        if (!hash_equals($computed, $signature)) {
            Log::error('❌ Signature webhook invalide');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        Log::info('✅ Webhook FedaPay validé');

        $data = json_decode($payload, true);

        $object = $data['entity'] ?? $data;
        $status = $object['status'] ?? null;
        $transactionId = $object['id'] ?? null;

        if (!$transactionId) {
            return response()->json(['error' => 'No transaction id'], 400);
        }

        $payment = Payment::where('transaction_reference', $transactionId)->first();

        if (!$payment) {
            Log::error("Paiement introuvable", ['transaction_id' => $transactionId]);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $paidStatuses = ['approved', 'transferred', 'paid', 'completed', 'success'];

        if (in_array($status, $paidStatuses, true)) {

            // =========================
            // Marquer paiement payé
            // =========================
            $payment->status = 'paid';
            $payment->paid_at = now();
            $payment->save();

            // =========================
            // Activer abonnement user
            // =========================
            $user = User::find($payment->user_id);
            if ($user) {
                $user->payment_status = 'paid';
                $user->payment_valid_until = now()->addYear();
                $user->save();
            }

            Log::info('✅ Abonnement activé', [
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'valid_until' => $user?->payment_valid_until
            ]);
        }

        return response()->json(['ok' => true]);
    }

}
