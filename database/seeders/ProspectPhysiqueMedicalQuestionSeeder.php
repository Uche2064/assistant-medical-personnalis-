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
                'libelle' => 'Quels sont vos taille et poids?',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Pression artérielle',
                'type_donnees' => TypeDonneeEnum::TEXT, 
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Avez-vous grossi ou maigri de plus de 5 Kgs depuis 6 mois? si oui, de combien?',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Avez-vous été déjà refusé(e), ajourné(e) par une société d\'assurance sur la vie?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Motif']),
            ],
            [
                'libelle' => 'Êtes-vous titulaire d\'une pension d\'invalidité?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Taux']),
            ],
            [
                'libelle' => 'Êtes-vous actuellement en arrêt de travail?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Motif']),
            ],
            [
                'libelle' => 'Durant les 5 dernières années, avez-vous dû interrompre votre travail pendant plus de 3 semaines?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Quand-Durée de chaque arrêt-Motif']),
            ],
            [
                'libelle' => 'Avez-vous été victime d\'un accident?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Date et séquelle']),
            ],
            [
                'libelle' => 'Avez-vous souffert ou souffrez-vous d\'une maladie ou d\'une infirmité? (affection pulmonaire, cardiaque, nerveuse, rénale, diabète, maladie du foie, cancer...)',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Laquelle']),
            ],
            [
                'libelle' => 'Avez-vous été atteint(e) de paludisme, si oui, avez-vous subi un traitement oral ou par piqûre?',
                'type_donnees' => TypeDonneeEnum::RADIO,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['options' => ['Non', 'Oui - Oral', 'Oui - Piqûre']]),
            ],
            [
                'libelle' => 'Avez-vous fait récemment l\'objet d\'une analyse de sang comportant le test de dépistage de l\'hépatite B ou du Sida?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Date et résultat']),
            ],
            [
                'libelle' => 'Avez-vous subi une transfusion ou bien une transfusion du sang?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Date et motif']),
            ],
            [
                'libelle' => 'Devez-vous être hospitalisé(e) prochainement ou subir des examens médicaux?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Date, nature, motif']),
            ],
            [
                'libelle' => 'Avez-vous subi des interventions chirurgicales ou devez-vous être opéré(e) prochainement?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Date, nature']),
            ],
            [
                'libelle' => 'Suivez-vous un régime ou un traitement médical?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Lequel']),
            ],
            [
                'libelle' => 'Présentez-vous ou avez-vous présenté un des symptômes suivants: Éruption cutanée, présence de ganglions anormaux, diarrhée chronique, fièvre prolongée?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Lesquels']),
            ],
            [
                'libelle' => 'Présentez-vous ou avez-vous présenté une des maladies suivantes: Méningite, hépatite B, verrues fréquentes, mycoses, affections génitales?',
                'type_donnees' => TypeDonneeEnum::BOOLEAN,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['precision' => 'Lesquelles']),
            ],
            [
                'libelle' => 'Quel sport pratiquez-vous?',
                'type_donnees' => TypeDonneeEnum::TEXT,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
            ],
            [
                'libelle' => 'Consommation du tabac',
                'type_donnees' => TypeDonneeEnum::RADIO,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['options' => ['pas du tout', 'un peu', 'modérément', 'beaucoup']]),
            ],
            [
                'libelle' => 'Consommation d\'alcool',
                'type_donnees' => TypeDonneeEnum::RADIO,
                'destinataire' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
                'obligatoire' => true,
                'est_actif' => true,
                'options' => json_encode(['options' => ['pas du tout', 'un peu', 'modérément', 'beaucoup']]),
            ],
            [
                'libelle' => 'Nom et Adresse de votre médecin traitant',
                'type_donnees' => TypeDonneeEnum::TEXT,
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