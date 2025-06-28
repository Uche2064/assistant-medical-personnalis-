<?php

namespace App\Observers;

use App\Enums\StatutValidationEnum;
use App\Enums\TypeClientEnum;
use App\Enums\TypeDemandeurEnum;
use App\Models\Client;
use App\Models\DemandeAdhesion;
use App\Models\Entreprise;
use App\Models\Prestataire;
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
            dd($demandeAdhesion);
            try {
                DB::beginTransaction();
                
                // Vérifier si un utilisateur avec cet email ou contact existe déjà
                $user = User::where('email', $demandeAdhesion->email)
                    ->orWhere('contact', $demandeAdhesion->contact)
                    ->first();
                    
                if (!$user) {
                    // Créer un nouvel utilisateur uniquement s'il n'existe pas
                    $userData = [
                        'email' => $demandeAdhesion->email,
                        'contact' => $demandeAdhesion->contact,
                        'adresse' => $demandeAdhesion->adresse,
                    ];
                    
                    // Ajouter les champs spécifiques selon le type de demandeur
                    if ($demandeAdhesion->type_demande === TypeDemandeurEnum::PROSPECT_PHYSIQUE) {
                        // Pour un client individuel (humain)
                        $userData['nom'] = $demandeAdhesion->nom_demandeur;
                        $userData['prenoms'] = $demandeAdhesion->prenoms_demandeur;
                        $userData['sexe'] = $demandeAdhesion->sexe;
                        $userData['date_naissance'] = $demandeAdhesion->date_naissance ?? null;
                    } else {
                        // Pour les entreprises et prestataires
                        $userData['raison_sociale'] = $demandeAdhesion->raison_sociale;
                    }
                    
                    $user = User::create($userData);
                }
                
                // Créer l'entité appropriée selon le type de demandeur
                if ($demandeAdhesion->type_demande === TypeDemandeurEnum::PROSPECT_PHYSIQUE) {
                    // Pour un client physique (individu)
                    $existingClient = Client::where('user_id', $user->id)->first();
                    
                    if (!$existingClient) {
                        Client::create([
                            'user_id' => $user->id,
                            'profession' => $demandeAdhesion->profession,
                            'type_client' => TypeClientEnum::PHYSIQUE,
                            'statut_validation' => $demandeAdhesion->statut,
                        ]);
                    }
                } elseif ($demandeAdhesion->type_demande === TypeDemandeurEnum::PROSPECT_MORAL) {
                    // Pour un client moral (entreprise)
                    $existingClient = Client::where('user_id', $user->id)->first();
                    
                    if (!$existingClient) {
                        Client::create([
                            'user_id' => $user->id,
                            'type_client' => TypeClientEnum::MORAL,
                            'statut_validation' => $demandeAdhesion->statut,
                        ]);
                    }
                } else {
                    // Pour tous les types de prestataires (CENTRE_DE_SOINS, MEDECIN_LIBERAL, etc.)
                    $existingPrestataire = Prestataire::where('user_id', $user->id)->first();
                    
                    if (!$existingPrestataire) {
                        Prestataire::create([
                            'user_id' => $user->id,
                            'type' => $demandeAdhesion->type_demande->value,
                            'raison_sociale' => $demandeAdhesion->raison_sociale,
                            'statut_validation' => $demandeAdhesion->statut,
                        ]);
                    }
                }
                
                // Valider la transaction
                DB::commit();
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de la création utilisateur après validation demande: ' . $e->getMessage());
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
