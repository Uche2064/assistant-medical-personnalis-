<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\TypeContratEnum;
use App\Models\CategorieGarantie;
use App\Models\Contrat;
use App\Models\Personnel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer un technicien
        $technicien = Personnel::whereHas('user', function($query) {
            $query->whereHas('roles', function($q) {
                $q->where('name', RoleEnum::TECHNICIEN->value);
            });
        })->first();

        if (!$technicien) {
            throw new \Exception('Aucun technicien trouvé. Assurez-vous que PersonnelSeeder a été exécuté en premier.');
        }

        // Récupérer les catégories de garanties
        $sante = CategorieGarantie::where('libelle', 'sante')->first();
        $pharmacie = CategorieGarantie::where('libelle', 'pharmacie')->first();
        $laboratoire = CategorieGarantie::where('libelle', 'laboratoire')->first();
        $optique = CategorieGarantie::where('libelle', 'optique')->first();
        $dentaire = CategorieGarantie::where('libelle', 'dentaire')->first();
        $maternite = CategorieGarantie::where('libelle', 'maternite')->first();
        $urgence = CategorieGarantie::where('libelle', 'urgence')->first();
        $prevention = CategorieGarantie::where('libelle', 'prevention')->first();

        $contrats = [
            [
                'type_contrat' => TypeContratEnum::BASIC,
                'technicien_id' => $technicien->id,
                'prime_standard' => 25000, // 25€ = ~25,000 FCFA
                'date_debut' => now(),
                'date_fin' => now()->addYear(),
                'est_actif' => true,
                'categories_garanties_standard' => [$sante->id, $pharmacie->id],
                'description' => 'Contrat de base avec couverture minimale pour les soins essentiels',
            ],
            [
                'type_contrat' => TypeContratEnum::STANDARD,
                'technicien_id' => $technicien->id,
                'prime_standard' => 45000, // 45€ = ~45,000 FCFA
                'date_debut' => now(),
                'date_fin' => now()->addYear(),
                'est_actif' => true,
                'categories_garanties_standard' => [$sante->id, $pharmacie->id, $laboratoire->id, $optique->id],
                'description' => 'Contrat standard avec couverture complète pour les soins courants',
            ],
            [
                'type_contrat' => TypeContratEnum::PREMIUM,
                'technicien_id' => $technicien->id,
                'prime_standard' => 75000, // 75€ = ~75,000 FCFA
                'date_debut' => now(),
                'date_fin' => now()->addYear(),
                'est_actif' => true,
                'categories_garanties_standard' => [$sante->id, $pharmacie->id, $laboratoire->id, $optique->id, $dentaire->id, $maternite->id, $urgence->id],
                'description' => 'Contrat premium avec couverture étendue incluant les soins spécialisés',
            ],
            [
                'type_contrat' => TypeContratEnum::TEAM,
                'technicien_id' => $technicien->id,
                'prime_standard' => 120000, // 120€ = ~120,000 FCFA
                'date_debut' => now(),
                'date_fin' => now()->addYear(),
                'est_actif' => true,
                'categories_garanties_standard' => [$sante->id, $pharmacie->id, $laboratoire->id, $optique->id, $dentaire->id, $maternite->id, $urgence->id, $prevention->id],
                'description' => 'Contrat pour équipes/entreprises avec couverture complète et prévention',
            ],
        ];

        foreach ($contrats as $contratData) {
            $contrat = Contrat::create([
                'type_contrat' => $contratData['type_contrat'],
                'technicien_id' => $contratData['technicien_id'],
                'prime_standard' => $contratData['prime_standard'],
                'date_debut' => $contratData['date_debut'],
                'date_fin' => $contratData['date_fin'],
                'est_actif' => $contratData['est_actif'],
                'categories_garanties_standard' => $contratData['categories_garanties_standard'],
            ]);

            // Assigner les catégories de garanties par défaut
            foreach ($contratData['categories_garanties_standard'] as $categorieId) {
                $contrat->categoriesGaranties()->attach($categorieId, [
                    'couverture' => $this->getCouvertureByType($contratData['type_contrat']),
                ]);
            }
        }

        $this->command->info('Contrats créés avec succès par le technicien !');
    }

    /**
     * Détermine le taux de couverture selon le type de contrat
     */
    private function getCouvertureByType(TypeContratEnum $type): float
    {
        return match($type) {
            TypeContratEnum::BASIC => 70.00,
            TypeContratEnum::STANDARD => 80.00,
            TypeContratEnum::PREMIUM => 90.00,
            TypeContratEnum::TEAM => 95.00,
        };
    }
}
