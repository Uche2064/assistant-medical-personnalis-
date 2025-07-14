<?php

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypePrestataireEnum;
use App\Http\Controllers\v1\Api\Assure\BeneficiaireController;
use App\Http\Controllers\v1\Api\auth\AuthController;
use App\Http\Controllers\v1\Api\categorie_garantie\CategorieGarantieController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\ClientControlleur;
use App\Http\Controllers\v1\Api\GestionnaireController;
use App\Http\Controllers\v1\Api\ContratController;
use App\Http\Controllers\v1\Api\demande_adhesion\DemandeAdhesionController;
use App\Http\Controllers\v1\Api\garanties\GarantieController;
use App\Http\Controllers\v1\Api\PersonnelController;
use App\Http\Controllers\v1\Api\QuestionController;

Route::middleware('verifyApiKey')->prefix('v1')->group(function () {

    // Authentification et gestion des mots de passe
    Route::prefix('auth')->group(function () {
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/login', [AuthController::class, 'loginWithEmailAndPassword']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/check-unique', [AuthController::class, 'checkUnique']);
    });

    Route::middleware(['auth:api', 'admin'])->prefix('gestionnaires')->group(function () {
        // gestionnaires
        Route::get('/', [GestionnaireController::class, 'index']);
        Route::post('/', [GestionnaireController::class, 'store']);
        Route::get('/{id}', [GestionnaireController::class, 'show'])->where('id', '[0-9]+');
        // Route::put('/{id}', [GestionnaireController::class, 'update']);
        Route::delete('/{id}', [GestionnaireController::class, 'destroy']);
    });

    Route::middleware(['auth:api'])->prefix('personnels')->group(function () {
        // Routes pour la gestion des personnels
        Route::get('/', [PersonnelController::class, 'index']);
        Route::post('/', [PersonnelController::class, 'store'])->middleware('gestionnaire');
        Route::get('/{id}', [PersonnelController::class, 'show']);
        // Route::put('/{id}', [PersonnelController::class, 'update'])->middleware('gestionnaire');
        Route::delete('/{id}', [PersonnelController::class, 'destroy'])->middleware('gestionnaire');
    });


    Route::prefix('demandes-adhesions')->group(function () {
        Route::post('/prestataires', [DemandeAdhesionController::class, 'storePrestataire']);
        Route::post('/entreprises', [DemandeAdhesionController::class, 'storeEntreprise']);
        Route::post('/clients', [DemandeAdhesionController::class, 'storeClient']);
        Route::get('/questionnaire/{demandeur}', [DemandeAdhesionController::class, 'getQuestionsForDemandeur']);

        Route::middleware(['auth:api'])->group(function () {
            Route::get('/', [DemandeAdhesionController::class, 'index'])->middleware([
                'medecin_controleur',
                'technicien'
            ]);
            Route::get('/{id}', [DemandeAdhesionController::class, 'show'])->middleware([
                'medecin_controleur',
                'technicien'
            ]);
            Route::put('/{demande_id}/valider-prospect', [DemandeAdhesionController::class, 'validerProspect'])->middleware('technicien');
            Route::put('/{demande_id}/valider-prestataire', [DemandeAdhesionController::class, 'validerClient'])->middleware('medecin_controleur');
            Route::put('/{demande_id}/rejeter', [DemandeAdhesionController::class, 'rejeter']);
        });
    });

    Route::middleware(['auth:api'])->prefix('contrats')->group(function () {
        Route::get('/', [ContratController::class, 'index']);
        Route::post('/', [ContratController::class, 'store'])->middleware('technicien');
        Route::get('/{id}', [ContratController::class, 'show'])->middleware('technicien');
        Route::put('/{id}', [ContratController::class, 'update'])->middleware('technicien');
        Route::delete('/{id}', [ContratController::class, 'destroy'])->middleware('technicien');
    });

    Route::prefix('medecin-controleur')->group(function () {
        Route::middleware(['auth:api', 'medecin_controleur'])->group(function () {
            Route::get('/questions', [QuestionController::class, 'index']);
            Route::get('/questions/{id}', [QuestionController::class, 'show']);
            Route::post('/bulk/questions', [QuestionController::class, 'bulkInsert']);
            Route::put('/bulk/questions', [QuestionController::class, 'bulkUpdate']);
            Route::post('/questions', [QuestionController::class, 'store']);
            Route::put('/questions/{id}', [QuestionController::class, 'update']);
            Route::delete('/bulk/questions', [QuestionController::class, 'bulkDestroy']);
            Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);
            Route::put('/questions/{id}/activate', [QuestionController::class, 'activate']);
        });
    });


    // catégories de garanties
    Route::prefix('categories-garanties')->group(function () {
        Route::get('/', [CategorieGarantieController::class, 'index']);
        Route::get('/{id}', [CategorieGarantieController::class, 'show']);

        Route::middleware('medecin_controleur')->group(function () {
            Route::post('/', [CategorieGarantieController::class, 'store']);
            Route::put('/{id}', [CategorieGarantieController::class, 'update']);
            Route::delete('/{id}', [CategorieGarantieController::class, 'destroy']);
        });
    });

    // garanties
    Route::middleware("auth:api")->prefix('garanties')->group(function () {
        Route::get('/', [GarantieController::class, 'index']);
        Route::get('/{id}', [GarantieController::class, 'show']);

        Route::middleware('medecin_controleur')->group(function () {
            Route::post('/', [GarantieController::class, 'store']);
            Route::put('/{id}', [GarantieController::class, 'update']);
            Route::delete('/{id}', [GarantieController::class, 'destroy']);
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

    Route::middleware(['auth:api'])->prefix('clients')->group(function () {
        Route::get('/', [ClientControlleur::class, 'index']);
        Route::get('/{id}', [ClientControlleur::class, 'show']);
        Route::put('/{id}', [ClientControlleur::class, 'update']);
        Route::delete('/{id}', [ClientControlleur::class, 'destroy']);
    });
});
