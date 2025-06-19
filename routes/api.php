<?php

use App\Http\Controllers\v1\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\AuthController;
use App\Http\Controllers\v1\Api\GestionnaireController;

Route::middleware('verifyApiKey')->prefix('v1')->group(function () {


    

    // Route pour envoyer un OTP (accessible à tous)
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);

    // Route pour l'inscription (ex: pour les assurés, si besoin)
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminController::class, 'login']);
        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            // gestionnaires
            Route::get('/gestionnaires', [GestionnaireController::class, 'index']);
            Route::post('/gestionnaires', [GestionnaireController::class, 'store']);

            // compagnie
            Route::post('/compagnies', [AdminController::class, 'storeCompagnie']);

        });
    });

});
