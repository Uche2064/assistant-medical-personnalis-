<?php

namespace App\Services;

use App\Models\DemandeAdhesion;
use App\Models\Assure;
use App\Models\Contrat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

        // Récupérer les détails du contrat proposé
        $contratPropose = $this->getContratProposeDetails($demande);

        Log::info($assurePrincipal->reponsesQuestionnaire);

        return [
            'demandeur' => [
                'nom' => $assurePrincipal->nom,
                'prenoms' => $assurePrincipal->prenoms,
                'date_naissance' => $assurePrincipal->date_naissance,
                'sexe' => $assurePrincipal->sexe->value,
                'profession' => $assurePrincipal->profession,
                'contact' => $demande->user->contact,
                'email' => $demande->user->email,
                'photo' => $demande->user->photo,
                'adresse' => $demande->user->adresse,
            ],
            'contrat_propose' => $contratPropose,
            'reponses_questionnaire' => $this->formaterReponsesQuestionnaire($assurePrincipal->reponsesQuestionnaire),
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
                'raison_sociale' => $prestataire->raison_sociale ?? null,
                'email' => $demande->user->email,
                'contact' => $demande->user->contact,
                'adresse' => $prestataire->user->adresse,
            ],
            'reponses_questionnaire' => $this->formaterReponsesQuestionnaire($demande->reponsesQuestionnaire),
        ];
    }

    /**
     * Récupérer les données pour un demandeur entreprise
     */
    public function getEntrepriseData(DemandeAdhesion $demande): array
    {

        $entreprise = $demande->user->entreprise;
        $employesPrincipaux = Assure::where('entreprise_id', $entreprise->id)
            ->where('est_principal', true)
            ->with(['reponsesQuestionnaire.question', 'beneficiaires'])
            ->get();

        $beneficiaires = collect();
        foreach ($employesPrincipaux as $employe) {
            $beneficiaires = $beneficiaires->merge($employe->beneficiaires);
        }

        
        // Calculer les statistiques des employés
        $statsEmployes = $this->calculateEmployesStats($employesPrincipaux);

        // Formater les réponses des employés
        $employesAvecReponsesFormatees = $employesPrincipaux->map(function ($employe) {
            return [
                'id' => $employe->id,
                'nom' => $employe->nom,
                'prenoms' => $employe->prenoms,
                'email' => $employe->email,
                'date_naissance' => $employe->date_naissance,
                'sexe' => $employe->sexe,
                'profession' => $employe->profession,
                'contact' => $employe->contact,
                'adresse' => $employe->adresse,
                'photo' => $employe->photo,
                'reponses_questionnaire' => $this->formaterReponsesQuestionnaire($employe->reponsesQuestionnaire),
                'beneficiaires' => $employe->beneficiaires->map(function ($beneficiaire) {
                    return [
                        'id' => $beneficiaire->id,
                        'nom' => $beneficiaire->nom,
                        'prenoms' => $beneficiaire->prenoms,
                        'date_naissance' => $beneficiaire->date_naissance,
                        'sexe' => $beneficiaire->sexe,
                        'lien_parente' => $beneficiaire->lien_parente,
                        'photo' => $beneficiaire->photo,
                    ];
                })
            ];
        });

        // Récupérer les détails du contrat proposé
        $contratPropose = $this->getContratProposeDetails($demande);

        return [
            'demandeur' => [
                'raison_sociale' => $entreprise->raison_sociale,
                'email' => $demande->user->email,
                'contact' => $demande->user->contact,
                'adresse' => $demande->user->adresse,
            ],
            'contrat_propose' => $contratPropose,
            'employes' => $employesAvecReponsesFormatees,
            'statistiques' => [
                'nombre_employes' => $employesPrincipaux->count(),
                'repartition_employes_par_sexe' => $statsEmployes['par_sexe'],
                'nombre_total_personnes_couvrir' => $employesPrincipaux->count() + $beneficiaires->count(),
                'nombre_beneficiaires' => $beneficiaires->count(),
                'repartition_employes_par_age' => $statsEmployes['par_age'],
            ]
        ];
    }

    /**
     * Formater les réponses au questionnaire en ne gardant que les champs non-null
     * @param \Illuminate\Support\Collection $reponses
     * @return array
     */
    public function formaterReponsesQuestionnaire($reponses): array
    {
        Log::info($reponses);
        return $reponses->map(function ($reponse) {
            $formatted = [
                'question_id' => $reponse->question_id,
                'libelle' => $reponse->question->libelle ?? null,
                'type_question' => $reponse->question->type_donnee ?? null,
            ];
            // Ajouter seulement les champs de réponse qui ne sont pas null
            if ($reponse->reponse_text !== null) {
                $formatted['reponse_text'] = $reponse->reponse_text;
            }
            if ($reponse->reponse_bool !== null) {
                $formatted['reponse_bool'] = $reponse->reponse_bool;
            }
            
            if ($reponse->reponse_number !== null) {
                $formatted['reponse_number'] = $reponse->reponse_number;
            }
            
            if ($reponse->reponse_date !== null) {
                $formatted['reponse_date'] = $reponse->reponse_date;
            }
            
            if ($reponse->reponse_fichier !== null) {
                $formatted['reponse_fichier'] = $reponse->reponse_fichier;
            }
            
            if ($reponse->reponse_select !== null) {
                $formatted['reponse_select'] = $reponse->reponse_select;
            }
            if ($reponse->reponse_radio !== null) {
                $formatted['reponse_radio'] = $reponse->reponse_radio;
            }

            Log::info($formatted);


            return $formatted;
        })->toArray();
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
            'par_age' => $this->calculateAgeDistribution($employes),
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
                $age = \Carbon\Carbon::parse($personne->date_naissance)->diffInYears(now());
                
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

        Log::info($tranches);

        return $tranches;
    }

    /** 
     * Récupérer les détails du contrat proposé pour une demande d'adhésion
     */
    private function getContratProposeDetails(DemandeAdhesion $demande): ?array
    {
        // Récupérer la proposition de contrat la plus récente
        $propositionContrat = $demande->propositionsContrat()
            ->with([
                'contrat.categoriesGaranties.garanties',
                'contrat.technicien'
            ])
            ->latest()
            ->first();

        if (!$propositionContrat || !$propositionContrat->contrat) {
            return null;
        }

        $contrat = $propositionContrat->contrat;

        // Calculer la couverture moyenne à partir des catégories de garanties
        $couvertures = $contrat->categoriesGaranties->pluck('pivot.couverture')->filter();
        $couvertureMoyenne = $couvertures->count() > 0 ? $couvertures->avg() : null;
        
        return [
            'contrat' => [
                'id' => $contrat->id,
                'libelle' => $contrat->libelle,
                'prime_standard' => $contrat->prime_standard,
                'frais_gestion' => $contrat->frais_gestion,
                'couverture_moyenne' => $couvertureMoyenne,
                'couverture' => $couvertures->first(), // Première couverture trouvée
                'categories_garanties' => $contrat->categoriesGaranties->map(function ($categorieGarantie) {
                    return [
                        'id' => $categorieGarantie->id,
                        'libelle' => $categorieGarantie->libelle,
                        'description' => $categorieGarantie->description,
                        'couverture' => $categorieGarantie->pivot->couverture ?? null,
                        'garanties' => $categorieGarantie->garanties->map(function ($garantie) {
                            return [
                                'id' => $garantie->id,
                                'libelle' => $garantie->libelle,
                                'prix_standard' => $garantie->prix_standard,
                                'taux_couverture' => $garantie->taux_couverture,
                                'plafond' => $garantie->plafond,
                            ];
                        })
                    ];
                })
            ]
        ];
    }
} 