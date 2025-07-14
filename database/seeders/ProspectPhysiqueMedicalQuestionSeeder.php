<?php

namespace Database\Seeders;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Models\Question;
use Illuminate\Database\Seeder;

class ProspectPhysiqueMedicalQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Questions du questionnaire médical SUNU pour les prospects physiques
        $questions = [
            [
                'libelle' => 'Présentez-vous ou avez-vous présenté un des symptômes suivants: Éruption cutanée, présence de ganglions anormaux, diarrhée chronique, fièvre prolongée?',
                'type_donnee' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => ['precision' => 'Lesquels'],
            ],
            [
                'libelle' => 'Présentez-vous ou avez-vous présenté une des maladies suivantes: Méningite, hépatite B, verrues fréquentes, mycoses, affections génitales?',
                'type_donnee' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => ['precision' => 'Lesquelles'],
            ],
            [
                'libelle' => 'Quel sport pratiquez-vous?',
                'type_donnee' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Consommation du tabac',
                'type_donnee' => TypeDonneeEnum::RADIO,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => ['pas du tout', 'un peu', 'modérément', 'beaucoup'],
            ],
            [
                'libelle' => 'Consommation d\'alcool',
                'type_donnee' => TypeDonneeEnum::RADIO,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => ['pas du tout', 'un peu', 'modérément', 'beaucoup'],
            ],
            [
                'libelle' => 'Nom et Adresse de votre médecin traitant',
                'type_donnee' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
        ];

        foreach ($questions as $question) {
            Question::create($question);
        }
    }
}