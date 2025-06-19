<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\AuthController;
use App\Http\Controllers\v1\Api\admin\CompagnieController;
use App\Http\Controllers\v1\Api\admin\GestionnaireController;
use App\Http\Controllers\v1\Api\gestionnaire\PersonnelController;

Route::middleware('verifyApiKey')->prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/login', [AuthController::class, 'loginWithEmailAndPassword']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });


    Route::prefix('admin')->group(function () {
        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            // gestionnaires
            Route::get('/gestionnaires', [GestionnaireController::class, 'index']);
            Route::get('/gestionnaires/{id}', [GestionnaireController::class, 'show']);
            Route::get('/gestionnaires/compagnie/{compagnie_id}', [GestionnaireController::class, 'gestionnaireByCompagnieId']);
            Route::post('/gestionnaires', [GestionnaireController::class, 'store']);

            Route::put('/gestionnaires/{id}', [GestionnaireController::class, 'update']);
            Route::delete('/gestionnaires/{id}', [GestionnaireController::class, 'destroy']);

            // compagnie
            Route::get('/compagnies', [CompagnieController::class, 'index']);
            Route::post('/compagnies', [CompagnieController::class, 'store']);
            Route::get('/compagnies/{id}', [CompagnieController::class, 'show']);
            Route::put('/compagnies/{id}', [CompagnieController::class, 'update']);
            Route::delete('/compagnies/{id}', [CompagnieController::class, 'destroy']);
        });
    });

    Route::prefix('gestionnaire')->group(function () {
        Route::middleware(['auth:sanctum', 'gestionnaire'])->group(function () {
            // Routes pour la gestion des personnels
            Route::get('/personnels', [PersonnelController::class, 'index']);
            Route::post('/personnels', [PersonnelController::class, 'store']);
            Route::get('/personnels/{id}', [PersonnelController::class, 'show']);
            Route::put('/personnels/{id}', [PersonnelController::class, 'update']);
            Route::delete('/personnels/{id}', [PersonnelController::class, 'destroy']);
        });
    });

});
