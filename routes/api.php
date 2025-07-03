<?php

use App\Http\Controllers\v1\Api\Assure\BeneficiaireController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\AuthController;
use App\Http\Controllers\v1\Api\GestionnaireController;
use App\Http\Controllers\v1\Api\ContratController;
use App\Http\Controllers\v1\Api\PersonnelController;
use App\Http\Controllers\v1\Api\DemandeAdhesionController;
use App\Http\Controllers\v1\Api\QuestionController;

Route::middleware('verifyApiKey')->prefix('v1')->group(function () {

    // Authentification et gestion des mots de passe
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/login', [AuthController::class, 'loginWithEmailAndPassword']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
        // gestionnaires
        Route::get('/gestionnaires', [GestionnaireController::class, 'index']);
        Route::get('/gestionnaires/{id}', [GestionnaireController::class, 'show']);
        Route::post('/gestionnaires', [GestionnaireController::class, 'store']);
        Route::put('/gestionnaires/{id}', [GestionnaireController::class, 'update']);
        Route::delete('/gestionnaires/{id}', [GestionnaireController::class, 'destroy']);
    });

    Route::middleware(['auth:api', 'gestionnaire'])->prefix('gestionnaire')->group(function () {
        // Routes pour la gestion des personnels
        Route::get('/personnels', [PersonnelController::class, 'index']);
        Route::post('/personnels', [PersonnelController::class, 'store']);
        Route::get('/personnels/{id}', [PersonnelController::class, 'show']);
        Route::put('/personnels/{id}', [PersonnelController::class, 'update']);
        Route::delete('/personnels/{id}', [PersonnelController::class, 'destroy']);
    });


    Route::prefix('demandes-adhesion')->group(function () {
        Route::post('/prestataires', [DemandeAdhesionController::class, 'storePrestataire']);
        Route::post('/entreprises', [DemandeAdhesionController::class, 'storeEntreprise']);
        Route::post('/clients', [DemandeAdhesionController::class, 'storeClient']);
        Route::get('/clients/formulaire', [DemandeAdhesionController::class, 'getClientFormulaire']);
        Route::get('/prestataires/formulaire', [DemandeAdhesionController::class, 'getPrestataireFormulaire']);
        Route::get('/entreprises/formulaire', [DemandeAdhesionController::class, 'getEntrepriseFormulaire']);

        Route::middleware(['auth:api', 'medecin_controleur'])->group(function () {
            Route::get('/', [DemandeAdhesionController::class, 'index']);
            Route::get('/{id}', [DemandeAdhesionController::class, 'show']);
            Route::post('/{demande_id}/validate', [DemandeAdhesionController::class, 'validate']);
            Route::post('/{demande_id}/reject', [DemandeAdhesionController::class, 'reject']);
        });
    });

    Route::prefix('medecin')->group(function () {
        Route::get('/questions/prospects-physique', [QuestionController::class, 'getProspectPhysiqueQuestions']);
        Route::get('/questions/prospects-moral', [QuestionController::class, 'getProspectMoralQuestions']);

        Route::middleware(['auth:api', 'medecin_controleur'])->group(function () {
            Route::get('/questions', [QuestionController::class, 'index']);
            Route::get('/questions/{id}', [QuestionController::class, 'show']);
            Route::post('/bulk/questions', [QuestionController::class, 'bulkInsert']);
            Route::put('/bulk/questions', [QuestionController::class, 'bulkUpdate']);
            Route::put('/bulk/questions', [QuestionController::class, 'bulkUpdate']);
            Route::delete('/bulk/questions', [QuestionController::class, 'bulkDestroy']);
        });
    });

    Route::middleware('auth:api')->prefix('contrats')->group(function () {
        Route::get('/', [ContratController::class, 'index']);
        Route::post('/', [ContratController::class, 'store']);
        Route::get('/{numero_police}', [ContratController::class, 'show']);
        Route::put('/{numero_police}', [ContratController::class, 'update']);
        // Route::patch('/{numero_police}/status', [ContratController::class, 'changeStatus']);
    });

    Route::middleware(['auth:api', 'assure_principal'])->prefix('assure/beneficiaires')->group(function () {
        Route::get('/', [BeneficiaireController::class, 'index']);
        Route::get('/{id}', [BeneficiaireController::class, 'show']);
        Route::post('/', [BeneficiaireController::class, 'store']);
        Route::put('/{id}', [BeneficiaireController::class, 'update']);
        Route::delete('/{id}', [BeneficiaireController::class, 'destroy']);
    });
});
