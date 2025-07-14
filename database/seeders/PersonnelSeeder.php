<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Jobs\SendCredentialsJob;
use App\Models\Personnel;
use App\Models\User;
use App\Services\NotificationService;
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
        $this->createPersonnel('comptable1@gmail.com', 'Comptable', 'Compta', RoleEnum::COMPTABLE->value);
        $this->createPersonnel('medecin1@gmail.com', 'Médecin', 'Contrôle', RoleEnum::MEDECIN_CONTROLEUR->value);
        $this->createPersonnel('technicien1@gmail.com', 'Technicien', 'Tech', RoleEnum::TECHNICIEN->value);
        $this->createPersonnel('commercial1@gmail.com', 'Commercial', 'Ventes', RoleEnum::COMMERCIAL->value);
    }

    private function createPersonnel(string $email, string $nom, string $prenoms, string $role): void
    {
        $plainPassword = User::genererMotDePasse();

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'nom' => $nom,
                'prenoms' => $prenoms,
                'password' => Hash::make($plainPassword),
                'adresse' => 'Nyékonakpoè',
                'est_actif' => true,
            ]
        );

        $code_parainage = Personnel::genererCodeParainage();

        if($role == RoleEnum::COMPTABLE->value){
            Personnel::create([
                'user_id' => $user->id,
                'code_parainage' => $code_parainage,
                'gestionnaire_id' => 1,
            ]);
        } else {
            Personnel::create([
                'user_id' => $user->id,
                'gestionnaire_id' => 1,
            ]);
        }

        $user->assignRole($role);
        Log::info("{$role} credentials - Email: {$email}, Password: {$plainPassword}");

        dispatch(new SendCredentialsJob($user, $plainPassword));
    }
}
