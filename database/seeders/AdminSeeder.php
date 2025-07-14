<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Jobs\SendCredentialsJob;
use App\Jobs\SendLoginNotificationJob;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        $adminEmail = 'tutowordpress2064@gmail.com';
        $adminUsername = 'globaladmin';
        $plainPassword = User::genererMotDePasse();

        $user = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'nom' => 'Global',
                'prenoms' => 'Admin',
                'password' => Hash::make($plainPassword),
                'adresse' => 'nyekonakpoè',
                'est_actif' => true
                
            ]
        );

        Log::info($plainPassword);
        $user->assignRole(RoleEnum::ADMIN_GLOBAL->value);

        dispatch(new SendCredentialsJob($user, $plainPassword));
    }
}
