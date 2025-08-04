<?php

namespace App\Traits;

use App\Models\DemandeAdhesion;

trait DemandeAdhesionDataTrait
{
    /**
     * Préparer les données enrichies d'une demande d'adhésion
     * 
     * Note importante sur les types de demandeurs :
     * - ENTREPRISE : Le demandeur (entreprise) ne répond pas à de questionnaire.
     *   Il soumet juste la liste de ses employés avec leurs questionnaires respectifs.
     * - PHYSIQUE : Le demandeur (personne physique) répond à un questionnaire + peut avoir des bénéficiaires.
     * - PRESTATAIRE : Le demandeur (prestataire) répond à un questionnaire spécifique.
     */
    protected function prepareDemandeData(DemandeAdhesion $demande)
    {
        return [
            'demande' => [
                'id' => $demande->id,
                'type_demandeur' => $demande->type_demandeur,
                'statut' => $demande->statut,
                'created_at' => $demande->created_at,
                'updated_at' => $demande->updated_at,
                'valide_par' => $demande->validePar,
                'valider_a' => $demande->valider_a,
                'motif_rejet' => $demande->motif_rejet,
            ],
            'demandeur' => [
                'user' => $demande->user,
                'entreprise' => $demande->entreprise,
                'client' => $demande->client,
                // Réponses questionnaire selon le type de demandeur
                'reponses_questionnaire' => $this->getDemandeurReponses($demande),
            ],
            'personnes_associees' => [
                'employes' => $demande->employes->map(function ($employe) {
                    return [
                        'id' => $employe->id,
                        'nom' => $employe->nom,
                        'prenoms' => $employe->prenoms,
                        'date_naissance' => $employe->date_naissance,
                        'sexe' => $employe->sexe,
                        'contact' => $employe->contact,
                        'email' => $employe->email,
                        'profession' => $employe->profession,
                        'lien_parente' => $employe->lien_parente,
                        'statut' => $employe->statut,
                        'reponses_questionnaire' => $employe->reponsesQuestionnaire,
                    ];
                }),
                'beneficiaires' => $demande->beneficiaires->map(function ($beneficiaire) {
                    return [
                        'id' => $beneficiaire->id,
                        'nom' => $beneficiaire->nom,
                        'prenoms' => $beneficiaire->prenoms,
                        'date_naissance' => $beneficiaire->date_naissance,
                        'sexe' => $beneficiaire->sexe,
                        'contact' => $beneficiaire->contact,
                        'email' => $beneficiaire->email,
                        'lien_parente' => $beneficiaire->lien_parente,
                        'statut' => $beneficiaire->statut,
                        'reponses_questionnaire' => $beneficiaire->reponsesQuestionnaire,
                    ];
                }),
            ],
            'statistiques' => [
                'total_employes' => $demande->employes->count(),
                'total_beneficiaires' => $demande->beneficiaires->count(),
                'total_personnes' => $demande->employes->count() + $demande->beneficiaires->count(),
                'employes_avec_reponses' => $demande->employes->filter(function ($employe) {
                    return $employe->reponsesQuestionnaire->count() > 0;
                })->count(),
                'beneficiaires_avec_reponses' => $demande->beneficiaires->filter(function ($beneficiaire) {
                    return $beneficiaire->reponsesQuestionnaire->count() > 0;
                })->count(),
            ]
        ];
    }

    /**
     * Charger une demande d'adhésion avec toutes ses relations
     */
    protected function loadDemandeWithRelations($id)
    {
        return DemandeAdhesion::with([
            'user',
            'user.entreprise',
            'user.client',
            'validePar',
            'reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('est_vue', true);
                $query->where('demande_adhesion_id', $id);
            },
            'reponsesQuestionnaire.question',
            'assures.reponsesQuestionnaire.question',
            'employes.reponsesQuestionnaire.question',
            'beneficiaires.reponsesQuestionnaire.question'
        ])->find($id);
    }

    //  charger demande d'adhésion avec les relations et les réponses questionnaire pour un prestataire
    protected function loadDemandeWithRelationsForPrestataire($id)
    {
        return DemandeAdhesion::with([
            'validePar',
            'reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('est_vue', true);
                $query->where('demande_adhesion_id', $id);
            },
            'reponsesQuestionnaire.question',
            'user.prestataire',
            'user.entreprise',
        ])->find($id);
    }
    /**
     * Obtenir les réponses du demandeur selon son type
     */
    protected function getDemandeurReponses(DemandeAdhesion $demande)
    {
        // Pour une entreprise, pas de réponses questionnaire du demandeur principal
        // L'entreprise soumet juste la liste de ses employés
        if ($demande->type_demandeur === 'entreprise') {
            return [];
        }

        // Pour les autres types (physique, prestataire), retourner les réponses du demandeur
        return $demande->reponsesQuestionnaire;
    }
}
