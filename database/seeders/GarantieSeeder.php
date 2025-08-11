<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\CategorieGarantie;
use App\Models\Garantie;
use App\Models\Personnel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GarantieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer un médecin contrôleur
        $medecinControleur = Personnel::whereHas('user', function($query) {
            $query->whereHas('roles', function($q) {
                $q->where('name', RoleEnum::MEDECIN_CONTROLEUR->value);
            });
        })->first();

        if (!$medecinControleur) {
            throw new \Exception('Aucun médecin contrôleur trouvé. Assurez-vous que PersonnelSeeder a été exécuté en premier.');
        }

        // Récupérer les catégories
        $sante = CategorieGarantie::where('libelle', 'sante')->first();
        $pharmacie = CategorieGarantie::where('libelle', 'pharmacie')->first();
        $laboratoire = CategorieGarantie::where('libelle', 'laboratoire')->first();
        $optique = CategorieGarantie::where('libelle', 'optique')->first();
        $dentaire = CategorieGarantie::where('libelle', 'dentaire')->first();
        $maternite = CategorieGarantie::where('libelle', 'maternite')->first();
        $urgence = CategorieGarantie::where('libelle', 'urgence')->first();
        $prevention = CategorieGarantie::where('libelle', 'prevention')->first();

        $garanties = [
            // Garanties Santé
            [
                'libelle' => 'consultation medecin generaliste',
                'categorie_garantie_id' => $sante->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 15000,
                'prix_standard' => 5000,
                'taux_couverture' => 80.00,
            ],
            [
                'libelle' => 'consultation specialiste',
                'categorie_garantie_id' => $sante->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 25000,
                'prix_standard' => 8000,
                'taux_couverture' => 80.00,
            ],
            [
                'libelle' => 'hospitalisation',
                'categorie_garantie_id' => $sante->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 500000,
                'prix_standard' => 150000,
                'taux_couverture' => 85.00,
            ],
            [
                'libelle' => 'chirurgie',
                'categorie_garantie_id' => $sante->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 1000000,
                'prix_standard' => 300000,
                'taux_couverture' => 90.00,
            ],

            // Garanties Pharmacie
            [
                'libelle' => 'medicaments generiques',
                'categorie_garantie_id' => $pharmacie->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 50000,
                'prix_standard' => 15000,
                'taux_couverture' => 70.00,
            ],
            [
                'libelle' => 'medicaments specialises',
                'categorie_garantie_id' => $pharmacie->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 100000,
                'prix_standard' => 25000,
                'taux_couverture' => 75.00,
            ],
            [
                'libelle' => 'produits pharmaceutiques',
                'categorie_garantie_id' => $pharmacie->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 30000,
                'prix_standard' => 8000,
                'taux_couverture' => 65.00,
            ],

            // Garanties Laboratoire
            [
                'libelle' => 'analyses sanguines',
                'categorie_garantie_id' => $laboratoire->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 75000,
                'prix_standard' => 20000,
                'taux_couverture' => 80.00,
            ],
            [
                'libelle' => 'examens radiologiques',
                'categorie_garantie_id' => $laboratoire->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 120000,
                'prix_standard' => 35000,
                'taux_couverture' => 85.00,
            ],
            [
                'libelle' => 'tests de diagnostic',
                'categorie_garantie_id' => $laboratoire->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 100000,
                'prix_standard' => 28000,
                'taux_couverture' => 80.00,
            ],

            // Garanties Optique
            [
                'libelle' => 'consultation ophtalmologue',
                'categorie_garantie_id' => $optique->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 20000,
                'prix_standard' => 6000,
                'taux_couverture' => 75.00,
            ],
            [
                'libelle' => 'lunettes de vue',
                'categorie_garantie_id' => $optique->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 80000,
                'prix_standard' => 25000,
                'taux_couverture' => 70.00,
            ],
            [
                'libelle' => 'lentilles de contact',
                'categorie_garantie_id' => $optique->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 40000,
                'prix_standard' => 12000,
                'taux_couverture' => 65.00,
            ],

            // Garanties Dentaire
            [
                'libelle' => 'consultation dentaire',
                'categorie_garantie_id' => $dentaire->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 25000,
                'prix_standard' => 8000,
                'taux_couverture' => 75.00,
            ],
            [
                'libelle' => 'detartrage',
                'categorie_garantie_id' => $dentaire->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 15000,
                'prix_standard' => 5000,
                'taux_couverture' => 80.00,
            ],
            [
                'libelle' => 'soins conservateurs',
                'categorie_garantie_id' => $dentaire->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 60000,
                'prix_standard' => 18000,
                'taux_couverture' => 70.00,
            ],
            [
                'libelle' => 'prothese dentaire',
                'categorie_garantie_id' => $dentaire->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 200000,
                'prix_standard' => 60000,
                'taux_couverture' => 60.00,
            ],

            // Garanties Maternité
            [
                'libelle' => 'suivi de grossesse',
                'categorie_garantie_id' => $maternite->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 150000,
                'prix_standard' => 45000,
                'taux_couverture' => 85.00,
            ],
            [
                'libelle' => 'accouchement',
                'categorie_garantie_id' => $maternite->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 300000,
                'prix_standard' => 90000,
                'taux_couverture' => 90.00,
            ],
            [
                'libelle' => 'soins post-natals',
                'categorie_garantie_id' => $maternite->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 100000,
                'prix_standard' => 30000,
                'taux_couverture' => 80.00,
            ],

            // Garanties Urgence
            [
                'libelle' => 'ambulance',
                'categorie_garantie_id' => $urgence->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 50000,
                'prix_standard' => 15000,
                'taux_couverture' => 100.00,
            ],
            [
                'libelle' => 'soins d\'urgence',
                'categorie_garantie_id' => $urgence->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 200000,
                'prix_standard' => 60000,
                'taux_couverture' => 95.00,
            ],
            [
                'libelle' => 'reanimation',
                'categorie_garantie_id' => $urgence->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 500000,
                'prix_standard' => 150000,
                'taux_couverture' => 100.00,
            ],

            // Garanties Prévention
            [
                'libelle' => 'bilan de sante',
                'categorie_garantie_id' => $prevention->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 100000,
                'prix_standard' => 30000,
                'taux_couverture' => 80.00,
            ],
            [
                'libelle' => 'vaccinations',
                'categorie_garantie_id' => $prevention->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 50000,
                'prix_standard' => 15000,
                'taux_couverture' => 90.00,
            ],
            [
                'libelle' => 'depistage',
                'categorie_garantie_id' => $prevention->id,
                'medecin_controleur_id' => $medecinControleur->id,
                'plafond' => 75000,
                'prix_standard' => 22000,
                'taux_couverture' => 85.00,
            ],
        ];

        foreach ($garanties as $garantie) {
            Garantie::create($garantie);
        }

        $this->command->info('Garanties créées avec succès par le médecin contrôleur !');
    }
} 