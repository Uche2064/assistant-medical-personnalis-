<?php

namespace App\Http\Controllers\v1\Api\statistiques;

use App\Enums\TypeDemandeurEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Assure;
use App\Models\DemandeAdhesion;
use App\Services\DemandeAdhesionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StatistiquesController extends Controller
{
    protected DemandeAdhesionService $demandeAdhesionService;

    public function __construct(DemandeAdhesionService $demandeAdhesionService)
    {
        $this->demandeAdhesionService = $demandeAdhesionService;
    }

    /**
     * Statistiques des demandes d'adhésion
     */
    public function stats()
    {
        return $this->demandeAdhesionService->getStats(Auth::user());
    }

    /**
     * Récupérer la liste des employés qui ont soumis leur demande d'adhésion
     * Pour le dashboard de l'entreprise
     */
    public function employesAvecDemandes(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise')) {
            return ApiResponse::error('Seules les entreprises peuvent consulter les demandes de leurs employés.', 403);
        }   

        $entreprise = $user->entreprise;

        // Récupérer tous les employés de l'entreprise avec leurs demandes d'adhésion
        $employes = Assure::where('entreprise_id', $entreprise->id)
            ->get();

        return ApiResponse::success($employes, 'Employés avec demandes d\'adhésion récupérés avec succès');
    }

    /**
     * Statistiques des employés et demandes d'adhésion pour le dashboard de l'entreprise
     */
    public function statistiquesEmployes(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise')) {
            return ApiResponse::error('Seules les entreprises peuvent consulter les statistiques de leurs employés.', 403);
        }

        $entreprise = $user->entreprise;

        // Récupérer tous les employés principaux de l'entreprise qui ont répondu au questionnaire
        $employesPrincipaux = Assure::where('entreprise_id', $entreprise->id)
            ->whereNull('assure_principal_id') // Assurés principaux uniquement
            ->get();

        $employesPrincipauxIds = $employesPrincipaux->pluck('id');

        // Récupérer tous les bénéficiaires des employés de cette entreprise
        $beneficiairesEmployes = Assure::whereIn('assure_principal_id', $employesPrincipauxIds)
            ->get();

        // Statistiques générales
        $totalEmployesPrincipaux = $employesPrincipaux->count();
        $totalEmployesEtBeneficiaires = $totalEmployesPrincipaux + $beneficiairesEmployes->count();

        // Statistiques par sexe - Employés principaux
        $employesPrincipauxHommes = $employesPrincipaux->where('sexe', 'M')->count();
        $employesPrincipauxFemmes = $employesPrincipaux->where('sexe', 'F')->count();

        // Statistiques par sexe - Bénéficiaires des employés
        $beneficiairesHommes = $beneficiairesEmployes->where('sexe', 'M')->count();
        $beneficiairesFemmes = $beneficiairesEmployes->where('sexe', 'F')->count();

        // Statistiques par sexe - Total des bénéficiaires (employés + leurs bénéficiaires)
        $totalBeneficiairesHommes = $employesPrincipauxHommes + $beneficiairesHommes;
        $totalBeneficiairesFemmes = $employesPrincipauxFemmes + $beneficiairesFemmes;

        // Répartition par âge - Employés principaux ET leurs bénéficiaires
        $repartitionAgeEmployesPrincipauxEtBeneficiaire = [
            '0-17' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 0 && $age <= 17;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 0 && $age <= 17;
            })->count(),
            '18-25' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 18 && $age <= 25;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 18 && $age <= 25;
            })->count(),
            '26-35' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 26 && $age <= 35;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 26 && $age <= 35;
            })->count(),
            '36-45' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 36 && $age <= 45;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 36 && $age <= 45;
            })->count(),
            '46-55' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 46 && $age <= 55;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 46 && $age <= 55;
            })->count(),
            '55+' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age > 55;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age > 55;
            })->count(),
        ];

        // Répartition par âge - Total des bénéficiaires (employés + leurs bénéficiaires)
        $repartitionAgeTotalBeneficiaires = [
            '0-17' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['0-17'],
            '18-25' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['18-25'],
            '26-35' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['26-35'],
            '36-45' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['36-45'],
            '46-55' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['46-55'],
            '55+' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['55+'],
        ];

        // Statistiques des réponses au questionnaire
        $employesAvecReponses = $employesPrincipaux->filter(function ($employe) {
            return $employe->reponsesQuestionnaire->count() > 0;
        })->count();

        $totalReponses = $employesPrincipaux->sum(function ($employe) {
            return $employe->reponsesQuestionnaire->count();
        });

        $statistiques = [
            'general' => [
                'total_employes_principaux' => $totalEmployesPrincipaux,
                'total_employes_et_beneficiaires' => $totalEmployesEtBeneficiaires, // Employés + leurs bénéficiaires
            ],
            'repartition' => [
                'par_sexe' => [
                    'total_beneficiaires' => [ // Employés + leurs bénéficiaires
                        'hommes' => $totalBeneficiairesHommes,
                        'femmes' => $totalBeneficiairesFemmes,
                    ],
                ],
                'par_age' => [
                    'total_beneficiaires' => $repartitionAgeTotalBeneficiaires,
                ],
            ],
        ];

        return ApiResponse::success($statistiques, 'Statistiques des employés récupérées avec succès.');
    }

    /**
     * Statistiques globales pour les administrateurs
     */
    public function statistiquesGlobales()
    {
        try {
            // Vérifier que l'utilisateur est un administrateur
            if (!Auth::user()->hasRole('admin')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            // Statistiques des demandes d'adhésion
            $totalDemandes = DemandeAdhesion::count();
            $demandesEnAttente = DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count();
            $demandesValidees = DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::VALIDEE->value)->count();
            $demandesRejetees = DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::REJETEE->value)->count();

            // Répartition par type de demandeur
            $repartitionParType = [
                'physique' => DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PHYSIQUE->value)->count(),
                'entreprise' => DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::ENTREPRISE->value)->count(),
                'prestataires' => DemandeAdhesion::whereNotIn('type_demandeur', [
                    TypeDemandeurEnum::PHYSIQUE->value, 
                    TypeDemandeurEnum::ENTREPRISE->value
                ])->count(),
            ];

            // Statistiques des employés
            $totalEmployes = Assure::where('entreprise_id', '!=', null)->count();
            $employesAvecReponses = Assure::where('entreprise_id', '!=', null)
                ->whereHas('reponsesQuestionnaire')
                ->count();

            $statistiques = [
                'demandes_adhesion' => [
                    'total' => $totalDemandes,
                    'en_attente' => $demandesEnAttente,
                    'validees' => $demandesValidees,
                    'rejetees' => $demandesRejetees,
                    'taux_validation' => $totalDemandes > 0 ? round(($demandesValidees / $totalDemandes) * 100, 2) : 0,
                    'repartition_par_type' => $repartitionParType,
                ],
                'employes' => [
                    'total' => $totalEmployes,
                    'avec_reponses' => $employesAvecReponses,
                    'sans_reponses' => $totalEmployes - $employesAvecReponses,
                    'taux_completion' => $totalEmployes > 0 ? round(($employesAvecReponses / $totalEmployes) * 100, 2) : 0,
                ],
            ];

            return ApiResponse::success($statistiques, 'Statistiques globales récupérées avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques globales', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }
} 