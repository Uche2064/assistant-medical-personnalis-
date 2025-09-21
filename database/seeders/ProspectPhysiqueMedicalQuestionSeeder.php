<?php

namespace Database\Seeders;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Enums\RoleEnum;
use App\Models\Question;
use App\Models\Personnel;
use Illuminate\Database\Seeder;

class ProspectPhysiqueMedicalQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Questions du questionnaire médical SUNU pour les prospects physiques
        $questions = [
            // [
            //     'libelle' => 'Souffrez vous du diabète',
            //     'type_de_donnee' => TypeDonneeEnum::BOOLEAN,
            //     'destinataire' => TypeDemandeurEnum::CLIENT,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            // [
            //     'libelle' => 'Mangez vous le porc ?',
            //     'type_de_donnee' => TypeDonneeEnum::BOOLEAN,
            //     'destinataire' => TypeDemandeurEnum::CLIENT,
            //     'est_obligatoire' => true,
            //     'est_active' => true,
            // ],
            [
                'libelle' => 'Quel sport pratiquez-vous?',
                'type_de_donnee' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::CLIENT,
                'est_obligatoire' => true,
                'est_active' => true,
            ],
            [
                'libelle' => 'Consommation du tabac',
                'type_de_donnee' => TypeDonneeEnum::RADIO,
                'destinataire' => TypeDemandeurEnum::CLIENT,
                'est_obligatoire' => true,
                'est_active' => true,
                'options' => ['pas du tout', 'un peu', 'modérément', 'beaucoup'],
            ],
            [
                'libelle' => 'Consommation d\'alcool',
                'type_de_donnee' => TypeDonneeEnum::RADIO,
                'destinataire' => TypeDemandeurEnum::CLIENT,
                'est_obligatoire' => true,
                'est_active' => true,
                'options' => ['pas du tout', 'un peu', 'modérément', 'beaucoup'],
            ],
            [
                'libelle' => 'Nom et Adresse de votre médecin traitant',
                'type_de_donnee' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::CLIENT,
                'est_obligatoire' => true,
                'est_active' => true,
            ],
        ];

        // Assign creator (cree_par_id) to a medecin controleur personnel only
        $personnel = Personnel::whereHas('user', function($q) {
            $q->whereHas('roles', function($qr) {
                $qr->where('name', RoleEnum::MEDECIN_CONTROLEUR->value);
            });
        })->first();
        if (!$personnel) {
            throw new \RuntimeException('Aucun médecin contrôleur trouvé pour renseigner cree_par_id des questions.');
        }

        foreach ($questions as $question) {
            $question['cree_par_id'] = $personnel->id;
            Question::create($question);
        }
    }
}