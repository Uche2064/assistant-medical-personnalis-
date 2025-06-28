<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notificationService = resolve(NotificationService::class);

        $adminEmail = 'cpica5125@gmail.com';
        $adminUsername = 'globaladmin';
        $plainPassword = User::genererMotDePasse();

        $user = User::updateOrCreate(
            ['email' => $adminEmail, 'username' => $adminUsername],
            [
                'nom' => 'Global',
                'prenoms' => 'Admin',
                'password' => bcrypt($plainPassword),
                'adresse' => 'nyekonakpoè',
                'est_actif' => true
            ]
        );

        $user->assignRole(RoleEnum::ADMIN_GLOBAL->value);

        $notificationService->sendCredentials($user, $plainPassword);
    }
}
