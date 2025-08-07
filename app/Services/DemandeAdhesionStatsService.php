<?php

namespace App\Services;

use App\Models\DemandeAdhesion;
use App\Models\Assure;
use Carbon\Carbon;

class DemandeAdhesionStatsService
{
    /**
     * Récupérer les données pour un demandeur physique
     */
    public function getPhysiqueData(DemandeAdhesion $demande): array
    {
        $assurePrincipal = $demande->user->assure;
        $beneficiaires = $assurePrincipal->beneficiaires ?? collect();

        // Calculer les statistiques des bénéficiaires
        $statsBeneficiaires = $this->calculateBeneficiairesStats($beneficiaires);

        return [
            'demandeur' => [
                'nom' => $assurePrincipal->nom,
                'prenoms' => $assurePrincipal->prenoms,
                'date_naissance' => $assurePrincipal->date_naissance,
                'sexe' => $assurePrincipal->sexe->value,
                'profession' => $assurePrincipal->profession,
                'contact' => $assurePrincipal->contact,
                'email' => $demande->user->email,
                'photo' => $demande->user->photo,
                'adresse' => $demande->user->adresse,
            ],
            'reponses_questionnaire' => $demande->reponsesQuestionnaire->map(function ($reponse) {
                return [
                    'question' => $reponse->question->libelle,
                    'reponse_text' => $reponse->reponse_text,
                    'reponse_bool' => $reponse->reponse_bool,
                    'reponse_number' => $reponse->reponse_number,
                    'reponse_date' => $reponse->reponse_date,
                    'reponse_fichier' => $reponse->reponse_fichier,
                ];
            }),
            'statistiques' => [
                'nombre_beneficiaires' => $beneficiaires->count(),
                'repartition_par_sexe' => $statsBeneficiaires['par_sexe'],
                'repartition_par_age' => $statsBeneficiaires['par_age'],
            ]
        ];
    }

    /**
     * Récupérer les données pour un demandeur prestataire
     */
    public function getPrestataireData(DemandeAdhesion $demande): array
    {
        $prestataire = $demande->user->prestataire;

        return [
            'demandeur' => [
                'raison_sociale' => $prestataire->raison_sociale,
                'email' => $demande->user->email,
                'contact' => $demande->user->contact,
                'adresse' => $prestataire->adresse,
            ],
            'reponses_questionnaire' => $demande->reponsesQuestionnaire->map(function ($reponse) {
                return [
                    'question' => $reponse->question->libelle,
                    'reponse_text' => $reponse->reponse_text,
                    'reponse_bool' => $reponse->reponse_bool,
                    'reponse_number' => $reponse->reponse_number,
                    'reponse_date' => $reponse->reponse_date,
                    'reponse_fichier' => $reponse->reponse_fichier,
                    'reponse_select' => $reponse->reponse_select,
                ];
            }),
        ];
    }

    /**
     * Récupérer les données pour un demandeur entreprise
     */
    public function getEntrepriseData(DemandeAdhesion $demande): array
    {
        $entreprise = $demande->user->entreprise;
        $employes = $demande->assures->where('est_principal', true);
        $beneficiaires = $demande->assures->where('est_principal', false);

        // Calculer les statistiques des employés
        $statsEmployes = $this->calculateEmployesStats($employes);

        return [
            'demandeur' => [
                'raison_sociale' => $entreprise->raison_sociale,
                'email' => $demande->user->email,
                'contact' => $demande->user->contact,
            ],
            'statistiques' => [
                'nombre_employes' => $employes->count(),
                'repartition_employes_par_sexe' => $statsEmployes['par_sexe'],
                'nombre_total_personnes_couvrir' => $employes->count() + $beneficiaires->count(),
            ]
        ];
    }

    /**
     * Calculer les statistiques des bénéficiaires
     */
    private function calculateBeneficiairesStats($beneficiaires): array
    {
        $parSexe = $beneficiaires->groupBy('sexe')->map->count();
        $parAge = $this->calculateAgeDistribution($beneficiaires);

        return [
            'par_sexe' => [
                'M' => $parSexe->get('M', 0),
                'F' => $parSexe->get('F', 0),
            ],
            'par_age' => $parAge,
        ];
    }

    /**
     * Calculer les statistiques des employés
     */
    private function calculateEmployesStats($employes): array
    {
        $parSexe = $employes->groupBy('sexe')->map->count();

        return [
            'par_sexe' => [
                'M' => $parSexe->get('M', 0),
                'F' => $parSexe->get('F', 0),
            ],
        ];
    }

    /**
     * Calculer la répartition par âge
     */
    private function calculateAgeDistribution($personnes): array
    {
        $tranches = [
            '0-18' => 0,
            '19-30' => 0,
            '31-50' => 0,
            '51-65' => 0,
            '65+' => 0,
        ];

        foreach ($personnes as $personne) {
            if ($personne->date_naissance) {
                $age = now()->diffInYears($personne->date_naissance);
                
                if ($age <= 18) {
                    $tranches['0-18']++;
                } elseif ($age <= 30) {
                    $tranches['19-30']++;
                } elseif ($age <= 50) {
                    $tranches['31-50']++;
                } elseif ($age <= 65) {
                    $tranches['51-65']++;
                } else {
                    $tranches['65+']++;
                }
            }
        }

        return $tranches;
    }
} 