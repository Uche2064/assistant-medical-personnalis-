<?php

namespace App\Filament\Resources\Gestionnaires\Pages;

use App\Filament\Resources\Gestionnaires\GestionnaireResource;
use App\Helpers\ImageUploadHelper;
use App\Jobs\SendCredentialsJob;
use App\Models\Personne;
use App\Models\Personnel;
use App\Models\User;
use App\Enums\RoleEnum;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class CreateGestionnaire extends CreateRecord
{
    protected static string $resource = GestionnaireResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Retourner les données pour qu'elles soient disponibles dans handleRecordCreation
        return $data;
    }
    
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $password = User::genererMotDePasse();
        $photoUrl = null;
        
        // Gestion de l'upload de la photo
        // Filament FileUpload stocke déjà le fichier et retourne le chemin relatif
        if (isset($data['photo']) && !empty($data['photo'])) {
            // Filament retourne un tableau avec le chemin
            $photoPath = is_array($data['photo']) ? (reset($data['photo']) ?: null) : $data['photo'];
            
            if ($photoPath) {
                // Filament stocke dans storage/app/public/uploads
                // Le chemin retourné est relatif au disque 'public' (ex: "uploads/filename.jpg")
                // On stocke ce chemin tel quel pour que ImageEntry puisse l'afficher
                // ImageEntry avec disk('public') cherchera dans storage/app/public
                $photoUrl = $photoPath;
                
                // Log pour débogage
                Log::info("Photo path from Filament: " . $photoPath);
            }
        }
        
        return DB::transaction(function () use ($data, $password, $photoUrl) {
            // Créer d'abord la personne
            $personne = Personne::create([
                'nom' => $data['nom'],
                'prenoms' => $data['prenoms'] ?? null,
                'date_naissance' => $data['date_naissance'] ?? null,
                'sexe' => $data['sexe'] ?? null,
                'profession' => $data['profession'] ?? null,
            ]);
            
            // Créer l'utilisateur
            $user = User::create([
                'email' => $data['email'],
                'contact' => $data['contact'],
                'adresse' => $data['adresse'] ?? null,
                'password' => Hash::make($password),
                'est_actif' => false,
                'mot_de_passe_a_changer' => true,
                'email_verifier_a' => now(),
                'photo_url' => $photoUrl,
                'personne_id' => $personne->id,
            ]);
            
            // S'assurer que le rôle gestionnaire existe, sinon le créer
            $roleName = RoleEnum::GESTIONNAIRE->value;
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
            
            // Assigner le rôle gestionnaire
            $user->assignRole($role);
            
            // Créer le personnel gestionnaire
            $personnel = Personnel::create([
                'user_id' => $user->id,
            ]);
            
            // Envoyer les identifiants par email
            dispatch(new SendCredentialsJob($user, $password));
            
            Log::info("Gestionnaire créé via Filament - Email: {$user->email}, Mot de passe: {$password}");
            
            return $personnel;
        });
    }
}
