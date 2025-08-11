<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\CategorieGarantie;
use App\Models\Personnel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorieGarantieSeeder extends Seeder
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

        $categories = [
            [
                'libelle' => 'sante',
                'description' => 'Garanties liées aux soins de santé, consultations médicales, hospitalisation et traitements',
                'medecin_controleur_id' => $medecinControleur->id,
            ],
            [
                'libelle' => 'pharmacie',
                'description' => 'Garanties pour les médicaments, ordonnances et produits pharmaceutiques',
                'medecin_controleur_id' => $medecinControleur->id,
            ],
            [
                'libelle' => 'laboratoire',
                'description' => 'Garanties pour les analyses médicales, tests de diagnostic et examens de laboratoire',
                'medecin_controleur_id' => $medecinControleur->id,
            ],
            [
                'libelle' => 'optique',
                'description' => 'Garanties pour les soins ophtalmologiques, lunettes et lentilles de contact',
                'medecin_controleur_id' => $medecinControleur->id,
            ],
            [
                'libelle' => 'dentaire',
                'description' => 'Garanties pour les soins dentaires, consultations et traitements odontologiques',
                'medecin_controleur_id' => $medecinControleur->id,
            ],
            [
                'libelle' => 'maternite',
                'description' => 'Garanties spécialisées pour la grossesse, l\'accouchement et les soins post-natals',
                'medecin_controleur_id' => $medecinControleur->id,
            ],
            [
                'libelle' => 'urgence',
                'description' => 'Garanties pour les soins d\'urgence, ambulance et interventions médicales d\'urgence',
                'medecin_controleur_id' => $medecinControleur->id,
            ],
            [
                'libelle' => 'prevention',
                'description' => 'Garanties pour les examens de prévention, vaccinations et bilans de santé',
                'medecin_controleur_id' => $medecinControleur->id,
            ],
        ];

        foreach ($categories as $categorie) {
            CategorieGarantie::create($categorie);
        }

        $this->command->info('Catégories de garanties créées avec succès par le médecin contrôleur !');
    }
} 