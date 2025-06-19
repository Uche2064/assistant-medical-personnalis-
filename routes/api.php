<?php

use App\Http\Controllers\v1\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\AuthController;
use App\Http\Controllers\v1\Api\GestionnaireController;

Route::middleware('verifyApiKey')->prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/login', [AuthController::class, 'loginWithEmailAndPassword']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });
    });


    Route::prefix('admin')->group(function () {
        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            // gestionnaires
            Route::get('/gestionnaires', [GestionnaireController::class, 'index']);
            Route::post('/gestionnaires', [GestionnaireController::class, 'store']);

            // compagnie
            Route::post('/compagnies', [AdminController::class, 'storeCompagnie']);
        });
    });
});
