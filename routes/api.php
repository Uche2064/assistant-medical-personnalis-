<?php

use App\Http\Controllers\v1\Api\admin\AdminController;
use App\Http\Controllers\v1\Api\Assure\BeneficiaireController;
use App\Http\Controllers\v1\Api\auth\AuthController;
use App\Http\Controllers\v1\Api\auth\ForgotPasswordController;
use App\Http\Controllers\v1\Api\categorie_garantie\CategorieGarantieController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\ClientControlleur;
use App\Http\Controllers\v1\Api\gestionnaire\GestionnaireController;
use App\Http\Controllers\v1\Api\ContratController;
use App\Http\Controllers\v1\Api\demande_adhesion\DemandeAdhesionController;
use App\Http\Controllers\v1\Api\garanties\GarantieController;
use App\Http\Controllers\v1\Api\medecin_controleur\QuestionController;
use App\Http\Controllers\v1\Api\PrestataireController;
use App\Http\Controllers\v1\Api\SoumissionEmployeController;
use Illuminate\Support\Facades\Auth;

Route::middleware('verifyApiKey')->prefix('v1')->group(function () {




    // ----------------------- Authentification et gestion des mots de passe ---------------------
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/check-unique', [AuthController::class, 'checkUnique']);
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
        Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
        Route::get('/me', [AuthController::class, 'getCurrentUser'])->middleware('auth:api');
        Route::get('/test-roles', [AuthController::class, 'testRoles'])->middleware('auth:api');
    });




    // ---------------------- gestion des gestionnaires par l'admin global -------------------

    Route::middleware(['auth:api', 'checkRole:admin_global'])->prefix('admin/gestionnaires')->group(function () {
        Route::post('/', [AdminController::class, 'storeGestionnaire']);
        Route::get('/', [AdminController::class, 'indexGestionnaires']);
        Route::get('/stats', [AdminController::class, 'gestionnaireStats']);
        Route::get('/{id}', [AdminController::class, 'showGestionnaire']);
        Route::patch('/{id}/suspend', [AdminController::class, 'suspendGestionnaire']);
        Route::patch('/{id}/activate', [AdminController::class, 'activateGestionnaire']);
        Route::delete('/{id}', [AdminController::class, 'destroyGestionnaire']);
    });

    // ---------------------- gestion des personnels par le gestionnaire --------------------

    // Lecture : admin + gestionnaire
    Route::middleware(['auth:api', 'checkRole:gestionnaire,admin_global'])->prefix('gestionnaire/personnels')->group(function () {
        Route::get('/', [GestionnaireController::class, 'indexPersonnels']);
        Route::get('/stats', [GestionnaireController::class, 'personnelStats']);
        Route::get('/{id}', [GestionnaireController::class, 'showPersonnel']);
    });

    // Écriture : uniquement gestionnaire
    Route::middleware(['auth:api', 'checkRole:gestionnaire'])->prefix('gestionnaire/personnels')->group(function () {
        Route::post('/', [GestionnaireController::class, 'storePersonnel']);
        Route::patch('/{id}/suspend', [GestionnaireController::class, 'suspendPersonnel']);
        Route::patch('/{id}/activate', [GestionnaireController::class, 'activatePersonnel']);
        Route::delete('/{id}', [GestionnaireController::class, 'destroyPersonnel']);
    });


    // --------------------- gestion des questions pour les prospects et prestataire par le médecin contrôleur --------------------

    Route::get('/questions', [QuestionController::class, 'getQuestionsByDestinataire']);
    Route::get('/has-demande', [DemandeAdhesionController::class, 'hasDemande'])->middleware('auth:api');


    Route::middleware(['auth:api', 'checkRole:medecin_controleur'])->prefix('questions')->group(function () {
        Route::get('/all', [QuestionController::class, 'indexQuestions']); // toutes les questions
        Route::get('/{id}', [QuestionController::class, 'showQuestion']); // toutes les questions
        Route::post('/', [QuestionController::class, 'bulkInsertQuestions']);
        Route::put('/{id}', [QuestionController::class, 'updateQuestion']);
        Route::patch('/{id}/toggle', [QuestionController::class, 'toggleQuestionStatus']);
        Route::delete('/{id}', [QuestionController::class, 'destroyQuestion']); // suppression simple
        Route::post('/bulk-delete', [QuestionController::class, 'bulkDestroyQuestions']); // suppression en masse
    });



    Route::middleware(['auth:api'])->prefix('demandes-adhesions')->group(function () {
        Route::get('demandes-adhesions/{id}/download', [DemandeAdhesionController::class, 'download'])->name('api.demandes-adhesions.download');
        Route::post('/', [DemandeAdhesionController::class, 'store'])->middleware('checkRole:user');
        Route::post('/prestataire', [DemandeAdhesionController::class, 'soumettreDemandeAdhesionPrestataire'])->middleware('checkRole:prestataire');
        Route::post('/entreprise', [DemandeAdhesionController::class, 'soumettreDemandeAdhesionEntreprise'])->middleware('checkRole:user');

        Route::get('/', [DemandeAdhesionController::class, 'index'])->middleware('checkRole:medecin_controleur,technicien,admin_global');
        Route::get('/{id}', [DemandeAdhesionController::class, 'show'])->middleware('checkRole:medecin_controleur,technicien,admin_global');

        Route::put('/{demande_id}/proposer-contrat', [DemandeAdhesionController::class, 'proposerContrat'])->middleware('checkRole:technicien');
        Route::put('/{demande_id}/valider-prestataire', [DemandeAdhesionController::class, 'validerPrestataire'])->middleware('checkRole:medecin_controleur');
        Route::put('/{demande_id}/rejeter', [DemandeAdhesionController::class, 'rejeter'])
            ->middleware('checkRole:technicien,medecin_controleur');
    });

    // --------------------- Routes pour les prestataires de soins ---------------------
    Route::middleware(['auth:api', 'checkRole:prestataire'])->prefix('prestataire')->group(function () {
        Route::get('/dashboard', [PrestataireController::class, 'dashboard']);
        Route::get('/profile', [PrestataireController::class, 'getProfile']);
        Route::put('/profile', [PrestataireController::class, 'updateProfile']);
        Route::get('/questions', [PrestataireController::class, 'getQuestions']);
        Route::get('/documents-requis', [PrestataireController::class, 'getDocumentsRequis']);
        Route::post('/valider-documents', [PrestataireController::class, 'validerDocuments']);
        Route::post('/demande-adhesion', [PrestataireController::class, 'soumettreDemandeAdhesion']);
    });


    // --------------- Gestion des catégories de garanties ------------------
    // ############# Accès lecture : médecin + technicien ##############
    Route::middleware(['auth:api', 'checkRole:medecin_controleur,technicien'])->prefix('categories-garanties')->group(function () {
        Route::get('/', [CategorieGarantieController::class, 'indexCategorieGarantie']);
        Route::get('/{id}', [CategorieGarantieController::class, 'showCategorieGarantie']);
    });

    // ############## Accès écriture : réservé au médecin contrôleur ##########
    Route::middleware(['auth:api', 'checkRole:medecin_controleur'])->prefix('categories-garanties')->group(function () {
        Route::post('/', [CategorieGarantieController::class, 'storeCategorieGarantie']);
        Route::put('/{id}', [CategorieGarantieController::class, 'updateCategorieGarantie']);
        Route::delete('/{id}', [CategorieGarantieController::class, 'destroyCategorieGarantie']);
    });



    // --------------- Gestion des garanties par le médecin contrôleur ------------------
    // ############# Accès lecture : médecin + technicien ##############
    Route::middleware(["auth:api", "checkRole:medecin_controleur,technicien"])->prefix('garanties')->group(function () {
        Route::get('/', [GarantieController::class, 'indexGaranties']);
        Route::get('/{id}', [GarantieController::class, 'showGarantie']);

        Route::middleware('checkRole:medecin_controleur')->group(function () {
            Route::post('/', [GarantieController::class, 'storeGarantie']);
            Route::put('/{id}', [GarantieController::class, 'updateGarantie']);
            Route::delete('/{id}', [GarantieController::class, 'destroyGarantie']);
        });
    });

    Route::middleware(['auth:api'])->prefix('contrats')->group(function () {
        Route::get('/', [ContratController::class, 'index']);
        Route::post('/', [ContratController::class, 'store'])->middleware('checkRole:technicien');
        Route::get('/{id}', [ContratController::class, 'show'])->middleware('checkRole:technicien');
        Route::put('/{id}', [ContratController::class, 'update'])->middleware('checkRole:technicien');
        Route::delete('/{id}', [ContratController::class, 'destroy'])->middleware('checkRole:technicien');
    });

    Route::prefix('employes/formulaire')->group(function () {
        Route::get('{token}', [SoumissionEmployeController::class, 'showForm']);
        Route::post('{token}', [SoumissionEmployeController::class, 'store']);
    });

    // --- Gestion des invitations employé pour les entreprises ---
    Route::middleware(['auth:api', 'checkRole:user'])->prefix('entreprise')->group(function () {
        Route::post('/inviter-employe', [DemandeAdhesionController::class, 'genererLienInvitationEmploye']);
        Route::post('/soumettre-demande-adhesion', [DemandeAdhesionController::class, 'soumettreDemandeAdhesionEntreprise']);
    });
    Route::prefix('employes/formulaire')->group(function () {
        Route::get('/{token}', [DemandeAdhesionController::class, 'showFormulaireEmploye']);
        Route::post('/{token}', [DemandeAdhesionController::class, 'soumettreFicheEmploye']);
    });

    // --- Demande d'adhésion personne physique ---
    Route::middleware(['auth:api', 'checkRole:user'])->post('/demandes-adhesion', [DemandeAdhesionController::class, 'store']);



    // --- Anciennes routes inutiles pour la demande d'adhésion (commentées) ---
    // Route::middleware('auth:api')->prefix('demandes-adhesion')->group(function () {
    //     Route::get('demandes-adhesions/{id}/download', [DemandeAdhesionController::class, 'download'])->name('api.demandes-adhesions.download');
    //     Route::post('/', [DemandeAdhesionController::class, 'store'])->middleware('role:user');
    //     Route::post('/p', [DemandeAdhesionController::class, 'storePrestataire'])->middleware('role:user');
    //     Route::post('/e', [DemandeAdhesionController::class, 'storeEntreprise'])->middleware('role:user');
    //     Route::get('/', [DemandeAdhesionController::class, 'index'])->middleware('role:medecin_controleur|technicien|admin_global');
    //     Route::get('/{id}', [DemandeAdhesionController::class, 'show'])->middleware('role:medecin_controleur|technicien|admin_global');
    //     Route::put('/{demande_id}/valider-prospect', [DemandeAdhesionController::class, 'validerProspect'])->middleware('role:technicien');
    //     Route::put('/{demande_id}/valider-prestataire', [DemandeAdhesionController::class, 'validerPrestataire'])->middleware('role:medecin_controleur');
    //     Route::put('/{demande_id}/rejeter', [DemandeAdhesionController::class, 'rejeter'])
    //         ->middleware('role:technicien|medecin_controleur');
    // });

    // ---------------------- Routes pour l'acceptation des contrats par les clients -------------------
    Route::prefix('contrats')->group(function () {
        // Accepter un contrat (accessible sans authentification via le token)
        Route::post('/accepter/{token}', [DemandeAdhesionController::class, 'accepterContrat']);
    });

    Route::middleware(['auth:api', 'checkRole:assure_principal'])->prefix('assure/beneficiaires')->group(function () {
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
