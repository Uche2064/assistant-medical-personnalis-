<?php

namespace App\Helpers;

use App\Enums\TypePrestataireEnum;

class PrestataireDocumentHelper
{
    /**
     * Obtenir la liste des documents requis selon le type de prestataire
     */
    public static function getDocumentsRequis(TypePrestataireEnum $type): array
    {
        return match($type) {
            TypePrestataireEnum::PHARMACIE => [
                'autorisation_ouverture' => 'Autorisation officielle d\'ouverture',
                'plan_situation_geographique' => 'Plan de situation géographique',
                'diplome_responsable' => 'Diplôme du responsable',
                'attestation_ordre' => 'Attestation d\'inscription à l\'ordre',
                'presentation_structure' => 'Présentation en images de la structure',
            ],
            TypePrestataireEnum::CENTRE_DE_SOINS => [
                'autorisation_ouverture' => 'Autorisation officielle d\'ouverture',
                'plan_situation_geographique' => 'Plan de situation géographique',
                'diplomes_responsables' => 'Diplôme des responsables des différents services',
                'grille_tarifaire' => 'Grille tarifaire actuelle',
                'presentation_structure' => 'Présentation en images de la structure',
                'carte_immatriculation_fiscale' => 'Carte d\'immatriculation fiscale',
            ],
            TypePrestataireEnum::OPTIQUE => [
                'autorisation_ouverture' => 'Autorisation officielle d\'ouverture',
                'plan_situation_geographique' => 'Plan de situation géographique',
                'diplomes_responsables' => 'Diplôme des responsables des différents services',
                'grille_tarifaire' => 'Grille tarifaire actuelle',
                'presentation_structure' => 'Présentation en images de la structure',
                'carte_immatriculation_fiscale' => 'Carte d\'immatriculation fiscale',
            ],
            TypePrestataireEnum::LABORATOIRE_CENTRE_DIAGNOSTIC => [
                'autorisation_ouverture' => 'Autorisation officielle d\'ouverture',
                'plan_situation_geographique' => 'Plan de situation géographique',
                'diplomes_responsables' => 'Diplôme des responsables des différents services',
                'grille_tarifaire' => 'Grille tarifaire actuelle',
                'presentation_structure' => 'Présentation en images de la structure',
                'carte_immatriculation_fiscale' => 'Carte d\'immatriculation fiscale',
            ],
            TypePrestataireEnum::MEDECIN_LIBERAL => [
                'autorisation_ouverture' => 'Autorisation officielle d\'ouverture',
                'plan_situation_geographique' => 'Plan de situation géographique',
                'diplome_responsable' => 'Diplôme du médecin',
                'attestation_ordre' => 'Attestation d\'inscription à l\'ordre des médecins',
                'presentation_structure' => 'Présentation en images de la structure',
            ],
        };
    }

    /**
     * Obtenir les documents obligatoires selon le type
     */
    public static function getDocumentsObligatoires(TypePrestataireEnum $type): array
    {
        $documents = self::getDocumentsRequis($type);
        return array_keys($documents);
    }

    /**
     * Valider si tous les documents requis sont présents
     */
    public static function validerDocuments(TypePrestataireEnum $type, array $documentsFournis): array
    {
        $documentsRequis = self::getDocumentsObligatoires($type);
        $documentsManquants = array_diff($documentsRequis, array_keys($documentsFournis));
        
        return [
            'valide' => empty($documentsManquants),
            'manquants' => $documentsManquants,
            'total_requis' => count($documentsRequis),
            'total_fournis' => count($documentsFournis),
        ];
    }

    /**
     * Obtenir l'email de contact pour les prestataires
     */
    public static function getEmailContact(): string
    {
        return 'regulation.medicale@sunu-sante.com';
    }

    /**
     * Obtenir le message d'instruction selon le type
     */
    public static function getMessageInstruction(TypePrestataireEnum $type): string
    {
        return match($type) {
            TypePrestataireEnum::PHARMACIE => 
                "Envoyez par mail à " . self::getEmailContact() . " les pièces suivantes :",
            TypePrestataireEnum::CENTRE_DE_SOINS => 
                "Envoyez par mail à " . self::getEmailContact() . " les pièces suivantes :",
            TypePrestataireEnum::OPTIQUE => 
                "Envoyez par mail à " . self::getEmailContact() . " les pièces suivantes :",
            TypePrestataireEnum::LABORATOIRE_CENTRE_DIAGNOSTIC => 
                "Envoyez par mail à " . self::getEmailContact() . " les pièces suivantes :",
            TypePrestataireEnum::MEDECIN_LIBERAL => 
                "Envoyez par mail à " . self::getEmailContact() . " les pièces suivantes :",
        };
    }
} 