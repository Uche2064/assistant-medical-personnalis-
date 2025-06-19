<?php

use App\Http\Controllers\v1\Api\admin\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\admin\AuthController;
use App\Http\Controllers\v1\Api\admin\CompagnieController;
use App\Http\Controllers\v1\Api\admin\GestionnaireController;

Route::middleware('verifyApiKey')->prefix('v1')->group(function () {


    

    // Route pour envoyer un OTP (accessible à tous)
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);

    // Route pour l'inscription (ex: pour les assurés, si besoin)
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminController::class, 'login']);
        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            // gestionnaire
            Route::get('/gestionnaires', [GestionnaireController::class, 'index']);
            Route::post('/gestionnaires', [GestionnaireController::class, 'store']);


            // compagnie
            Route::get('/compagnies', [CompagnieController::class, 'index']);
            Route::post('/compagnies', [CompagnieController::class, 'store']);
            Route::get('/compagnies/{id}', [CompagnieController::class, 'show']);
            Route::put('/compagnies/{id}', [CompagnieController::class, 'update']);
            Route::delete('/compagnies/{id}', [CompagnieController::class, 'destroy']);

        });
    });

});
