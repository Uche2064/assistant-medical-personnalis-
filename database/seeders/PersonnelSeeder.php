<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Jobs\SendCredentialsJob;
use App\Models\Personnel;
use App\Models\User;
// use App\Services\NotificationService; // Commenté car service non vérifié
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PersonnelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

      public function run(): void
    {
        // Récupérer le premier gestionnaire créé
        $gestionnaire = Personnel::whereHas('user', function($query) {
            $query->whereHas('roles', function($q) {
                $q->where('name', RoleEnum::GESTIONNAIRE->value);
            });
        })->first();

        if (!$gestionnaire) {
            throw new \Exception('Aucun gestionnaire trouvé. Assurez-vous que GestionnaireSeeder a été exécuté en premier.');
        }

        $this->createPersonnel('uchesonUche5@gmail.com', 'Comptable', 'Compta', RoleEnum::COMPTABLE->value, $gestionnaire->id);
        $this->createPersonnel('godswilllek02@gmail.com', 'Médecin', 'Contrôle', RoleEnum::MEDECIN_CONTROLEUR->value, $gestionnaire->id);
        $this->createPersonnel('godswill2064@gmail.com', 'Technicien', 'Tech', RoleEnum::TECHNICIEN->value, $gestionnaire->id);
        $this->createPersonnel('anatoliebratt@gmail.com', 'Commercial', 'Ventes', RoleEnum::COMMERCIAL->value, $gestionnaire->id);
    }

    private function createPersonnel(string $email, string $nom, string $prenoms, string $role, int $gestionnaireId): void
    {
        $plainPassword = User::genererMotDePasse();

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'password' => Hash::make($plainPassword),
                'adresse' => 'Nyékonakpoè',
                'est_actif' => false,
            ]
        );

        $code_parainage = Personnel::genererCodeParainage();

        if($role == RoleEnum::COMPTABLE->value){
            Personnel::create([
                'user_id' => $user->id,
                'nom' => $nom,
                'prenoms' => $prenoms,
                'code_parainage' => $code_parainage,
                'gestionnaire_id' => $gestionnaireId,
                "sexe" => 'M'



            ]);
        } else {
            Personnel::create([
                'user_id' => $user->id,
                'nom' => $nom,
                'prenoms' => $prenoms,
                "sexe" => 'M',
                'gestionnaire_id' => $gestionnaireId,
            ]);
        }

        $user->assignRole($role);
        Log::info("{$role} credentials - Email: {$email}, Password: {$plainPassword}");

        dispatch(new SendCredentialsJob($user, $plainPassword));
    }
}
