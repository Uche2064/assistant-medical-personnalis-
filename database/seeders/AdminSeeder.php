<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        

        $adminEmail = 'globaladmin@sunu-sante.com';
        $adminContact = '+22890000000';
        $adminUsername = 'globaladmin';

     $user = User::firstOrCreate([
            'email' => $adminEmail,
            'username'=> $adminUsername,
            'contact' => $adminContact
        ], [
            'nom' => 'Global',
            'prenoms' => 'Admin',
            'contact' => $adminContact,
            'password' => bcrypt('globaladmin@sunusante'),
            'adresse' => 'nyekonakpoè',
            'username' => 'globaladmin',
            'est_actif' => true
        ]);

        $user->assignRole(RoleEnum::ADMIN_GLOBAL->value);
    }
}
