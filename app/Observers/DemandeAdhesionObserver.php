<?php

namespace App\Observers;

use App\Enums\StatutValidationEnum;
use App\Enums\TypeClientEnum;
use App\Models\Client;
use App\Models\DemandeAdhesion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DemandeAdhesionObserver
{
    /**
     * Handle the DemandeAdhesion "created" event.
     */
    public function created(DemandeAdhesion $demandeAdhesion): void
    {
        //
    }

    public function updated(DemandeAdhesion $demandeAdhesion): void
    {
        // Si la demande vient d'être validée
        if ($demandeAdhesion->isDirty('statut') && $demandeAdhesion->statut === StatutValidationEnum::VALIDE) {
            try {
                // Utiliser une transaction pour garantir l'atomicité
                DB::beginTransaction();
                
                // Récupérer les informations complémentaires
                $infosComplementaires = is_array($demandeAdhesion->infos_complementaires) 
                ? $demandeAdhesion->infos_complementaires 
                : json_decode($demandeAdhesion->getRawOriginal('infos_complementaires'), true) ?? [];
                
                // Vérifier si un utilisateur avec cet email ou contact existe déjà
                $user = User::where('email', $demandeAdhesion->email)
                    ->orWhere('contact', $demandeAdhesion->contact)
                    ->first();
                    
                if (!$user) {
                    // Créer un nouvel utilisateur uniquement s'il n'existe pas
                    $user = User::create([
                        'nom' => $demandeAdhesion->nom,
                        'prenoms' => $demandeAdhesion->prenoms,
                        'email' => $demandeAdhesion->email,
                        'contact' => $demandeAdhesion->contact,
                        'sexe' => $demandeAdhesion->sexe,
                        'adresse' => $demandeAdhesion->adresse,
                        'photo' => $infosComplementaires['photo_url'] ?? null,
                        'password' => Hash::make(User::genererMotDePasse()) // Mot de passe temporaire
                    ]);
                }
                
                // Vérifier si un client existe déjà pour cet utilisateur
                $existingClient = Client::where('user_id', $user->id)->first();
                
                if (!$existingClient) {
                    // Créer un client associé à cet utilisateur
                    Client::create([
                        'user_id' => $user->id,
                        'profession' => $demandeAdhesion->profession,
                        'type_client' => TypeClientEnum::CLIENT_PHYSIQUE,
                        'statut_validation' => $demandeAdhesion->statut,
                    ]);
                }
                
                // Valider la transaction
                DB::commit();
                
                // Envoyer un email pour définir le mot de passe
                // Notification::send($user, new SetPasswordNotification());
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de la création utilisateur/client: ' . $e->getMessage());
            }
        }
    }

    /**
     * Handle the DemandeAdhesion "deleted" event.
     */
    public function deleted(DemandeAdhesion $demandeAdhesion): void
    {
        //
    }

    /**
     * Handle the DemandeAdhesion "restored" event.
     */
    public function restored(DemandeAdhesion $demandeAdhesion): void
    {
        //
    }

    /**
     * Handle the DemandeAdhesion "force deleted" event.
     */
    public function forceDeleted(DemandeAdhesion $demandeAdhesion): void
    {
        //
    }
}
