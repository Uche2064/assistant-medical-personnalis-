<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommercialParrainageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les commerciaux
        $commerciaux = User::whereHas('roles', function($query) {
            $query->where('name', RoleEnum::COMMERCIAL->value);
        })->get();

        foreach ($commerciaux as $commercial) {
            // Générer un code parrainage unique s'il n'en a pas déjà un
            if (!$commercial->code_parrainage_commercial) {
                $codeParrainage = $this->genererCodeUnique();
                $commercial->update(['code_parrainage_commercial' => $codeParrainage]);
                
                $this->command->info("Code parrainage généré pour {$commercial->email}: {$codeParrainage}");
            }
        }

        $this->command->info('Codes parrainage générés pour tous les commerciaux.');
    }

    /**
     * Générer un code parrainage unique
     */
    private function genererCodeUnique()
    {
        do {
            $code = 'COM' . strtoupper(Str::random(6));
        } while (User::where('code_parrainage_commercial', $code)->exists());

        return $code;
    }
}