<?php

namespace App\Filament\Resources\DemandeAdhesions\Pages;

use App\Enums\RoleEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Filament\Resources\DemandeAdhesions\DemandeAdhesionResource;
use App\Models\DemandeAdhesion;
use App\Services\DemandeAdhesionService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Group;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViewDemandeAdhesion extends ViewRecord
{
    protected static string $resource = DemandeAdhesionResource::class;

    protected DemandeAdhesionService $demandeAdhesionService;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->demandeAdhesionService = app(DemandeAdhesionService::class);

        // Charger toutes les relations nécessaires
        $this->record->load([
            'user.personne',
            'user.client',
            'assurePrincipal.user.personne',
            'assurePrincipal.beneficiaires.user.personne',
            'reponsesQuestions.question',
            'validePar',
        ]);
    }

    protected function getHeaderActions(): array
    {
        $user = Filament::auth()->user() ?? Auth::user();
        $demande = $this->record;
        $canValidate = $user && (
            $user->hasRole(RoleEnum::TECHNICIEN->value) ||
            $user->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value)
        ) && $demande->statut === StatutDemandeAdhesionEnum::EN_ATTENTE;

        $canReject = $canValidate;
        $canPropose = $user && $user->hasRole(RoleEnum::TECHNICIEN->value) &&
                     $demande->statut === StatutDemandeAdhesionEnum::EN_ATTENTE;

        return [
            Action::make('valider')
                ->label('Valider la demande')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible($canValidate)
                ->form([
                    Textarea::make('motif_validation')
                        ->label('Motif de validation')
                        ->placeholder('Optionnel'),
                    Textarea::make('notes_techniques')
                        ->label('Notes techniques')
                        ->placeholder('Optionnel'),
                ])
                ->action(function (array $data) use ($user, $demande) {
                    try {
                        DB::beginTransaction();
                        $this->demandeAdhesionService->validerDemande(
                            $demande,
                            $user->personnel ?? $user,
                            $data['motif_validation'] ?? null,
                            $data['notes_techniques'] ?? null
                        );
                        DB::commit();

                        Notification::make()
                            ->success()
                            ->title('Demande validée')
                            ->body('La demande d\'adhésion a été validée avec succès.')
                            ->send();

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $demande]));
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Notification::make()
                            ->danger()
                            ->title('Erreur')
                            ->body('Une erreur est survenue lors de la validation : ' . $e->getMessage())
                            ->send();
                    }
                }),

            Action::make('rejeter')
                ->label('Rejeter la demande')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible($canReject)
                ->form([
                    Textarea::make('motif_rejet')
                        ->label('Motif de rejet')
                        ->required()
                        ->placeholder('Veuillez indiquer le motif de rejet'),
                    Textarea::make('notes_techniques')
                        ->label('Notes techniques')
                        ->placeholder('Optionnel'),
                ])
                ->action(function (array $data) use ($user, $demande) {
                    try {
                        DB::beginTransaction();
                        $this->demandeAdhesionService->rejeterDemande(
                            $demande,
                            $user->personnel ?? $user,
                            $data['motif_rejet'],
                            $data['notes_techniques'] ?? null
                        );
                        DB::commit();

                        Notification::make()
                            ->success()
                            ->title('Demande rejetée')
                            ->body('La demande d\'adhésion a été rejetée.')
                            ->send();

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $demande]));
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Notification::make()
                            ->danger()
                            ->title('Erreur')
                            ->body('Une erreur est survenue lors du rejet : ' . $e->getMessage())
                            ->send();
                    }
                }),

            Action::make('proposer_contrat')
                ->label('Proposer un contrat')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->visible($canPropose)
                ->form([
                    Select::make('contrat_id')
                        ->label('Type de contrat')
                        ->options(function () {
                            return \App\Models\TypeContrat::where('est_actif', true)
                                ->pluck('libelle', 'id')
                                ->toArray();
                        })
                        ->required()
                        ->searchable()
                        ->live(),

                    \Filament\Forms\Components\Placeholder::make('garanties_info')
                        ->label('Catégories de garantie et garanties')
                        ->content(function ($get) {
                            $contratId = $get('contrat_id');
                            if (!$contratId) {
                                return new \Illuminate\Support\HtmlString(
                                    '<div style="padding: 1rem; color: rgb(75 85 99);">Sélectionnez un contrat pour voir les catégories de garantie.</div>'
                                );
                            }

                            $contrat = \App\Models\TypeContrat::with(['categoriesGaranties.garanties' => function ($query) {
                                $query->where('est_active', true);
                            }])->find($contratId);

                            if (!$contrat || $contrat->categoriesGaranties->isEmpty()) {
                                return new \Illuminate\Support\HtmlString(
                                    '<div style="padding: 1rem; color: rgb(75 85 99);">Aucune catégorie de garantie associée à ce contrat.</div>'
                                );
                            }

                            $html = '<div style="margin-top: 1rem;" x-data="{ openCategories: {} }">';
                            $categorieIndex = 0;
                            foreach ($contrat->categoriesGaranties as $categorie) {
                                if ($categorie->garanties->isNotEmpty()) {
                                    $categorieKey = 'cat_' . $categorieIndex;
                                    $html .= '<div style="margin-bottom: 1.5rem; padding: 1rem; border: 1px solid rgb(229 231 235); border-radius: 0.5rem; background-color: rgb(249 250 251);">';

                                    // En-tête avec bouton collapse
                                    $html .= '<div style="display: flex; align-items: center; justify-content: space-between; cursor: pointer; margin-bottom: 1rem;" @click="openCategories[\'' . $categorieKey . '\'] = !openCategories[\'' . $categorieKey . '\']">
                                        <div style="flex: 1;">
                                            <h4 style="font-weight: 600; font-size: 1.125rem; margin-bottom: 0.5rem; color: rgb(17 24 39);">' . htmlspecialchars($categorie->libelle) . '</h4>';
                                    if ($categorie->description) {
                                        $html .= '<p style="font-size: 0.875rem; color: rgb(107 114 128); font-style: italic;">' . htmlspecialchars($categorie->description) . '</p>';
                                    }
                                    $html .= '</div>
                                        <div style="margin-left: 1rem;">
                                            <svg style="width: 1rem; height: 1rem; color: rgb(107 114 128); transition: transform 0.2s;"
                                                 :style="{ transform: openCategories[\'' . $categorieKey . '\'] ? \'rotate(180deg)\' : \'rotate(0deg)\' }"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>';

                                    // Contenu collapsible
                                    $html .= '<div x-show="openCategories[\'' . $categorieKey . '\'] !== false"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                                             x-transition:enter-end="opacity-100 transform translate-y-0"
                                             x-transition:leave="transition ease-in duration-150"
                                             x-transition:leave-start="opacity-100 transform translate-y-0"
                                             x-transition:leave-end="opacity-0 transform -translate-y-2"
                                             style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 0.75rem;">';

                                    foreach ($categorie->garanties as $garantie) {
                                        $html .= '<div style="padding: 0.75rem; background-color: white; border: 1px solid rgb(229 231 235); border-radius: 0.375rem;">
                                            <div style="font-weight: 500; margin-bottom: 0.75rem; color: rgb(17 24 39);">' . htmlspecialchars($garantie->libelle) . '</div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background-color: rgb(240 253 244); border: 1px solid rgb(187 247 208); border-radius: 0.25rem; margin-bottom: 0.5rem;">
                                                <span style="font-size: 0.75rem; font-weight: 500; color: rgb(22 101 52); text-transform: uppercase;">Plafond</span>
                                                <span style="font-weight: 600; color: rgb(20 83 45);">' . number_format($garantie->plafond, 0, ',', ' ') . ' FCFA</span>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background-color: rgb(239 246 255); border: 1px solid rgb(191 219 254); border-radius: 0.25rem;">
                                                <span style="font-size: 0.75rem; font-weight: 500; color: rgb(30 64 175); text-transform: uppercase;">Prix Standard</span>
                                                <span style="font-weight: 600; color: rgb(30 58 138);">' . number_format($garantie->prix_standard, 0, ',', ' ') . ' FCFA</span>
                                            </div>
                                        </div>';
                                    }

                                    $html .= '</div></div>';
                                    $categorieIndex++;
                                }
                            }
                            $html .= '</div>';

                            return new \Illuminate\Support\HtmlString($html);
                        })
                        ->visible(fn ($get) => !empty($get('contrat_id'))),

                    Textarea::make('commentaires_technicien')
                        ->label('Commentaires')
                        ->placeholder('Optionnel')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) use ($user, $demande) {
                    try {
                        DB::beginTransaction();

                        $technicien = $user->personnel ?? $user;

                        // Créer la proposition de contrat
                        $propositionContrat = \App\Models\PropositionContrat::create([
                            'demande_adhesion_id' => $demande->id,
                            'contrat_id' => $data['contrat_id'],
                            'commentaires_technicien' => $data['commentaires_technicien'] ?? null,
                            'technicien_id' => $technicien->id,
                            'statut' => \App\Enums\StatutPropositionContratEnum::PROPOSEE->value,
                        ]);

                        // Mettre à jour le statut de la demande
                        $demande->update([
                            'statut' => \App\Enums\StatutDemandeAdhesionEnum::PROPOSEE->value,
                        ]);

                        // Notifier le client
                        $notificationService = app(\App\Services\NotificationService::class);

                        $notificationService->createNotification(
                            $demande->user->id,
                            'Proposition de contrat reçue',
                            "Un technicien a analysé votre demande et vous propose un contrat.",
                            'contrat_propose',
                            [
                                'demande_id' => $demande->id,
                                'proposition_id' => $propositionContrat->id,
                                'contrat_id' => $propositionContrat->contrat_id,
                                'libelle' => $propositionContrat->contrat->libelle,
                                'prime_standard' => $propositionContrat->contrat->prime_standard,
                                'commentaires_technicien' => $propositionContrat->commentaires_technicien,
                                'propose_par' => $technicien->nom . ' ' . ($technicien->prenoms ?? ''),
                                'date_proposition' => now()->format('d/m/Y à H:i'),
                                'type_notification' => 'contrat_propose'
                            ]
                        );

                        // Envoyer un email au client
                        try {
                            \App\Jobs\SendEmailJob::dispatch(
                                $demande->user->email,
                                'Proposition de contrat SUNU Santé',
                                'emails.proposition_contrat',
                                [
                                    'user' => $demande->user,
                                    'proposition' => $propositionContrat,
                                    'demande' => $demande,
                                    'contrat' => $propositionContrat->contrat,
                                    'technicien' => $technicien,
                                ]
                            );
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::warning('Erreur envoi email proposition contrat: ' . $e->getMessage());
                        }

                        DB::commit();

                        Notification::make()
                            ->success()
                            ->title('Contrat proposé')
                            ->body('Le contrat a été proposé avec succès. Le client a été notifié par email et notification.')
                            ->send();

                        $this->redirect($this->getResource()::getUrl('view', ['record' => $demande]));
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Notification::make()
                            ->danger()
                            ->title('Erreur')
                            ->body('Une erreur est survenue : ' . $e->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\DemandeAdhesionStatsWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
}
