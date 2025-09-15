<?php

namespace Database\Seeders;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Enums\RoleEnum;
use App\Models\Question;
use Illuminate\Database\Seeder;
use App\Models\Personnel;

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
                'type_de_donnee' => TypeDonneeEnum::FILE,
                'destinataire' => TypeDemandeurEnum::PHARMACIE,
                'est_obligatoire' => true,
                'est_active' => true,
            ],
            // [
            //     'libelle' => 'Plan de situation géographique',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::PHARMACIE,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Diplôme du responsable',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::PHARMACIE,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Attestation d\'inscription à l\'ordre',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::PHARMACIE,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Présentation en images de la structure',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::PHARMACIE,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
        ];

        // Questions pour les centres de soins (selon document officiel)
        $questionsCentreSoins = [
            [
                'libelle' => 'Autorisation officielle d\'ouverture',
                'type_de_donnee' => TypeDonneeEnum::FILE,
                'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
                'est_obligatoire' => true,
                'est_active' => true,
            ],
            [
                'libelle' => 'Plan de situation géographique',
                'type_de_donnee' => TypeDonneeEnum::FILE,
                'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
                'est_obligatoire' => true,
                'est_active' => true,
            ],
            // [
            //     'libelle' => 'Diplôme des responsables des différents services',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Grille tarifaire actuelle',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Présentation en images de la structure',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Carte d\'immatriculation fiscale',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::CENTRE_DE_SOINS,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
        ];

        // Questions pour les centres d'optique (selon document officiel)
        $questionsOptique = [
            [
                'libelle' => 'Autorisation officielle d\'ouverture',
                'type_de_donnee' => TypeDonneeEnum::FILE,
                'destinataire' => TypeDemandeurEnum::OPTIQUE,
                'est_obligatoire' => true,
                'est_active' => true,
            ],
            [
                'libelle' => 'Plan de situation géographique',
                'type_de_donnee' => TypeDonneeEnum::FILE,
                'destinataire' => TypeDemandeurEnum::OPTIQUE,
                'est_obligatoire' => true,
                'est_active' => true,
            ],
            // [
            //     'libelle' => 'Diplôme des responsables des différents services',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::OPTIQUE,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Grille tarifaire actuelle',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::OPTIQUE,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Présentation en images de la structure',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::OPTIQUE,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Carte d\'immatriculation fiscale',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::OPTIQUE,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
        ];

        // Questions pour les laboratoires (selon document officiel)
        $questionsLaboratoire = [
            [
                'libelle' => 'Autorisation officielle d\'ouverture',
                'type_de_donnee' => TypeDonneeEnum::FILE,
                'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
                'est_obligatoire' => true,
                'est_active' => true,
            ],
            // [
            //     'libelle' => 'Plan de situation géographique',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Diplôme des responsables des différents services',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Grille tarifaire actuelle',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Présentation en images de la structure',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Carte d\'immatriculation fiscale',
            //     'type_de_donnee' => TypeDonneeEnum::FILE,
            //     'destinataire' => TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
        ];
        

        // Fusionner toutes les questions et les créer
        $allQuestions = array_merge(
            $questionsCentreSoins, 
            $questionsPharmacie, 
            $questionsLaboratoire, 
            $questionsOptique
        );

        // Only medecin controleur can create questions
        $medecin = Personnel::whereHas('user', function($q) {
            $q->whereHas('roles', function($qr) {
                $qr->where('name', RoleEnum::MEDECIN_CONTROLEUR->value);
            });
        })->first();

        if (!$medecin) {
            throw new \RuntimeException('Aucun médecin contrôleur trouvé pour créer les questions des prestataires.');
        }

        foreach ($allQuestions as $question) {
            $question['cree_par_id'] = $medecin->id;
            Question::create($question);
        }
    }
}
