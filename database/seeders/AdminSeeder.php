<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Jobs\SendCredentialsJob;
use App\Models\Personnel;
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
        $contact = '22871610653';
        $plainPassword = User::genererMotDePasse();
        // creer l'entrée dans user
        $user = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'password' => Hash::make($plainPassword),
                'adresse' => 'nyekonakpoè',
                'contact' => $contact,
            ]
        );

        // on créer l'entrée dans personnel

        Personnel::updateOrCreate(
            [
                'nom' => $nom,
                'prenoms' => $prenoms,
                'user_id' => $user->id,
            ],
        );

        Log::info("Admin created with password: " . $plainPassword);
        $user->assignRole(RoleEnum::ADMIN_GLOBAL->value);

        dispatch(new SendCredentialsJob($user->load('personnel'), $plainPassword));
    }
}
