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
                        ->searchable(),
                    Textarea::make('commentaires_technicien')
                        ->label('Commentaires')
                        ->placeholder('Optionnel'),
                ])
                ->action(function (array $data) use ($user, $demande) {
                    try {
                        DB::beginTransaction();
                        // TODO: Implémenter la logique de proposition de contrat
                        DB::commit();

                        Notification::make()
                            ->success()
                            ->title('Contrat proposé')
                            ->body('Le contrat a été proposé avec succès.')
                            ->send();
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
