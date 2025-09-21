<?php

namespace App\Observers;

use App\Enums\StatutValidationEnum;
use App\Enums\TypeClientEnum;
use App\Enums\TypeDemandeurEnum;
use App\Models\Client;
use App\Models\DemandesAdhesions;
use App\Models\Entreprise;
use App\Models\Personnel;
use App\Models\Personnes;
use App\Models\Prestataire;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DemandeAdhesionObserver
{
    /**
     * Handle the DemandesAdhesions "created" event.
     */
    public function created(DemandesAdhesions $demandeAdhesion): void
    {
        //
    }

    public function updated(DemandesAdhesions $demandeAdhesion): void
    {
        // Si la demande vient d'être validée
        if ($demandeAdhesion->isDirty('statut') && $demandeAdhesion->statut === StatutValidationEnum::VALIDEE) {
            try {
                DB::beginTransaction();
                
                // Vérifier si un utilisateur avec cet email ou contact existe déjà
                $user = Personnes::where('email', $demandeAdhesion->prospect->email)
                    ->orWhere('contact', $demandeAdhesion->prospect->contact)
                    ->first();
                    
                if (!$user) {
                    // Créer un nouvel utilisateur uniquement s'il n'existe pas
                    $userData = [
                        'email' => $demandeAdhesion->prospect->email,
                        'contact' => $demandeAdhesion->prospect->contact,
                        'adresse' => $demandeAdhesion->prospect->adresse,
                    ];
                    
                    // Ajouter les champs spécifiques selon le type de demandeur
                    if ($demandeAdhesion->type_demandeur === TypeDemandeurEnum::CLIENT) {
                        // Pour un client individuel (humain)
                        $userData['nom'] = $demandeAdhesion->prospect->nom_demandeur;
                        $userData['prenoms'] = $demandeAdhesion->prospect->prenoms_demandeur;
                        $userData['sexe'] = $demandeAdhesion->prospect->sexe;
                        $userData['date_naissance'] = $demandeAdhesion->prospect->date_naissance ?? null;
                    } else {
                        // Pour les entreprises et prestataires
                        $userData['raison_sociale'] = $demandeAdhesion->prospect->raison_sociale;
                    }
                    
                    $user = User::create($userData);
                }
                
                // Créer l'entité appropriée selon le type de demandeur
                if ($demandeAdhesion->type_demandeur === TypeDemandeurEnum::CLIENT) {
                    // Pour un client physique (individu)
                    $existingClient = Client::where('user_id', $user->id)->first();
                    
                    if (!$existingClient) {
                        Client::create([
                            'user_id' => $user->id,
                            'profession' => $demandeAdhesion->prospect->profession,
                            'type_client' => TypeClientEnum::CLIENT,
                        ]);
                    }
                } elseif ($demandeAdhesion->type_demandeur === TypeDemandeurEnum::MORAL) {
                    // Pour un client moral (entreprise)
                    $existingClient = Client::where('user_id', $user->id)->first();
                    
                    if (!$existingClient) {
                        Client::create([
                            'user_id' => $user->id,
                            'type_client' => TypeClientEnum::MORAL,
                        ]);
                    }
                } else {
                    // Pour tous les types de prestataires (CENTRE_DE_SOINS, MEDECIN_LIBERAL, etc.)
                    $existingPrestataire = Prestataire::where('user_id', $user->id)->first();
                    
                    if (!$existingPrestataire) {
                        Prestataire::create([
                            'user_id' => $user->id,
                            'type_prestataire' => $demandeAdhesion->type_demandeur,
                            'raison_sociale' => $demandeAdhesion->raison_sociale,
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
     * Handle the DemandesAdhesions "deleted" event.
     */
    public function deleted(DemandesAdhesions $demandeAdhesion): void
    {
        //
    }

    /**
     * Handle the DemandesAdhesions "restored" event.
     */
    public function restored(DemandesAdhesions $demandeAdhesion): void
    {
        //
    }

    /**
     * Handle the DemandesAdhesions "force deleted" event.
     */
    public function forceDeleted(DemandesAdhesions $demandeAdhesion): void
    {
        //
    }
}
