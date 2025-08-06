<?php

use App\Http\Controllers\v1\Api\admin\AdminController;
use App\Http\Controllers\v1\Api\Assure\BeneficiaireController;
use App\Http\Controllers\v1\Api\auth\AuthController;
use App\Http\Controllers\v1\Api\auth\ForgotPasswordController;
use App\Http\Controllers\v1\Api\categorie_garantie\CategorieGarantieController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\ClientControlleur;
use App\Http\Controllers\v1\Api\gestionnaire\GestionnaireController;
use App\Http\Controllers\v1\Api\demande_adhesion\DemandeAdhesionController;
use App\Http\Controllers\v1\Api\garanties\GarantieController;
use App\Http\Controllers\v1\Api\medecin_controleur\QuestionController;
use App\Http\Controllers\v1\Api\PrestataireController;
use App\Http\Controllers\v1\Api\SoumissionEmployeController;
use App\Http\Controllers\v1\Api\CommercialController;
use App\Http\Controllers\v1\Api\ComptableController;
use App\Http\Controllers\v1\Api\TechnicienController;
use App\Http\Controllers\v1\Api\AssureController;
use App\Http\Controllers\v1\Api\StatsController;
use App\Helpers\ApiResponse;
use App\Http\Controllers\v1\Api\contrat\ContratController;
use Illuminate\Support\Facades\Auth;

// Route publique pour les fichiers (en dehors du middleware verifyApiKey)
Route::get('/v1/files/{filename}', function ($filename) {
    // Sécuriser le nom de fichier
    $filename = basename($filename);
    
    $path = storage_path('app/public/uploads/' . $filename);
    
    if (!file_exists($path)) {
        abort(404, 'Fichier non trouvé');
    }
    
    // Vérifier que c'est bien dans le dossier uploads
    $realPath = realpath($path);
    $uploadsDir = realpath(storage_path('app/public/uploads'));
    
    if (!$realPath || strpos($realPath, $uploadsDir) !== 0) {
        abort(403, 'Accès interdit');
    }
    
    return response()->file($path);
})->where('filename', '.*');

Route::middleware('verifyApiKey')->prefix('v1')->group(function () {


    // ----------------------- Authentification et gestion des mots de passe ---------------------
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/me', [AuthController::class, 'getCurrentUser']);
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);

        // ----------------------- Gestion des mots de passe ---------------------
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
        Route::post('/verify-reset-otp', [ForgotPasswordController::class, 'verifyOtp']);
        Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

        // ----------------------- Gestion des OTP ---------------------
        // Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        // Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/check-unique', [AuthController::class, 'checkUnique']);

        Route::get('/test-roles', [AuthController::class, 'testRoles'])->middleware('auth:api');
    });


    // ---------------------- gestion des gestionnaires par l'admin global -------------------

    Route::middleware(['auth:api', 'checkRole:admin_global'])->prefix('admin/gestionnaires')->group(function () {
        Route::get('/', [AdminController::class, 'indexGestionnaires']);
        Route::post('/', [AdminController::class, 'storeGestionnaire']);
        Route::get('/stats', [AdminController::class, 'gestionnaireStats']);
        Route::get('/{id}', [AdminController::class, 'showGestionnaire']);
        Route::patch('/{id}/change-status', [AdminController::class, 'toggleGestionnaireStatus']);
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
        Route::patch('/{id}/change-status', [GestionnaireController::class, 'togglePersonnelStatus']);
        Route::delete('/{id}', [GestionnaireController::class, 'destroyPersonnel']);

    });


    // --------------------- gestion des questions pour les prospects et prestataire par le médecin contrôleur --------------------

    


    Route::middleware(['auth:api', 'checkRole:medecin_controleur,admin_global'])->prefix('questions')->group(function () {
        Route::get('/all', [QuestionController::class, 'indexQuestions']); // toutes les questions
        Route::get('/stats', [QuestionController::class, 'questionStats']); // statistiques des questions
        Route::get('/{id}', [QuestionController::class, 'showQuestion']); // toutes les questions
        Route::post('/', [QuestionController::class, 'bulkInsertQuestions']);
        Route::put('/{id}', [QuestionController::class, 'updateQuestion']);
        Route::patch('/{id}/toggle', [QuestionController::class, 'toggleQuestionStatus']);
        Route::delete('/{id}', [QuestionController::class, 'destroyQuestion']); // suppression simple
        Route::post('/bulk-delete', [QuestionController::class, 'bulkDestroyQuestions']); // suppression en masse
    });
    Route::get('/questions', [QuestionController::class, 'getQuestionsByDestinataire']); // questions par destinataire


    // --------------------- Routes pour les demandes d'adhésion ---------------------

    Route::get('/has-demande', [DemandeAdhesionController::class, 'hasDemande'])->middleware('auth:api');

    Route::middleware(['auth:api'])->prefix('demandes-adhesions')->group(function () {
        Route::get('/', [DemandeAdhesionController::class, 'index'])->middleware('checkRole:medecin_controleur,technicien,admin_global');
        // Demande d'adhésion personne physique (assuré principal)
        Route::post('/', [DemandeAdhesionController::class, 'store'])->middleware('checkRole:physique');

        Route::get('/stats', [DemandeAdhesionController::class, 'stats'])->middleware('checkRole:admin_global,medecin_controleur,technicien');

        // Demande d'adhésion prestataire de soins
        Route::post('/prestataire', [DemandeAdhesionController::class, 'store'])->middleware('checkRole:prestataire');
        // Demande d'adhésion entreprise (soumission groupée)
        Route::post('/entreprise', [DemandeAdhesionController::class, 'soumettreDemandeAdhesionEntreprise'])->middleware('checkRole:entreprise');

        // Téléchargement PDF, listing, détails, etc.
        Route::get('/{id}/download', [DemandeAdhesionController::class, 'download'])->name('api.demandes-adhesions.download');
        Route::get('/{id}', [DemandeAdhesionController::class, 'show'])->middleware('checkRole:medecin_controleur,technicien,admin_global');

        // Actions sur les demandes (proposer contrat, valider, rejeter)
        Route::put('/{demande_id}/proposer-contrat', [DemandeAdhesionController::class, 'proposerContrat'])->middleware('checkRole:technicien');
        Route::put('/{demande_id}/valider-prestataire', [DemandeAdhesionController::class, 'validerPrestataire'])->middleware('checkRole:medecin_controleur');
        Route::put('/{demande_id}/rejeter', [DemandeAdhesionController::class, 'rejeter'])->middleware('checkRole:technicien,medecin_controleur');
    });
    // Anciennes routes spécifiques supprimées ou commentées pour éviter toute confusion.

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
       Route::middleware(['auth:api', 'checkRole:technicien'])->group(function () {
        Route::get('/stats', [ContratController::class, 'stats']);
        Route::post('/', [ContratController::class, 'store']);
        Route::get('/{id}', [ContratController::class, 'show']);
        Route::put('/{id}', [ContratController::class, 'update']);
        Route::delete('/{id}', [ContratController::class, 'destroy']);
       });
    });

    Route::prefix('employes/formulaire')->group(function () {
        Route::get('{token}', [SoumissionEmployeController::class, 'showForm']);
        Route::post('{token}', [SoumissionEmployeController::class, 'store']);
    });

    // --- Gestion des invitations employé pour les entreprises ---
    Route::middleware(['auth:api', 'checkRole:entreprise'])->prefix('entreprise')->group(function () {
        Route::get('/liens-invitation', [DemandeAdhesionController::class, 'consulterLiensInvitation']);
        Route::get('/generer-lien', [DemandeAdhesionController::class, 'genererLienInvitationEmploye']);
        Route::get('/get-invitation-link', [DemandeAdhesionController::class, 'getInvitationLink']);
        Route::post('/soumettre-demande-adhesion', [DemandeAdhesionController::class, 'soumettreDemandeAdhesionEntreprise']);
        
        // Routes pour consulter les demandes d'adhésion
        Route::get('/mes-demandes', [DemandeAdhesionController::class, 'mesDemandesAdhesion']);
        Route::get('/mes-demandes/{id}', [DemandeAdhesionController::class, 'maDemandeAdhesion']);
        
        // Routes pour consulter les demandes des employés
        Route::get('/demandes-employes', [DemandeAdhesionController::class, 'demandesEmployes']);
        Route::get('/demandes-employes/{id}', [DemandeAdhesionController::class, 'demandeEmploye']);
    });
    Route::prefix('employes/formulaire')->group(function () {
        Route::get('/{token}', [DemandeAdhesionController::class, 'showFormulaireEmploye']);
        Route::post('/{token}', [DemandeAdhesionController::class, 'soumettreFicheEmploye']);
    });
  

    // --- Demande d'adhésion personne physique ---

    // Route pour servir les fichiers uploadés (publique)
    Route::get('/files/{filename}', function ($filename) {
        // Sécuriser le nom de fichier
        $filename = basename($filename);
        
        $path = storage_path('app/public/uploads/' . $filename);
        
        if (!file_exists($path)) {
            abort(404, 'Fichier non trouvé');
        }
        
        // Vérifier que c'est bien dans le dossier uploads
        $realPath = realpath($path);
        $uploadsDir = realpath(storage_path('app/public/uploads'));
        
        if (!$realPath || strpos($realPath, $uploadsDir) !== 0) {
            abort(403, 'Accès interdit');
        }
        
        return response()->file($path);
    })->where('filename', '.*');

    // Route pour télécharger les fichiers de manière sécurisée
    Route::middleware(['auth:api'])->prefix('download')->group(function () {
        Route::get('/file/{filename}', function ($filename) {
            $path = storage_path('app/public/uploads/' . $filename);
            
            if (!file_exists($path)) {
                return ApiResponse::error('Fichier non trouvé', 404);
            }
            
            return response()->download($path);
        })->where('filename', '.*');
    });

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

    // --------------------- Routes pour l'Assuré Principal (PHYSIQUE) ---------------------
    Route::middleware(['auth:api', 'checkRole:physique'])->prefix('assure')->group(function () {
        Route::get('/dashboard', [AssureController::class, 'dashboard']);
        Route::get('/beneficiaires', [AssureController::class, 'beneficiaires']);
        Route::post('/beneficiaires', [AssureController::class, 'ajouterBeneficiaire']);
        Route::put('/beneficiaires/{id}', [AssureController::class, 'modifierBeneficiaire']);
        Route::delete('/beneficiaires/{id}', [AssureController::class, 'supprimerBeneficiaire']);
        Route::get('/centres-soins', [AssureController::class, 'centresSoins']);
        Route::get('/historique-remboursements', [AssureController::class, 'historiqueRemboursements']);
        Route::get('/contrat', [AssureController::class, 'contrat']);
        Route::get('/profil', [AssureController::class, 'profil']);
        Route::put('/profil', [AssureController::class, 'updateProfil']);
    });

    // Statistiques des assurés
    Route::middleware(['auth:api', 'checkRole:admin_global,medecin_controleur,technicien'])->prefix('assures')->group(function () {
        Route::get('/stats', [AssureController::class, 'assureStats']);
    });

    // Statistiques du dashboard adaptées au rôle
    Route::middleware(['auth:api'])->prefix('dashboard')->group(function () {
        Route::get('/stats', [StatsController::class, 'dashboardStats']);
    });

    Route::middleware(['auth:api'])->prefix('clients')->group(function () {
        Route::get('/', [ClientControlleur::class, 'index']);
        Route::get('/stats', [ClientControlleur::class, 'clientStats']);
        Route::get('/{id}', [ClientControlleur::class, 'show']);
        Route::put('/{id}', [ClientControlleur::class, 'update']);
        Route::delete('/{id}', [ClientControlleur::class, 'destroy']);
    });

    // --------------------- Routes pour le Commercial ---------------------
    Route::middleware(['auth:api', 'checkRole:commercial'])->prefix('commercial')->group(function () {
        Route::get('/dashboard', [CommercialController::class, 'dashboard']);
        Route::get('/prospects', [CommercialController::class, 'prospects']);
        Route::post('/generer-code-parrainage', [CommercialController::class, 'genererCodeParrainage']);
        Route::get('/statistiques', [CommercialController::class, 'statistiques']);
        Route::get('/commissions', [CommercialController::class, 'commissions']);
        Route::get('/prospects/{id}', [CommercialController::class, 'showProspect']);
        Route::put('/prospects/{id}', [CommercialController::class, 'updateProspect']);
    });

    // --------------------- Routes pour le Comptable ---------------------
    Route::middleware(['auth:api', 'checkRole:comptable'])->prefix('comptable')->group(function () {
        Route::get('/dashboard', [ComptableController::class, 'dashboard']);
        Route::get('/factures', [ComptableController::class, 'factures']);
        Route::post('/factures/{id}/valider-remboursement', [ComptableController::class, 'validerRemboursement']);
        Route::post('/factures/{id}/effectuer-remboursement', [ComptableController::class, 'effectuerRemboursement']);
        Route::post('/factures/{id}/rejeter', [ComptableController::class, 'rejeterFacture']);
        Route::get('/rapports', [ComptableController::class, 'rapports']);
        Route::get('/factures/{id}', [ComptableController::class, 'showFacture']);
        Route::get('/statistiques-prestataires', [ComptableController::class, 'statistiquesPrestataires']);
    });

    // --------------------- Routes pour le Technicien ---------------------
    Route::middleware(['auth:api', 'checkRole:technicien'])->prefix('technicien')->group(function () {
        Route::get('/dashboard', [TechnicienController::class, 'dashboard']);
        Route::get('/demandes-adhesion', [TechnicienController::class, 'demandesAdhesion']);
        Route::post('/demandes-adhesion/{id}/valider', [TechnicienController::class, 'validerDemande']);
        Route::post('/demandes-adhesion/{id}/rejeter', [TechnicienController::class, 'rejeterDemande']);
        Route::post('/demandes-adhesion/{id}/proposer-contrat', [TechnicienController::class, 'proposerContrat']);
        Route::get('/factures', [TechnicienController::class, 'factures']);
        Route::post('/factures/{id}/valider', [TechnicienController::class, 'validerFacture']);
        Route::post('/factures/{id}/rejeter', [TechnicienController::class, 'rejeterFacture']);
        Route::get('/demandes-adhesion/{id}', [TechnicienController::class, 'showDemande']);
        Route::get('/factures/{id}', [TechnicienController::class, 'showFacture']);
    });
});
