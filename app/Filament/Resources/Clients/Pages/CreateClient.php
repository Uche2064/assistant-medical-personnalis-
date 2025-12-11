<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Enums\ClientTypeEnum;
use App\Enums\LienParenteEnum;
use App\Enums\RoleEnum;
use App\Filament\Resources\Clients\ClientResource;
use App\Helpers\ImageUploadHelper;
use App\Jobs\SendEmailJob;
use App\Models\Assure;
use App\Models\Client;
use App\Models\CommercialParrainageCode;
use App\Models\LienInvitation;
use App\Models\Personne;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $commercial = Filament::auth()->user() ?? Auth::guard('web')->user();

        if (!$commercial || !$commercial->hasRole(RoleEnum::COMMERCIAL->value)) {
            throw new \Exception('Seuls les commerciaux peuvent créer des clients.');
        }

        // Récupérer le code parrainage actuel
        $currentParrainageCode = CommercialParrainageCode::getCurrentCode($commercial->id);

        if (!$currentParrainageCode) {
            Notification::make()
                ->title('Erreur')
                ->body('Vous n\'avez pas de code de parrainage actif. Veuillez en générer un d\'abord.')
                ->danger()
                ->send();
            throw new \Exception('Aucun code de parrainage actif.');
        }

        // Ajouter le code parrainage aux données
        $data['code_parrainage'] = $currentParrainageCode->code_parrainage;
        $data['commercial_id'] = $commercial->id;
        $data['type_demandeur'] = 'client';

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $commercial = Filament::auth()->user() ?? Auth::guard('web')->user();

            // Vérifier si l'email existe déjà
            if (User::where('email', $data['email'])->exists()) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Cet email est déjà utilisé.')
                    ->danger()
                    ->send();
                throw new \Exception('Cet email est déjà utilisé.');
            }

            // Vérifier si le contact existe déjà
            if (User::where('contact', $data['contact'])->exists()) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Ce numéro de téléphone est déjà utilisé.')
                    ->danger()
                    ->send();
                throw new \Exception('Ce numéro de téléphone est déjà utilisé.');
            }

            // Générer un mot de passe automatique
            $motDePasseGenere = User::genererMotDePasse();

            // Gestion de l'upload de la photo (uniquement pour physique)
            $photoUrl = null;
            if (isset($data['photo']) && $data['type_client'] === ClientTypeEnum::PHYSIQUE->value) {
                $photoUrl = ImageUploadHelper::uploadImage($data['photo'], 'uploads', $data['email']);
            }

            // Créer la personne
            $personne = Personne::create([
                'nom' => $data['nom'] ?? null,
                'prenoms' => $data['prenoms'] ?? null,
                'date_naissance' => $data['date_naissance'] ?? null,
                'sexe' => $data['sexe'] ?? null,
                'profession' => $data['profession'] ?? null,
            ]);

            // Créer l'utilisateur
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($motDePasseGenere),
                'contact' => $data['contact'],
                'photo_url' => $photoUrl,
                'adresse' => $data['adresse'],
                'est_actif' => false,
                'mot_de_passe_a_changer' => true,
                'personne_id' => $personne->id,
            ]);

            // Créer le client avec commercial_id et code_parrainage
            $client = Client::create([
                'user_id' => $user->id,
                'type_client' => $data['type_client'],
                'code_parrainage' => $data['code_parrainage'],
                'commercial_id' => $commercial->id,
            ]);

            // Créer l'assuré si client physique
            if ($data['type_client'] === ClientTypeEnum::PHYSIQUE->value) {
                Assure::create([
                    'user_id' => $user->id,
                    'client_id' => $client->id,
                    'est_principal' => true,
                    'lien_parente' => LienParenteEnum::PRINCIPAL,
                    'assure_principal_id' => null,
                ]);
            } else {
                // Créer le lien d'invitation pour client moral
                LienInvitation::create([
                    'client_id' => $client->id,
                    'jeton' => LienInvitation::genererToken(),
                    'expire_a' => now()->addDays((int) env('TOKEN_LINK_EXPIRE_TIME_DAYS', 30)),
                ]);
            }

            // Assigner le rôle client
            $user->assignRole(RoleEnum::CLIENT->value);

            // Envoyer l'email avec les informations de connexion
            dispatch(new SendEmailJob(
                $user->email,
                'Votre compte SUNU Santé a été créé',
                'emails.compte_cree_par_commercial',
                [
                    'user' => $user,
                    'mot_de_passe' => $motDePasseGenere,
                    'commercial' => $commercial,
                    'type_client' => $data['type_client'],
                ]
            ));

            Notification::make()
                ->title('Client créé avec succès')
                ->body('Un email a été envoyé au client avec ses informations de connexion.')
                ->success()
                ->send();

            return $client;
        });
    }
}
