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

        $user = User::updateOrCreate(
            ['email' => $gestionnaireEmail],
            [
                'password' => Hash::make($plainPassword),
                'adresse' => 'nyekonakpoè',
                'est_actif' => false,
            ]
        );

        Personnel::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nom' => 'gestionnaire',
                "sexe" => 'M',
                'prenoms' => 'gest',
            ]
        );

        Log::info("Gestionnaire created with password: " . $plainPassword);
        $user->assignRole(RoleEnum::GESTIONNAIRE->value);

        dispatch(new SendCredentialsJob($user, $plainPassword));
    }
}
