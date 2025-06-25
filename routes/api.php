<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\AuthController;
use App\Http\Controllers\v1\Api\admin\CompagnieController;
use App\Http\Controllers\v1\Api\admin\GestionnaireController;
use App\Http\Controllers\v1\Api\ContratController;
use App\Http\Controllers\v1\Api\gestionnaire\PersonnelController;
use App\Http\Controllers\v1\Api\DemandeAdhesionController;
use App\Http\Controllers\v1\Api\medecin\QuestionController;

Route::middleware('verifyApiKey')->prefix('v1')->group(function () {

    // Authentification et gestion des mots de passe
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/login', [AuthController::class, 'loginWithEmailAndPassword']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->middleware('auth:api');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:api');
    }); 

   

    Route::prefix('admin')->group(function () {
        Route::middleware(['auth:api', 'admin'])->group(function () {
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
        Route::middleware(['auth:api', 'gestionnaire'])->group(function () {
            // Routes pour la gestion des personnels
            Route::get('/personnels', [PersonnelController::class, 'index']);
            Route::post('/personnels', [PersonnelController::class, 'store']);
            Route::get('/personnels/{id}', [PersonnelController::class, 'show']);
            Route::put('/personnels/{id}', [PersonnelController::class, 'update']);
            Route::delete('/personnels/{id}', [PersonnelController::class, 'destroy']);
        });
    });


     // Routes pour les demandes d'adhésion
     Route::prefix('demandes-adhesion')->group(function () {
        // Routes publiques pour soumettre des demandes d'adhésion
        Route::post('/prestataires', [DemandeAdhesionController::class, 'storePrestataire']);
        Route::post('/entreprises', [DemandeAdhesionController::class, 'storeEntreprise']);
        Route::post('/clients', [DemandeAdhesionController::class, 'storeClient']);
        Route::get('/clients/formulaire', [DemandeAdhesionController::class, 'getClientFormulaire']);
        Route::get('/prestataires/formulaire', [DemandeAdhesionController::class, 'getPrestataireFormulaire']);
        Route::get('/entreprises/formulaire', [DemandeAdhesionController::class, 'getEntrepriseFormulaire']);
        
        // Routes protégées pour gestion des demandes
        Route::middleware(['auth:api', 'medecin_controleur'])->group(function () {
            Route::get('/', [DemandeAdhesionController::class, 'index']);
            Route::get('/{id}', [DemandeAdhesionController::class, 'show']);
            Route::post('/{demande_id}/validate', [DemandeAdhesionController::class, 'validate']);
            Route::post('/{demande_id}/reject', [DemandeAdhesionController::class, 'reject']);
        });
    });
    
    // Routes pour les questions
    Route::prefix('medecin')->group(function () {
        // Route publique pour récupérer les questions pour les prospects
        Route::get('/questions/prospects-physique', [QuestionController::class, 'getProspectPhysiqueQuestions']);
        Route::get('/questions/prospects-moral', [QuestionController::class, 'getProspectMoralQuestions']);
        
        // Routes protégées pour la gestion des questions
        Route::middleware(['auth:api', 'medecin_controleur'])->group(function () {
            Route::get('/questions', [QuestionController::class, 'index']);
            Route::get('/questions/{id}', [QuestionController::class, 'show']);
            // Route::post('/questions', [QuestionController::class, 'store']);
            Route::post('/bulk/questions', [QuestionController::class, 'bulkInsert']);
            // Route::put('/questions/{id}', [QuestionController::class, 'update']);
            Route::put('/bulk/questions', [QuestionController::class, 'bulkUpdate']);
            // Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);
            Route::delete('/bulk/questions', [QuestionController::class, 'bulkDestroy']);
        });
    });
    
    // Routes pour les contrats
    Route::prefix('contrats')->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::get('/', [ContratController::class, 'index']);
            Route::post('/', [ContratController::class, 'store']);
            Route::get('/{uuid}', [ContratController::class, 'show']);
            Route::put('/{uuid}', [ContratController::class, 'update']);
            Route::patch('/{uuid}/status', [ContratController::class, 'changeStatus']);
        });
    });

});
