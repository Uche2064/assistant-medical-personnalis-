<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\TypeContratEnum;
use App\Models\CategorieGarantie;
use App\Models\TypeContrat;
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
        $categorieGaranties = CategorieGarantie::all();

        if ($categorieGaranties->isEmpty()) {
            throw new \Exception('Aucune catégorie de garantie trouvée. Assurez-vous que CategorieGarantieSeeder a été exécuté en premier.');
        }

        $contrats = [
            [
                'libelle' => TypeContratEnum::BASIC,
                'technicien_id' => $technicien->id,
                'prime_standard' => 250000,
                'frais_gestion' => 20.0,
                'est_actif' => true,
            ],
            [
                'libelle' => TypeContratEnum::STANDARD,
                'technicien_id' => $technicien->id,
                'prime_standard' => 450000,
                'frais_gestion' => 20.0,
                'est_actif' => true,
            ],
            [
                'libelle' => TypeContratEnum::PREMIUM,
                'technicien_id' => $technicien->id,
                'prime_standard' => 750000,
                'frais_gestion' => 20.0,
                'est_actif' => true,
            ],
            [
                'libelle' => TypeContratEnum::TEAM,
                'technicien_id' => $technicien->id,
                'prime_standard' => 5000000,
                'frais_gestion' => 20.0,
                'est_actif' => true,
            ],
        ];

        foreach ($contrats as $contratData) {
            $typeContrat = TypeContrat::create([
                'libelle' => $contratData['libelle'],
                'technicien_id' => $contratData['technicien_id'],
                'prime_standard' => $contratData['prime_standard'],
                'est_actif' => $contratData['est_actif'],
            ]);

            // Associer des catégories de garanties avec champs pivot
            $this->associerCategoriesGaranties($typeContrat, $categorieGaranties, $contratData['libelle'], $contratData['frais_gestion']);
        }

        $this->command->info('Contrats créés avec succès par le technicien !');
    }

    /**
     * Associe des catégories de garanties à un contrat via la table pivot
     */
    private function associerCategoriesGaranties(TypeContrat $typeContratModel, $categorieGaranties, TypeContratEnum $typeContrat, float $fraisGestion): void
    {
        // Déterminer combien de catégories associer selon le type de contrat
        $nombreCategories = $this->getNombreCategoriesByType($typeContrat);
        
        // Prendre un nombre aléatoire de catégories
        $categoriesSelectionnees = $categorieGaranties->random(min($nombreCategories, $categorieGaranties->count()));
        
        foreach ($categoriesSelectionnees as $categorie) {
            $typeContratModel->categoriesGaranties()->attach($categorie->id, [
                'couverture' => $this->getCouvertureByType($typeContrat),
                'frais_gestion' => $fraisGestion,
            ]);
        }
        
    }

    /**
     * Détermine le nombre de catégories à associer selon le type de contrat
     */
    private function getNombreCategoriesByType(TypeContratEnum $type): int
    {
        return match($type) {
            TypeContratEnum::BASIC => 1,      // 1 catégorie pour BASIC
            TypeContratEnum::STANDARD => 2,   // 2 catégories pour STANDARD
            TypeContratEnum::PREMIUM => 3,    // 3 catégories pour PREMIUM
            TypeContratEnum::TEAM => 4,       // 4 catégories pour TEAM
        };
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
