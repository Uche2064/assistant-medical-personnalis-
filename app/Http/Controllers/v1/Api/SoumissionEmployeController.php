<?php

namespace App\Http\Controllers\v1\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\demande_adhesion\SoumissionEmployeFormRequest;
use App\Models\BeneficiaireTemp;
use App\Models\EmployesTemp;
use App\Models\InvitationEmploye;
use App\Models\Personnes;
use App\Models\Question;
use App\Models\ReponsesQuestionnaire;
use App\Services\DemandeValidatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SoumissionEmployeController extends Controller
{
    protected DemandeValidatorService $demandeValidatorService;

    public function __construct(DemandeValidatorService $demandeValidatorService)
    {
        $this->demandeValidatorService = $demandeValidatorService;
    }
   public function showForm($token)
    {
        $invitation = InvitationEmploye::where('token', $token)
            ->where('expire_at', '>', now())
            ->first();

        if (!$invitation) {
            return ApiResponse::error('Invitation invalide ou expirée.', 400);
        }

        // Tu peux personnaliser ici selon les questions médicales dynamiques
        return ApiResponse::success([
            'prospect_id' => $invitation->prospect_id,
            'question' => Question::where('destinataire', 'physique')->where('est_actif', true)->get(),
        ],
            'Formulaire d\'invitation trouvé.');
    }



    public function store(SoumissionEmployeFormRequest $request, $token)
    {
        $invitation = $this->validateToken($token);
        $data = $request->validated();
        if ($this->demandeValidatorService->hasValidatedDemande($data)) {
                return ApiResponse::error("Vous avez déjà une demande acceptée.", 422);
            }

            if ($this->demandeValidatorService->hasPendingDemande($data)) {
                return ApiResponse::error("Vous avez déjà une demande en attente.", 422);
            }

        if ($invitation instanceof JsonResponse) {
            return $invitation;
        }

      
        $prospect_id = $invitation->prospect_id;

        $employe = Personnes::create([
            'prospect_id' => $prospect_id,
            'nom' => $data['nom'],
            'prenoms' => $data['prenoms'] ?? null,
            'date_naissance' => $data['date_naissance'] ?? null,
            'sexe' => $data['sexe'],
            'type_personne' => $data['type_personne'],
            'type_personne' => $data['type_personne'],
        ]);

        foreach ($data['reponses'] as $r) {
            ReponsesQuestionnaire::create([
                'question_id' => $r['question_id'],
                'personne_type' => Personanes::class,
                'personne_id' => $employe->id,
                'reponses_text' => $r['reponses_text'] ?? null,
                'reponse_bool' => $r['reponse_bool'] ?? null,
                'reponse_decimal' => $r['reponse_decimal'] ?? null,
                'reponse_date' => $r['reponse_date'] ?? null,
            ]);
        }

        if ($data->filled('beneficiaires')) {
            foreach ($data['beneficiaires'] as $b) {
                $bene = Personnes::create([
                    'employe_temp_id' => $employe->id,
                    'nom' => $b['nom'],
                    'prenoms' => $b['prenoms'],
                    'date_naissance' => $b['date_naissance'],
                    'sexe' => $b['sexe'],
                    'lien_parente' => $b['lien_parente'],
                ]);

                foreach ($b['reponses'] as $r) {
                    ReponsesQuestionnaire::create([
                        'question_id' => $r['question_id'],
                        'personne_type' => Personnes::class,
                        'personne_id' => $bene->id,
                        'reponses_text' => $r['reponses_text'] ?? null,
                        'reponse_bool' => $r['reponse_bool'] ?? null,
                        'reponse_decimal' => $r['reponse_decimal'] ?? null,
                        'reponse_date' => $r['reponse_date'] ?? null,
                    ]);
                }
            }
        }

        return ApiResponse::success(
            ['employe_id' => $employe->id],
            'Employé et ses bénéficiaires enregistrés avec succès.'
        );
    }

    private function validateToken($token): InvitationEmployes | JsonResponse
    {
        $invitation = InvitationEmployes::where('token', $token)
            ->where('expire_at', '>', now())
            ->first();

        if (!$invitation) {
            return ApiResponse::error('Invitation invalide ou expirée.', 400);
        }
        return $invitation;
    }
}
