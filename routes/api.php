<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\AnnonceController;
use App\Http\Controllers\Api\AccountCategoryController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\BienController;
use App\Http\Controllers\Api\AdminBienController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\SearchController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Sanctum protège automatiquement les routes dans le groupe auth:sanctum
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| AUTH PUBLIC
|--------------------------------------------------------------------------
*/
Route::post('/login', [UserController::class, 'login']);
Route::post('/users', [UserController::class, 'store']); // register
Route::get('/biens/libre', [BienController::class, 'getAllBiens']);
Route::post('/check-admin-access', function (Request $request) {
    if (Request::get('code') === env('ADMIN_ACCESS_CODE')) {
        return response()->json(['valid' => true]);
    }

    return response()->json(['error' => 'Invalid code'], 403);
});



// ---------------------- ROUTES PUBLIQUES ----------------------
// Création d'une réservation (utilisateur connecté ou invité)
Route::post('/reservations', [ReservationController::class, 'store']);

// Accès invité pour suivre ses réservations via tracking_token
Route::get('/reservations/guest', [ReservationController::class, 'guestReservations']);


// ---------------------- ROUTES PROTÉGÉES ----------------------
Route::middleware('auth:sanctum')->group(function () {

    // Liste des réservations de l'utilisateur connecté ou owner
    Route::get('/reservations', [ReservationController::class, 'index']);

    // Détail d'une réservation
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);

    // Mise à jour du statut d'une réservation (owner/admin)
    Route::put('/reservations/{reservation}', [ReservationController::class, 'update']);
    Route::patch('/reservations/{reservation}', [ReservationController::class, 'update']);

    // Suppression d'une réservation (owner/admin)
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);
});

// ----------------- AJOUT ADMIN -----------------
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::post('/admin/users/africa/location/africa/location', [UserController::class, 'storeAdmin']); // Ajout par admin
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('biens', BienController::class);
});


// Groupe des routes pour les biens (avec middleware auth:sanctum si authentification)
Route::middleware('auth:sanctum')->group(function () {

    // 📄 Liste des biens de l'utilisateur connecté (GET /api/biens/user)
    Route::get('/mesbiens/user', [BienController::class, 'userBiens']);

    Route::get('/cesbiens', [BienController::class, 'getUserBiens']);

    // 📄 Liste des biens avec filtres (GET /api/biens)
    Route::get('/biens', [BienController::class, 'index']);

    // ➕ Création d’un bien (POST /api/biens)
    Route::post('/biens', [BienController::class, 'store']);

    // 🔍 Détails d’un bien (GET /api/biens/{bien})
    Route::get('/biens/{bien}', [BienController::class, 'show']);

    // ✏️ Mise à jour d’un bien (PUT /api/biens/{bien})
    Route::put('/biens/{bien}', [BienController::class, 'update']);

    // 🗑️ Suppression d’un bien (DELETE /api/biens/{bien})
    Route::delete('/biens/{bien}', [BienController::class, 'destroy']);
});


Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::put('/admin/biens/{bien}/actif', [AdminBienController::class, 'updateActif']);
    Route::put('/admin/users/{user}/activated', [AdminUserController::class, 'updateActivated']);
});



Route::post('/forgot-password', [ForgotPasswordController::class, 'sendEmail']);
Route::post('/verify-otp',        [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/reset-password',    [ForgotPasswordController::class, 'resetPassword']);

Route::post('/register-agent', [RegisterController::class, 'registerAgent']);
Route::post('/register-agence', [RegisterController::class, 'registerAgence']);

/*
|--------------------------------------------------------------------------
| ACCOUNT CATEGORIES
|--------------------------------------------------------------------------
*/
Route::prefix('account-categories')->group(function () {
    Route::get('/', [AccountCategoryController::class, 'index']);
    Route::get('/agents', [AccountCategoryController::class, 'agents']);
    Route::get('/agences', [AccountCategoryController::class, 'agences']);
    Route::post('/', [AccountCategoryController::class, 'store']);
    Route::get('/{id}', [AccountCategoryController::class, 'show']);
    Route::put('/{id}', [AccountCategoryController::class, 'update']);
    Route::delete('/{id}', [AccountCategoryController::class, 'destroy']);
});


Route::get('/search', [SearchController::class, 'search']);
Route::post('/notifications/report', [NotificationController::class, 'storeSignalement']);

/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES PAR SANCTUM
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/users/documents', [UserController::class, 'uploadDocuments']);

    Route::get('/users', [UserController::class, 'index'])->middleware('is_admin');
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('is_admin');





    /*
    |--------------------------------------------------------------------------
    | ANNONCES
    |--------------------------------------------------------------------------
    */
    Route::get('/annonces', [AnnonceController::class, 'index']);
    Route::post('/annonces', [AnnonceController::class, 'store']);
    Route::get('/annonces/{id}', [AnnonceController::class, 'show']);
    Route::put('/annonces/{id}', [AnnonceController::class, 'update']);
    Route::delete('/annonces/{id}', [AnnonceController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | MEDIAS
    |--------------------------------------------------------------------------
    */
    Route::get('/medias', [MediaController::class, 'index']); 
    Route::post('/medias', [MediaController::class, 'store']);
    Route::get('/medias/{id}', [MediaController::class, 'show']);
    Route::put('/medias/{id}', [MediaController::class, 'update']);
    Route::delete('/medias/{id}', [MediaController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | PAYMENTS
    |--------------------------------------------------------------------------
    */
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::put('/payments/{id}', [PaymentController::class, 'update']);
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | NOTIFICATIONS
    |--------------------------------------------------------------------------
    */
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::put('/notifications/{id}', [NotificationController::class, 'update']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::get('/notifications/last-verification/{userId}', [NotificationController::class, 'getLastVerificationNotification']);

});



















// <?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\UserController;
// use App\Http\Controllers\Api\AnnonceController;
// use App\Http\Controllers\Api\MediaController;
// use App\Http\Controllers\Api\PaymentController;
// use App\Http\Controllers\Api\NotificationController;
// use App\Http\Controllers\Api\AccountCategoryController;


// /*
// |--------------------------------------------------------------------------
// | AUTH PUBLIC
// |--------------------------------------------------------------------------
// */
// Route::post('/login', [UserController::class, 'login']);
// Route::post('/users', [UserController::class, 'store']); // register


// /*
// |--------------------------------------------------------------------------
// | ROUTES PROTÉGÉES PAR SANCTUM
// |--------------------------------------------------------------------------
// */
// Route::middleware('auth:sanctum')->group(function () {

//     // Profile + logout
//     Route::get('/profile', [UserController::class, 'profile']);
//     Route::post('/logout', [UserController::class, 'logout']);

//     // Upload documents
//     Route::post('/users/documents', [UserController::class, 'uploadDocuments']);

//     /*
//     |--------------------------------------------------------------------------
//     | API RESOURCE ROUTES
//     |--------------------------------------------------------------------------
//     | version propre & automatique
//     |--------------------------------------------------------------------------
//     */

//     // Users CRUD (store est public donc exclu)
//     Route::apiResource('users', UserController::class)
//         ->except(['store']);

//     // Annonces CRUD
//     Route::apiResource('annonces', AnnonceController::class);

//     // Medias CRUD
//     Route::apiResource('medias', MediaController::class);

//     // Payments CRUD
//     Route::apiResource('payments', PaymentController::class);

//     // Notifications CRUD
//     Route::apiResource('notifications', NotificationController::class);

//     // Account Categories CRUD
//     Route::apiResource('account-categories', AccountCategoryController::class);
// });
