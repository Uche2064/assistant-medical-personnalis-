<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Jobs\SendCredentialsJob;
use App\Models\Gestionnaire;
use App\Models\User;
use App\Services\NotificationService;
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
        
        $notificationService = resolve(NotificationService::class);

        $gestionnaireEmail = 'gestionnaire@gmail.com';
        $plainPassword = User::genererMotDePasse();

        $user = User::updateOrCreate(
            ['email' => $gestionnaireEmail],
            [
                'nom' => 'gestionnaire',
                'prenoms' => 'gest',
                'password' => Hash::make($plainPassword),
                'adresse' => 'nyekonakpoè',
                'est_actif' => true
                
            ]
        );

        Gestionnaire::updateOrCreate(
            ['user_id' => $user->id],
        );

        Log::info($plainPassword);
        $user->assignRole(RoleEnum::GESTIONNAIRE->value);

        dispatch(new SendCredentialsJob($user, $plainPassword));
    }
}
