<?php

namespace Database\Seeders;

use App\Models\CommercialParrainageCode;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MigrateExistingParrainageCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les commerciaux avec des codes parrainage existants
        $commerciaux = User::whereHas('roles', function($query) {
            $query->where('name', RoleEnum::COMMERCIAL->value);
        })->get();

        foreach ($commerciaux as $commercial) {
            // Vérifier si le commercial a déjà des codes dans la nouvelle table
            $existingCodes = CommercialParrainageCode::where('commercial_id', $commercial->id)->count();

            if ($existingCodes === 0) {
                // Créer un code dans la nouvelle table avec le code existant
                $now = now();
                $expiration = $now->copy()->addYear(); // 1 an à partir de maintenant

                CommercialParrainageCode::generateNewCode($commercial->id);

                $this->command->info("Code parrainage migré pour {$commercial->email}: {$commercial->code_parrainage_commercial}");
            }
        }

        $this->command->info('Migration des codes parrainage existants terminée.');
    }
}
