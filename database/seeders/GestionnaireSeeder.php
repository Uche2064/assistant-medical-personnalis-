<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Jobs\SendCredentialsJob;
use App\Models\Personnel;
use App\Models\Personne;
use App\Models\User;
// use App\Services\NotificationService; // Commenté car service non vérifié
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class GestionnaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // $notificationService = resolve(NotificationService::class); // Commenté car service non vérifié

        $gestionnaireEmail = 'gestionnaire@gmail.com';
        $plainPassword = User::genererMotDePasse();

        $personne = Personne::updateOrCreate(
            ['nom' => 'gestionnaire', 'prenoms' => 'gest'],
            ['sexe' => 'M']
        );

        $user = User::updateOrCreate(
            ['email' => $gestionnaireEmail],
            [
                'password' => Hash::make($plainPassword),
                'adresse' => 'nyekonakpoè',
                'est_actif' => false,
                'personne_id' => $personne->id,
                'contact' => random_int(10000000, 99999999)
            ]
        );

        Personnel::updateOrCreate(
            ['user_id' => $user->id],
            []
        );

        Log::info("Gestionnaire created with password: " . $plainPassword);
        $user->assignRole(RoleEnum::GESTIONNAIRE->value);

        dispatch(new SendCredentialsJob($user, $plainPassword));
    }
}
