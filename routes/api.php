<?php

use App\Http\Controllers\v1\Api\admin\AdminController;
use App\Http\Controllers\v1\Api\Assure\BeneficiaireController;
use App\Http\Controllers\v1\Api\auth\AuthController;
use App\Http\Controllers\v1\Api\categorie_garantie\CategorieGarantieController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\gestionnaire\GestionnaireController;
use App\Http\Controllers\v1\Api\demande_adhesion\DemandeAdhesionController;
use App\Http\Controllers\v1\Api\garanties\GarantieController;
use App\Http\Controllers\v1\Api\medecin_controleur\QuestionController;
use App\Http\Controllers\v1\Api\CommercialController;
use App\Http\Controllers\v1\Api\ComptableController;
use App\Http\Controllers\v1\Api\technicien\TechnicienController;
use App\Http\Controllers\v1\Api\AssureController;
use App\Http\Controllers\v1\Api\statistiques\StatsController;
use App\Helpers\ApiResponse;
use App\Http\Controllers\v1\Api\client\ClientController;
use App\Http\Controllers\v1\Api\client\ClientReseauController;
use App\Http\Controllers\v1\Api\Common\DownloadFileController;
use App\Http\Controllers\v1\Api\contrat\ContratController;
use App\Http\Controllers\v1\Api\entreprise\EntrepriseController;
use App\Http\Controllers\v1\Api\facture\FactureController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\v1\Api\NotificationController;
use App\Http\Controllers\v1\Api\prestataire\PrestataireController;
use App\Http\Controllers\v1\Api\ClientPrestataireController;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Broadcast;

Route::middleware('verifyApiKey')->prefix('v1')->group(function () {

    // ----------------------- Authentification et gestion des mots de passe ---------------------
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']); // 👌
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/me', [AuthController::class, 'getCurrentUser']);
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/forgot-password', [AuthController::class, 'sendOtp']);

        // ----------------------- Gestion des mots de passe ---------------------
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);

        // ----------------------- Gestion des OTP ---------------------
        // Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
        Route::post('/check-unique', [AuthController::class, 'checkUnique']);
    });


    // ---------------------- gestion des gestionnaires par l'admin global -------------------
    Route::middleware(['auth:api', 'checkRole:admin_global'])->prefix('admin')->group(function () {
        // Dashboard global
        Route::get('/stats', [AdminController::class, 'dashboardGlobal']);

        // Gestion des gestionnaires
        Route::prefix('gestionnaires')->group(function () {
            Route::get('/', [AdminController::class, 'indexGestionnaires']);
            Route::post('/', [AdminController::class, 'storeGestionnaire']);
            Route::get('/{id}', [AdminController::class, 'showGestionnaire']);
            Route::patch('/{id}/change-status', [AdminController::class, 'toggleGestionnaireStatus']);
            Route::delete('/{id}', [AdminController::class, 'destroyGestionnaire']);
        });
    });

    // ---------------------- gestion des personnels par le gestionnaire --------------------
    // Lecture : admin + gestionnaire
    Route::middleware(['auth:api'])->prefix('gestionnaire/personnels')->group(function () {
        Route::get('/', [GestionnaireController::class, 'indexPersonnels'])->middleware('checkRole:gestionnaire,admin_global');
        Route::get('/stats', [GestionnaireController::class, 'personnelStats'])->middleware('checkRole:gestionnaire,admin_global');
        Route::get('/{id}', [GestionnaireController::class, 'showPersonnel'])->middleware('checkRole:gestionnaire,admin_global');
        Route::post('/', [GestionnaireController::class, 'storePersonnel'])->middleware('checkRole:gestionnaire');
        Route::patch('/{id}/change-status', [GestionnaireController::class, 'togglePersonnelStatus'])->middleware('checkRole:gestionnaire');
        Route::delete('/{id}', [GestionnaireController::class, 'destroyPersonnel'])->middleware('checkRole:gestionnaire');
    });

    // --------------------- gestion des questions pour les prospects et prestataire par le médecin contrôleur --------------------
    Route::prefix('questions')->group(function () {
        Route::get('/', [QuestionController::class, 'indexQuestions']); // 👌
        Route::get('/stats', [QuestionController::class, 'questionStats'])->middleware(['auth:api', 'checkRole:medecin_controleur']); // 👌
        Route::get('/{id}', [QuestionController::class, 'showQuestion'])->middleware(['auth:api', 'checkRole:medecin_controleur']);; // 👌
        Route::post('/', [QuestionController::class, 'bulkInsertQuestions'])->middleware(['auth:api', 'checkRole:medecin_controleur']);; // 👌
        Route::put('/{id}', [QuestionController::class, 'updateQuestion'])->middleware(['auth:api', 'checkRole:medecin_controleur']);; // 👌
        Route::delete('/{id}', [QuestionController::class, 'destroyQuestion'])->middleware(['auth:api', 'checkRole:medecin_controleur']);; // 👌
        Route::delete('/', [QuestionController::class, 'bulkDestroyQuestions']); // suppression en masse

        // Route::patch('/{id}/toggle', [QuestionController::class, 'toggleQuestionStatus'])->middleware(['auth:api', 'checkRole:medecin_controleur']);;
    });

    // --------------------- Statistiques complètes du médecin contrôleur --------------------
    Route::middleware(['auth:api', 'checkRole:medecin_controleur'])->prefix('medecin-controleur')->group(function () {
        Route::get('/stats', [QuestionController::class, 'medecinControleurStats']);
    });

    // --------------- Gestion des garanties par le médecin contrôleur ------------------
    // ############# Accès lecture : médecin + technicien ##############
    Route::middleware(["auth:api"])->prefix('garanties')->group(function () {
        Route::get('/', [GarantieController::class, 'indexGaranties']); //👌
        Route::get('/{id}', [GarantieController::class, 'showGarantie']);  //👌
        Route::post('/', [GarantieController::class, 'storeGarantie'])->middleware(["checkRole:medecin_controleur,technicien"]); //👌
        Route::put('/{id}', [GarantieController::class, 'updateGarantie'])->middleware(["checkRole:medecin_controleur,technicien"]); //👌
        Route::delete('/{id}', [GarantieController::class, 'destroyGarantie'])->middleware(["checkRole:medecin_controleur,technicien"]);  //👌
        Route::patch('/{id}', [GarantieController::class, 'toggleGarantieStatus'])->middleware(["checkRole:medecin_controleur,technicien"]);  //👌
        Route::delete('/', [GarantieController::class, 'bulkDelete']);
    });

    // --------------- Gestion des catégories de garanties ------------------
    // ############# Accès lecture : médecin + technicien ##############
    Route::middleware(['auth:api'])->prefix('categories-garanties')->group(function () {
        Route::get('/', [CategorieGarantieController::class, 'indexCategorieGarantie']); //👌
        Route::get('/{id}', [CategorieGarantieController::class, 'showCategorieGarantie']); //👌
        Route::post('/', [CategorieGarantieController::class, 'storeCategorieGarantie'])->middleware(["checkRole:medecin_controleur,technicien"]); //👌
        Route::put('/{id}', [CategorieGarantieController::class, 'updateCategorieGarantie'])->middleware(["checkRole:medecin_controleur,technicien"]); //👌
        Route::delete('/{id}', [CategorieGarantieController::class, 'destroyCategorieGarantie'])->middleware(["checkRole:medecin_controleur,technicien"]); //👌
        Route::patch('/{categorieGarantie}/garanties/{garantie}/toggle', [CategorieGarantieController::class, 'toggleCategorieGarantieStatus'])->middleware(["checkRole:medecin_controleur,technicien"]);
    });
    // --------------------- Routes pour le Technicien ---------------------
    Route::middleware(['auth:api', 'checkRole:technicien'])->prefix('technicien')->group(function () {
        Route::get('/dashboard', [TechnicienController::class, 'technicienStats']);
        Route::get('/demandes-adhesion/{id}', [TechnicienController::class, 'showDemande']);
        Route::get('/demandes-adhesion', [TechnicienController::class, 'demandesAdhesion']);

        Route::post('/demandes-adhesion/{id}/proposer-contrat', [TechnicienController::class, 'proposerContrat']);
        Route::get('/factures', [TechnicienController::class, 'factures']);
        Route::post('/factures/{id}/valider', [TechnicienController::class, 'validerFacture']);
        Route::post('/factures/{id}/rejeter', [TechnicienController::class, 'rejeterFacture']);
        Route::get('/factures/{id}', [TechnicienController::class, 'showFacture']);
        Route::get('/clients/{id}', [TechnicienController::class, 'showClientDetails']);
        Route::get('/clients', [TechnicienController::class, 'getClientsTechnicien']);
        Route::get('/clients/stats', [TechnicienController::class, 'getStatistiquesClients']);

        // Routes pour les sinistres (consultation seulement)
        Route::get('/sinistres', [TechnicienController::class, 'sinistres']);
        Route::get('/sinistres/{id}', [TechnicienController::class, 'showSinistre']);

        // Routes pour le réseautage de prestataires
        Route::get('/clients-avec-contrats-acceptes', [TechnicienController::class, 'getClientsAvecContratsAcceptes']);
        Route::get('/prestataires-pour-assignation', [TechnicienController::class, 'getPrestatairesPourAssignation']);
        Route::get('/clients/{id}/assignations', [TechnicienController::class, 'getAssignationsClient']);
        Route::post('/clients/{id}/assignations', [TechnicienController::class, 'assignerReseauPrestataires']);
        // Route::delete('/clients/{id}/assignations/{id}', [TechnicienController::class, 'desassignerPrestataire']);
    });
    // --------------------- Routes pour les demandes d'adhésion ---------------------
    Route::middleware(['auth:api'])->prefix('demandes-adhesions')->group(function () {
        Route::get('/has-demande', [DemandeAdhesionController::class, 'hasDemande'])->middleware('checkRole:client'); //👌
        Route::get('/', [DemandeAdhesionController::class, 'index'])->middleware('checkRole:medecin_controleur,technicien,admin_global,gestionnaire,commercial'); //👌
        Route::get('/{id}', [DemandeAdhesionController::class, 'show'])->middleware('checkRole:medecin_controleur,technicien'); //👌
        Route::post('/client', [DemandeAdhesionController::class, 'storeClientPhysiqueDemande'])->middleware('checkRole:client'); //👌
        Route::post('/prestataire', [DemandeAdhesionController::class, 'storePrestataireDemande'])->middleware('checkRole:prestataire'); //👌
        Route::post('/{id}/valider-client', [TechnicienController::class, 'validerDemande']);
        // Actions sur les demandes (proposer contrat, valider, rejeter)
        Route::put('/{demande_id}/valider-prestataire', [PrestataireController::class, 'validerPrestataire'])->middleware('checkRole:medecin_controleur');
        Route::put('/{demande_id}/rejeter', [DemandeAdhesionController::class, 'rejeter'])->middleware('checkRole:technicien,medecin_controleur');
        Route::put('/{id}/proposer-contrat', [TechnicienController::class, 'proposerContrat'])->middleware('checkRole:technicien');
        // Route::get('/stats', [DemandeAdhesionController::class, 'stats'])->middleware('checkRole:admin_global,medecin_controleur,technicien,gestionnaire,commercial');
    });


    // --------------------- Routes pour les téléchargements ---------------------
    Route::middleware(['auth:api'])->prefix('download')->group(function () {
        Route::get('/demande-adhesion/{id}', [DownloadFileController::class, 'downloadDemandeAdhesion'])->name('api.demandes-adhesions.download');
        // Route pour télécharger une facture en PDF
        Route::get('/factures/{id}/download', [DownloadFileController::class, 'downloadFacture'])
            ->name('factures.download');
        // Route pour prévisualiser une facture en HTML (optionnel)
        Route::get('/factures/{id}/preview', [DownloadFileController::class, 'previewPdf'])
            ->name('factures.preview');
    });



    // --------------------- Routes pour l'Assuré Principal (CLIENT) ---------------------
    Route::middleware(['auth:api', 'checkRole:client'])->prefix('client')->group(function () {
        Route::prefix('beneficiaires')->group(function () {
            Route::get('/', [AssureController::class, 'beneficiaires']);
            Route::get('/{id}', [AssureController::class, 'beneficiaire']);
            Route::post('/', [AssureController::class, 'ajouterBeneficiaire']);
            Route::put('/{id}', [AssureController::class, 'modifierBeneficiaire']);
            Route::delete('/{id}', [AssureController::class, 'supprimerBeneficiaire']);
        });
        Route::get('/mes-contrats', [ClientController::class, 'mesContrats']);
        Route::get('/mes-contrats/{id}', [ClientController::class, 'contratDetails']);
        Route::get('/statistiques', [ClientController::class, 'statistiques']);
        Route::get('/contrats-proposes', [ClientController::class, 'getContratsProposes']);
        Route::get('/stats', [ClientController::class, 'stats']);
        Route::post('/contrats-proposes/{proposition_id}/accepter', [DemandeAdhesionController::class, 'accepterContrat']);
        Route::post('/contrats-proposes/{proposition_id}/refuser', [ClientController::class, 'refuserContrat']);
        // Route::get('/has-active-contrat', [AssureController::class, 'hasActiveContrat']);
        Route::get('/historique-remboursements', [AssureController::class, 'historiqueRemboursements']);
        Route::get('/contrat', [AssureController::class, 'contrat']);
        Route::get('/profil', [AssureController::class, 'profil']);
        Route::put('/profil', [AssureController::class, 'updateProfil']);
        Route::get('/employes', [AssureController::class, 'getEmployeAssure']);

        // reseau
        Route::prefix('reseau')->group(function () {
            Route::get('/mes-prestataires', [ClientReseauController::class, 'mesPrestataires']);
            Route::get('/statistiques', [ClientReseauController::class, 'statistiquesReseau']);
            Route::get('/prestataires/{id}', [ClientReseauController::class, 'detailsPrestataire']);
        });

        // entreprise
        Route::prefix('entreprise')->group(function () {
            Route::get('/lien-invitation', [EntrepriseController::class, 'getInvitationLink']); //👌
            Route::get('/generer-lien-invitation', [EntrepriseController::class, 'genererLienInvitationEmploye']); //👌

            Route::post('/soumettre-demande-adhesion', [EntrepriseController::class, 'soumettreDemandeAdhesionEntreprise']);

            // Routes pour consulter les demandes d'adhésion
            Route::get('/mes-demandes', [EntrepriseController::class, 'getDemandesAdhesions']);

            // Routes pour consulter les demandes des employés
            Route::get('/demandes-employes', [EntrepriseController::class, 'demandesEmployes']);

            // Route pour le dashboard - liste des employés avec leurs demandes
            // Route::get('/employes-avec-demandes', [EntrepriseController::class, 'employesAvecDemandes']);

            // Route pour les statistiques des employés
            Route::get('/statistiques-employes', [EntrepriseController::class, 'statistiquesEmployes']);
        });
    });
    Route::prefix('employes/formulaire')->group(function () {
        Route::get('/{token}', [EntrepriseController::class, 'showFormulaireEmploye']);
        Route::post('/{token}', [EntrepriseController::class, 'soumettreFicheEmploye']);
    });

    // --------------------- Routes pour la gestion des assignations clients-prestataires ---------------------
    Route::middleware(['auth:api', 'checkRole:admin_global,technicien,medecin_controleur'])->prefix('client-prestataires')->group(function () {
        Route::get('/', [ClientPrestataireController::class, 'index']);
        Route::get('/statistiques', [ClientPrestataireController::class, 'statistiques']);
        Route::get('/{id}', [ClientPrestataireController::class, 'show']);

        // Routes pour modification/suppression (admin et technicien uniquement)
        Route::middleware(['checkRole:admin_global,technicien'])->group(function () {
            Route::put('/{id}', [ClientPrestataireController::class, 'update']);
            Route::delete('/{id}', [ClientPrestataireController::class, 'destroy']);
        });
    });

    // Anciennes routes spécifiques supprimées ou commentées pour éviter toute confusion.
    // --------------------- Routes pour les prestataires de soins ---------------------
    Route::middleware(['auth:api', 'checkRole:prestataire'])->prefix('prestataire')->group(function () {

        Route::get('/dashboard', [PrestataireController::class, 'dashboard']);

        Route::get('/assures', [PrestataireController::class, 'getAssure']);
        Route::get('/assures/search', [PrestataireController::class, 'searchAssure']);

        // Routes pour la gestion des sinistres
        Route::prefix('sinistres')->group(function () {
            Route::get('/', [\App\Http\Controllers\v1\Api\prestataire\SinistreController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\v1\Api\prestataire\SinistreController::class, 'store']);
            Route::get('/existing/{id}', [\App\Http\Controllers\v1\Api\prestataire\SinistreController::class, 'existingSinistre']);
            Route::get('/{id}', [\App\Http\Controllers\v1\Api\prestataire\SinistreController::class, 'show']);
            Route::post('/{id}/facture', [\App\Http\Controllers\v1\Api\prestataire\SinistreController::class, 'createFacture']);
        });
    });

    Route::middleware(['auth:api'])->prefix('contrats')->group(function () {
        Route::get('/', [ContratController::class, 'index']);
        Route::get('/categories-garanties', [ContratController::class, 'getCategoriesGaranties']);

        Route::middleware(['checkRole:technicien,medecin_controleur'])->group(function () {
            Route::get('/stats', [ContratController::class, 'stats']);
            Route::post('/', [ContratController::class, 'store']);
            Route::get('/{id}', [ContratController::class, 'show']);
            Route::put('/{id}', [ContratController::class, 'update']);
            Route::delete('/{id}', [ContratController::class, 'destroy']);
        });
    });

    // --- Demande d'adhésion personne client ---
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

    // --------------------- Routes pour les notifications ---------------------
    Route::middleware(['auth:api'])->prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/stats', [NotificationController::class, 'stats']);
        Route::patch('/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::patch('/{id}/mark-as-unread', [NotificationController::class, 'markAsUnread']);
        Route::patch('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/destroy-read', [NotificationController::class, 'destroyRead']);
    });

    // --------------------- Routes pour l'acceptation des contrats par les clients -------------------
    Route::prefix('contrats')->group(function () {
        // Accepter un contrat (accessible sans authentification via le token)
        Route::post('/accepter/{token}', [DemandeAdhesionController::class, 'accepterContrat']);
    });
    // Statistiques des assurés
    Route::middleware(['auth:api', 'checkRole:admin_global,medecin_controleur,technicien,comptable'])->prefix('assures')->group(function () {
        Route::get('/stats', [StatsController::class, 'getAssureStats']);
    });

    // Statistiques du dashboard adaptées au rôle
    Route::middleware(['auth:api'])->prefix('dashboard')->group(function () {
        Route::get('/stats', [StatsController::class, 'dashboardStats']);
    });

    // --------------------- Routes pour le Commercial ---------------------
    Route::middleware(['auth:api', 'checkRole:commercial'])->prefix('commercial')->group(function () {
        Route::get('/dashboard', [CommercialController::class, 'dashboard']);
        Route::get('/prospects', [CommercialController::class, 'prospects']);
        Route::get('/statistiques', [CommercialController::class, 'statistiques']);
        Route::get('/commissions', [CommercialController::class, 'commissions']);
        Route::get('/prospects/{id}', [CommercialController::class, 'showProspect']);
        Route::put('/prospects/{id}', [CommercialController::class, 'updateProspect']);

        // Routes pour le système de parrainage
        Route::post('/generer-code-parrainage', [\App\Http\Controllers\v1\Api\commercial\CommercialController::class, 'genererCodeParrainage']);
        Route::get('/mon-code-parrainage', [\App\Http\Controllers\v1\Api\commercial\CommercialController::class, 'monCodeParrainage']);
        Route::get('/historique-codes-parrainage', [\App\Http\Controllers\v1\Api\commercial\CommercialController::class, 'historiqueCodesParrainage']);
        Route::post('/renouveler-code-parrainage', [\App\Http\Controllers\v1\Api\commercial\CommercialController::class, 'renouvelerCodeParrainage']);
        Route::post('/creer-compte-client', [\App\Http\Controllers\v1\Api\commercial\CommercialController::class, 'creerCompteClient']);
        Route::get('/mes-clients-parraines', [\App\Http\Controllers\v1\Api\commercial\CommercialController::class, 'mesClientsParraines']);
        Route::get('/mes-statistiques', [\App\Http\Controllers\v1\Api\commercial\CommercialController::class, 'mesStatistiques']);
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



    Route::middleware(['auth:api', 'checkRole:entreprise'])->prefix('entreprise')->group(function () {
        Route::get('/demandes-adhesions', [EntrepriseController::class, 'getDemandesAdhesions']);
    });


    // --------------------- Routes pour les Prestataires (Réseau de Clients) ---------------------
    Route::middleware(['auth:api', 'checkRole:prestataire'])->prefix('prestataire/reseau')->group(function () {
        Route::get('/mes-clients', [\App\Http\Controllers\v1\Api\prestataire\PrestataireReseauController::class, 'mesClients']);
        Route::get('/statistiques', [\App\Http\Controllers\v1\Api\prestataire\PrestataireReseauController::class, 'statistiquesClients']);
    });

    // --------------------- Routes pour récupérer tous les assurés ---------------------
    Route::middleware(['auth:api', 'checkRole:technicien,medecin_controleur,comptable,admin_global,gestionnaire'])->prefix('personnel')->group(function () {
        Route::get('/assures', [TechnicienController::class, 'getAllAssures']);
    });

    // --------------------- Routes pour les factures ---------------------
    Route::middleware(['auth:api', 'checkRole:prestataire,technicien,comptable,medecin_controleur,entreprise,client,admin_global,gestionnaire'])->prefix('factures')->group(function () {
        // Routes de consultation
        Route::get('/', [FactureController::class, 'factures']);
        Route::get('/stats', [FactureController::class, 'stats']);
        Route::get('/{id}', [FactureController::class, 'showFacture']);

        // --------------------- Routes pour la validation des factures ---------------------

        // Validation par technicien
        Route::post('/{factureId}/validate-technicien', [\App\Http\Controllers\v1\Api\facture\FactureValidationController::class, 'validateByTechnicien'])
            ->middleware('checkRole:technicien');
        Route::post('/{factureId}/reject-technicien', [\App\Http\Controllers\v1\Api\facture\FactureValidationController::class, 'rejectByTechnicien'])
            ->middleware('checkRole:technicien');

        // Validation par médecin contrôleur
        Route::post('/{factureId}/validate-medecin', [\App\Http\Controllers\v1\Api\facture\FactureValidationController::class, 'validateByMedecin'])
            ->middleware('checkRole:medecin_controleur');
        Route::post('/{factureId}/reject-medecin', [\App\Http\Controllers\v1\Api\facture\FactureValidationController::class, 'rejectByMedecin'])
            ->middleware('checkRole:medecin_controleur');

        // Autorisation par comptable
        Route::post('/{factureId}/authorize-comptable', [\App\Http\Controllers\v1\Api\facture\FactureValidationController::class, 'authorizeByComptable'])
            ->middleware('checkRole:comptable');
        Route::post('/{factureId}/reject-comptable', [\App\Http\Controllers\v1\Api\facture\FactureValidationController::class, 'rejectByComptable'])
            ->middleware('checkRole:comptable');
        Route::post('/{factureId}/mark-reimbursed', [\App\Http\Controllers\v1\Api\facture\FactureValidationController::class, 'markAsReimbursed'])
            ->middleware('checkRole:comptable');

        // Modification de facture rejetée (prestataire)
        Route::put('/{factureId}/update-rejected', [\App\Http\Controllers\v1\Api\facture\FactureValidationController::class, 'updateRejectedFacture'])
            ->middleware('checkRole:prestataire');

        // Historique de validation (accessible à tous les rôles autorisés)
        Route::get('/{factureId}/validation-history', [\App\Http\Controllers\v1\Api\facture\FactureValidationController::class, 'getValidationHistory']);
    });

    // Routes de broadcasting - en dehors du groupe middleware verifyApiKey
    Route::post('/v1/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    })->middleware('auth:api');
});
