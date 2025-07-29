<?php

namespace App\Services;

class PrestataireDocumentService
{
    /**
     * Obtenir la liste des documents requis selon le type de prestataire
     */
    public static function getDocumentsRequis(string $typePrestataire): array
    {
        $documentsCommuns = [
            'autorisation_ouverture' => [
                'nom' => 'Autorisation officielle d\'ouverture',
                'obligatoire' => true,
                'description' => 'Document officiel autorisant l\'ouverture de l\'établissement'
            ],
            'plan_situation_geographique' => [
                'nom' => 'Plan de situation géographique',
                'obligatoire' => true,
                'description' => 'Plan indiquant l\'emplacement géographique de l\'établissement'
            ],
            'presentation_structure' => [
                'nom' => 'Présentation en images de la structure',
                'obligatoire' => true,
                'description' => 'Photos ou images de l\'établissement et de ses équipements'
            ]
        ];

        switch ($typePrestataire) {
            case 'pharmacie':
                return array_merge($documentsCommuns, [
                    'diplome_responsable' => [
                        'nom' => 'Diplôme du responsable',
                        'obligatoire' => true,
                        'description' => 'Diplôme du pharmacien responsable'
                    ],
                    'attestation_ordre' => [
                        'nom' => 'Attestation d\'inscription à l\'ordre',
                        'obligatoire' => true,
                        'description' => 'Attestation d\'inscription à l\'ordre des pharmaciens'
                    ]
                ]);

            case 'centre_de_soins':
            case 'laboratoire_centre_diagnostic':
            case 'optique':
                return array_merge($documentsCommuns, [
                    'diplomes_responsables' => [
                        'nom' => 'Diplômes des responsables des différents services',
                        'obligatoire' => true,
                        'description' => 'Diplômes des médecins et professionnels responsables'
                    ],
                    'grille_tarifaire' => [
                        'nom' => 'Grille tarifaire actuelle',
                        'obligatoire' => true,
                        'description' => 'Liste des prix et tarifs des services proposés'
                    ],
                    'carte_immatriculation_fiscale' => [
                        'nom' => 'Carte d\'immatriculation fiscale',
                        'obligatoire' => true,
                        'description' => 'Document fiscal de l\'établissement'
                    ]
                ]);

            default:
                return $documentsCommuns;
        }
    }

    /**
     * Valider les documents fournis selon le type de prestataire
     */
    public static function validerDocuments(array $documentsFournis, string $typePrestataire): array
    {
        $documentsRequis = self::getDocumentsRequis($typePrestataire);
        $erreurs = [];
        $documentsManquants = [];

        foreach ($documentsRequis as $cle => $document) {
            if ($document['obligatoire'] && !isset($documentsFournis[$cle])) {
                $documentsManquants[] = $document['nom'];
                $erreurs[] = "Le document '{$document['nom']}' est obligatoire.";
            }
        }

        return [
            'valide' => empty($erreurs),
            'erreurs' => $erreurs,
            'documents_manquants' => $documentsManquants,
            'documents_fournis' => array_keys($documentsFournis),
            'total_requis' => count($documentsRequis)
        ];
    }

    /**
     * Obtenir le résumé des documents pour un prestataire
     */
    public static function getResumeDocuments(array $documentsFournis, string $typePrestataire): array
    {
        $documentsRequis = self::getDocumentsRequis($typePrestataire);
        $resume = [];

        foreach ($documentsRequis as $cle => $document) {
            $resume[$cle] = [
                'nom' => $document['nom'],
                'obligatoire' => $document['obligatoire'],
                'description' => $document['description'],
                'fourni' => isset($documentsFournis[$cle]),
                'url' => $documentsFournis[$cle] ?? null
            ];
        }

        return $resume;
    }

    /**
     * Obtenir les types de prestataires supportés
     */
    public static function getTypesPrestataires(): array
    {
        return [
            'pharmacie' => [
                'nom' => 'Pharmacie',
                'description' => 'Établissement pharmaceutique'
            ],
            'centre_de_soins' => [
                'nom' => 'Centre de Soins',
                'description' => 'Centre médical ou clinique'
            ],
            'laboratoire_centre_diagnostic' => [
                'nom' => 'Laboratoire et Centre de Diagnostic',
                'description' => 'Laboratoire d\'analyses médicales'
            ],
            'optique' => [
                'nom' => 'Centre d\'Optique',
                'description' => 'Centre d\'optique et lunetterie'
            ]
        ];
    }
} 