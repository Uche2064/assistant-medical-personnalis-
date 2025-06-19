<?php

use App\Http\Controllers\v1\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\admin\AuthController;
use App\Http\Controllers\v1\Api\admin\CompagnieController;
use App\Http\Controllers\v1\Api\admin\GestionnaireController;

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
            Route::get('/gestionnaires/{id}', [GestionnaireController::class, 'show']);
            Route::put('/gestionnaires/{id}', [GestionnaireController::class, 'update']);
            Route::delete('/gestionnaires/{id}', [GestionnaireController::class, 'destroy']);

            // compagnie

            // compagnie
            Route::get('/compagnies', [CompagnieController::class, 'index']);
            Route::post('/compagnies', [CompagnieController::class, 'store']);
            Route::get('/compagnies/{id}', [CompagnieController::class, 'show']);
            Route::put('/compagnies/{id}', [CompagnieController::class, 'update']);
            Route::delete('/compagnies/{id}', [CompagnieController::class, 'destroy']);
        });
    });
});
