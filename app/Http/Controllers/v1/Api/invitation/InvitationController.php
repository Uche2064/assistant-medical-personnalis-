<?php

namespace App\Http\Controllers\v1\Api\invitation;

use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\demande_adhesion\SoumissionEmployeFormRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Assure;
use App\Models\InvitationEmploye;
use App\Models\Question;
use App\Services\DemandeAdhesionService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvitationController extends Controller
{
    protected NotificationService $notificationService;
    protected DemandeAdhesionService $demandeAdhesionService;

    public function __construct(
        NotificationService $notificationService,
        DemandeAdhesionService $demandeAdhesionService
    ) {
        $this->notificationService = $notificationService;
        $this->demandeAdhesionService = $demandeAdhesionService;
    }

    /**
     * Consulter les liens d'invitation existants pour une entreprise
     */
    public function consulterLiensInvitation(Request $request)
    {
        return $this->demandeAdhesionService->getLiensInvitation(Auth::user());
    }

    /**
     * Récupérer le lien d'invitation actif pour une entreprise
     */
    public function getInvitationLink(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('entreprise')) {
            return ApiResponse::error('Seules les entreprises peuvent générer un lien d\'invitation.', 403);
        }
        $invitation = InvitationEmploye::where('entreprise_id', $user->entreprise->id)
            ->where('expire_at', '>', now())
            ->first();
        return ApiResponse::success([
            'invitation_id' => $invitation->id,
            'token' => $invitation->token,
            'url' => config('app.frontend_url') . "/employes/formulaire/{$invitation->token}",
            'expire_at' => $invitation->expire_at,
        ], 'Lien d\'invitation récupéré avec succès.');
    }

    /**
     * Générer un lien d'invitation unique pour qu'un employé remplisse sa fiche d'adhésion
     */
    public function genererLienInvitationEmploye(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('entreprise')) {
            return ApiResponse::error('Seules les entreprises peuvent générer un lien d\'invitation.', 403);
        }
        
        $entrepriseId = $user->entreprise->id;
        $invitation = InvitationEmploye::where('entreprise_id', $entrepriseId)
            ->where('expire_at', '>', now())
            ->first();
            
        if ($invitation) {
            $url = config('app.frontend_url') . "/employes/formulaire/{$invitation->token}";
            return ApiResponse::success([
                'invitation_id' => $invitation->id,
                'url' => $url,
                'token' => $invitation->token,
                'expire_at' => $invitation->expire_at,
            ], 'Lien d\'invitation déjà existant.');
        }
        
        $invitation = InvitationEmploye::create([
            'entreprise_id' => $entrepriseId,
            'token' => InvitationEmploye::generateToken(),
            'expire_at' => now()->addDays(7),
        ]);
        
        $url = config('app.frontend_url') . "/employes/formulaire/{$invitation->token}";
        return ApiResponse::success([
            'invitation_id' => $invitation->id,
            'token' => $invitation->token,
            'url' => $url,
            'expire_at' => $invitation->expire_at,
        ], 'Nouveau lien d\'invitation généré avec succès.');
    }

    /**
     * Afficher le formulaire d'adhésion employé via le token d'invitation
     */
    public function showFormulaireEmploye($token)
    {
        $invitation = InvitationEmploye::where('token', $token)
            ->where('expire_at', '>', now())
            ->first();
            
        if (!$invitation) {
            return ApiResponse::error('Lien d\'invitation invalide ou expiré.', 404);
        }
        
        $questions = Question::active()->byDestinataire(TypeDemandeurEnum::CLIENT->value)->get();
        
        return ApiResponse::success([
            'entreprise' => $invitation->entreprise,
            'token' => $token,
            'questions' => QuestionResource::collection($questions),
        ], 'Formulaire employé prêt à être rempli.');
    }

    /**
     * Soumettre la fiche employé via le lien d'invitation
     */
    public function soumettreFicheEmploye(SoumissionEmployeFormRequest $request, $token)
    {
        $invitation = InvitationEmploye::where('token', $token)
            ->where('expire_at', '>', now())
            ->first();
            
        if (!$invitation) {
            return ApiResponse::error('Lien d\'invitation invalide ou expiré.', 404);
        }
        
        $data = $request->validated();
        
        DB::beginTransaction();
        try {
            $assure = Assure::create([
                'user_id' => null,
                'entreprise_id' => $invitation->entreprise_id,
                'nom' => $data['nom'],
                'prenoms' => $data['prenoms'],
                'email' => $data['email'] ?? null,
                'date_naissance' => $data['date_naissance'],
                'sexe' => $data['sexe'],
                'contact' => $data['contact'] ?? null,
                'est_principal' => true,
                'profession' => $data['profession'] ?? null,
            ]);
            
            foreach ($data['reponses'] as $reponse) {
                $this->demandeAdhesionService->enregistrerReponsePersonne('employe', $assure->id, $reponse, null);
            }
            
            if (isset($data['beneficiaires']) && is_array($data['beneficiaires'])) {
                foreach ($data['beneficiaires'] as $beneficiaire) {
                    $benefAssure = Assure::create([
                        'user_id' => null,
                        'assure_principal_id' => $assure->id,
                        'nom' => $beneficiaire['nom'],
                        'prenoms' => $beneficiaire['prenoms'],
                        'date_naissance' => $beneficiaire['date_naissance'],
                        'sexe' => $beneficiaire['sexe'],
                        'lien_parente' => $beneficiaire['lien_parente'],
                        'est_principal' => false,
                    ]);

                    foreach ($beneficiaire['reponses'] as $reponse) {
                        $this->demandeAdhesionService->enregistrerReponsePersonne('beneficiaire', $benefAssure->id, $reponse, null);
                    }
                }
            }
            
            $entreprise = $invitation->entreprise;
            if ($entreprise && $entreprise->user) {
                $this->notificationService->createNotification(
                    $entreprise->user->id,
                    'Nouvelle fiche employé soumise',
                    "L'employé {$assure->nom} {$assure->prenoms} a soumis sa fiche d'adhésion.",
                    'info',
                    [
                        'employe_id' => $assure->id,
                        'employe_nom' => $assure->nom,
                        'employe_prenoms' => $assure->prenoms,
                        'date_soumission' => now()->format('d/m/Y à H:i'),
                        'type' => 'nouvelle_fiche_employe'
                    ]
                );
            }
            
            DB::commit();
            return ApiResponse::success(null, 'Fiche employé soumise avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la soumission de la fiche employé : ' . $e->getMessage(), 500);
        }
    }
} 