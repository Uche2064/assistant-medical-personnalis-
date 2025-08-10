<?php

namespace App\Helpers;

use App\Enums\TypeDemandeurEnum;

class CommonHelpers
{
    public static function getTemplateByDemandeurType($typeDemandeur)
    {
        return match($typeDemandeur) {
            TypeDemandeurEnum::PHYSIQUE->value => 'pdf.demande-adhesion-physique',
            TypeDemandeurEnum::ENTREPRISE->value => 'pdf.demande-adhesion-entreprise',
            default => 'pdf.demande-adhesion-prestataire', // Pour tous les types de prestataires
        };
    }

    
    /**
     * Calculer les statistiques pour une demande d'adhésion
     */
    public static function calculerStatistiquesDemande($demande)
    {
        $toutesLesPersonnes = collect();
        
        // Ajouter l'assuré principal s'il existe
        if ($demande->user && $demande->user->assure) {
            $toutesLesPersonnes->push($demande->user->assure);
        }
        
        // Ajouter les employés (vérifier si la relation existe)
        if ($demande->employes) {
            $toutesLesPersonnes = $toutesLesPersonnes->merge($demande->employes);
        }
        
        // Ajouter les bénéficiaires (vérifier si la relation existe)
        if ($demande->beneficiaires) {
            $toutesLesPersonnes = $toutesLesPersonnes->merge($demande->beneficiaires);
        }

        // Statistiques par âge
        $repartitionAge = [
            '18-25' => $toutesLesPersonnes->filter(function ($personne) {
                $age = $personne->date_naissance ? now()->diffInYears($personne->date_naissance) : 0;
                return $age >= 18 && $age <= 25;
            })->count(),
            '26-35' => $toutesLesPersonnes->filter(function ($personne) {
                $age = $personne->date_naissance ? now()->diffInYears($personne->date_naissance) : 0;
                return $age >= 26 && $age <= 35;
            })->count(),
            '36-45' => $toutesLesPersonnes->filter(function ($personne) {
                $age = $personne->date_naissance ? now()->diffInYears($personne->date_naissance) : 0;
                return $age >= 36 && $age <= 45;
            })->count(),
            '46-55' => $toutesLesPersonnes->filter(function ($personne) {
                $age = $personne->date_naissance ? now()->diffInYears($personne->date_naissance) : 0;
                return $age >= 46 && $age <= 55;
            })->count(),
            '55+' => $toutesLesPersonnes->filter(function ($personne) {
                $age = $personne->date_naissance ? now()->diffInYears($personne->date_naissance) : 0;
                return $age > 55;
            })->count(),
        ];

        // Statistiques par sexe
        $repartitionSexe = [
            'hommes' => $toutesLesPersonnes->where('sexe', 'M')->count(),
            'femmes' => $toutesLesPersonnes->where('sexe', 'F')->count(),
        ];

        return [
            'total_personnes' => $toutesLesPersonnes->count(),
            'total_employes' => $demande->employes ? $demande->employes->count() : 0,
            'total_beneficiaires' => $demande->beneficiaires ? $demande->beneficiaires->count() : 0,
            'repartition_age' => $repartitionAge,
            'repartition_sexe' => $repartitionSexe,
            'assure_principal' => $demande->user && $demande->user->assure ? [
                'nom' => $demande->user->assure->nom,
                'prenoms' => $demande->user->assure->prenoms,
                'photo_url' => $demande->user->assure->photo_url,
            ] : null,
        ];
    }
}   