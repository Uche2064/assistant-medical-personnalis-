<?php

namespace Database\Seeders;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Models\Question;
use Illuminate\Database\Seeder;

class PrestataireMedicalQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Questions pour les pharmacies (selon document officiel)
        $questionsPharmacie = [
            [
                'libelle' => 'Autorisation officielle d\'ouverture',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PHARMACIE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Plan de situation géographique',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PHARMACIE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Diplôme du responsable',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PHARMACIE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Attestation d\'inscription à l\'ordre',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PHARMACIE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Présentation en images de la structure',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PHARMACIE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
        ];

        // Questions pour les centres de soins (selon document officiel)
        $questionsCentreSoins = [
            [
                'libelle' => 'Autorisation officielle d\'ouverture',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Plan de situation géographique',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Diplôme des responsables des différents services',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Grille tarifaire actuelle',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Présentation en images de la structure',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Carte d\'immatriculation fiscale',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
                'obligatoire' => true,
                'est_actif' => true,
            ],
        ];

        // Questions pour les centres d'optique (selon document officiel)
        $questionsOptique = [
            [
                'libelle' => 'Autorisation officielle d\'ouverture',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::OPTIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Plan de situation géographique',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::OPTIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Diplôme des responsables des différents services',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::OPTIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Grille tarifaire actuelle',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::OPTIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Présentation en images de la structure',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::OPTIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Carte d\'immatriculation fiscale',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::OPTIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
        ];

        // Questions pour les laboratoires (selon document officiel)
        $questionsLaboratoire = [
            [
                'libelle' => 'Autorisation officielle d\'ouverture',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Plan de situation géographique',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Diplôme des responsables des différents services',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Grille tarifaire actuelle',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Présentation en images de la structure',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Carte d\'immatriculation fiscale',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
                'obligatoire' => true,
                'est_actif' => true,
            ],
        ];
        
        // Questions pour les médecins libéraux (adaptation similaire)
        $questionsMedecinLiberal = [
            [
                'libelle' => 'Autorisation officielle d\'exercice',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::MEDECIN_LIBERAL,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Diplôme',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::MEDECIN_LIBERAL,
                'obligatoire' => true,
                'est_actif' => true,
            ],
        ];

        // Fusionner toutes les questions et les créer
        $allQuestions = array_merge(
            $questionsCentreSoins, 
            $questionsMedecinLiberal, 
            $questionsPharmacie, 
            $questionsLaboratoire, 
            $questionsOptique
        );

        foreach ($allQuestions as $question) {
            Question::create($question);
        }
    }
}
