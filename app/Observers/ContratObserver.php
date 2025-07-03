<?php

namespace App\Observers;

use App\Enums\RoleEnum;
use App\Models\Client;
use App\Models\Contrat;
use App\Models\User;
use App\Services\NotificationService;
use DateTime;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ContratObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Handle the contrats "created" event.
     */
    public function created(Contrat $contrat)
    {
        // récupérer le client qui pait le contrat
        $client = Client::find($contrat->client_id);
        // Mise à jour de la colonne prime dans la table client
        $client->update([
            'prime' => $contrat->prime,
            'date_paiement_prime'=> date('Y-m-d H:i:s')
        ]);

        // Récupérer l'utilisateur associé au client
        $user = $client->user;
        
        // Vérifier si l'utilisateur n'a pas déjà un mot de passe défini ou s'il doit changer son mot de passe
        if ($user->password === null || $user->must_change_password) {
            // Générer un nouveau mot de passe et l'attribuer à l'utilisateur
            $password = User::genererMotDePasse();
            $user->password = Hash::make($password);
            $user->est_actif = true;  // Activer l'utilisateur
            
            // Attribuer le rôle 'assuré' à l'utilisateur s'il ne l'a pas déjà
            if (!$user->hasRole(RoleEnum::ASSURE)) {
                $user->assignRole(RoleEnum::ASSURE);
            }
            
            $user->save();
           
            
            // Envoyer les identifiants de connexion avec les informations du contrat
            $this->notificationService->sendEmail($user->email, "Nouveau contrat signé", 'emails.contract_credentials', [
                'user' => $user,
                'contrat' => $contrat,
                'password' => $password
            ]);
            
            Log::info('Identifiants de connexion et informations du contrat envoyés à l\'utilisateur', [
                'user_id' => $user->id,
                'contrat_id' => $contrat->id,
                'numero_police' => $contrat->numero_police
            ]);
        }
    }

    /**
     * Handle the contrats "updated" event.
     */
    public function updated(Contrat $contrat): void
    {
        // Si la prime a été modifiée, mettre à jour la prime du client
        if ($contrat->isDirty('prime')) {
            $client = Client::find($contrat->client_id);
            $client->update([
                'prime' => $contrat->prime,
            ]);
        }
    }

    /**
     * Handle the contrats "deleted" event.
     */
    public function deleted(Contrat $contrat): void
    {
        //
    }

    /**
     * Handle the contrats "restored" event.
     */
    public function restored(Contrat $contrat): void
    {
        //
    }

    /**
     * Handle the contrats "force deleted" event.
     */
    public function forceDeleted(Contrat $contrat): void
    {
        //
    }
}
