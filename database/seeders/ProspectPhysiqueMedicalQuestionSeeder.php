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
                'libelle' => 'Souffrez vous du diabète',
                'type_donnee' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Mangez vous le porc ?',
                'type_donnee' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Quel sport pratiquez-vous?',
                'type_donnee' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Consommation du tabac',
                'type_donnee' => TypeDonneeEnum::RADIO,
                'destinataire' => TypeDemandeurEnum::PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => ['pas du tout', 'un peu', 'modérément', 'beaucoup'],
            ],
            [
                'libelle' => 'Consommation d\'alcool',
                'type_donnee' => TypeDonneeEnum::RADIO,
                'destinataire' => TypeDemandeurEnum::PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => ['pas du tout', 'un peu', 'modérément', 'beaucoup'],
            ],
            [
                'libelle' => 'Nom et Adresse de votre médecin traitant',
                'type_donnee' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
        ];

        foreach ($questions as $question) {
            Question::create($question);
        }
    }
}