<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Jobs\SendCredentialsJob;
use App\Models\Personnel;
use App\Models\Personne;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $adminEmail = 'tutowordpress2064@gmail.com';
        $nom = 'admin';
        $prenoms = 'global';
        $plainPassword = User::genererMotDePasse();

        // Create Personne then User linked to it
        $personne = Personne::updateOrCreate(
            [ 'nom' => $nom, 'prenoms' => $prenoms ],
            [ 'sexe' => 'M' ]
        );

        $user = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'password' => Hash::make($plainPassword),
                'adresse' => 'nyekonakpoè',
                'contact' => random_int(10000000, 99999999),
                'personne_id' => $personne->id,
            ]
        );

        // Create minimal personnel row linked to user (schema only has user_id)
        Personnel::updateOrCreate(
            [ 'user_id' => $user->id ],
            []
        );

        Log::info("Admin created with password: " . $plainPassword);
        $user->assignRole(RoleEnum::ADMIN_GLOBAL->value);

        dispatch(new SendCredentialsJob($user, $plainPassword));
    }
}
