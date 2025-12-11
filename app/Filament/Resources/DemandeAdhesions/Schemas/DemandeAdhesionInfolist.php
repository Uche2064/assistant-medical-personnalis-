<?php

namespace App\Filament\Resources\DemandeAdhesions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Schema;

class DemandeAdhesionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type_demandeur')
                    ->label('Type de demandeur')
                    ->badge()
                    ->color(fn ($record) => match($record->type_demandeur->value) {
                        'client' => 'info',
                        'prestataire' => 'warning',
                        default => 'gray',
                    }),
                TextEntry::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn ($record) => $record->statut->getColor()),
                TextEntry::make('user.email')
                    ->label('Email du demandeur')
                    ->icon('heroicon-o-envelope'),
                TextEntry::make('user.contact')
                    ->label('Contact')
                    ->icon('heroicon-o-phone'),
                TextEntry::make('user.personne.nom')
                    ->label('Nom')
                    ->placeholder('-'),
                TextEntry::make('user.personne.prenoms')
                    ->label('Prénoms')
                    ->placeholder('-'),
                TextEntry::make('user.personne.date_naissance')
                    ->label('Date de naissance')
                    ->date('d/m/Y')
                    ->placeholder('-'),
                TextEntry::make('user.personne.sexe')
                    ->label('Sexe')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('user.personne.profession')
                    ->label('Profession')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Date de soumission')
                    ->dateTime('d/m/Y à H:i'),
                TextEntry::make('valider_a')
                    ->label('Date de validation')
                    ->dateTime('d/m/Y à H:i')
                    ->placeholder('-')
                    ->visible(fn ($record) => $record->statut->value === 'validee'),
                TextEntry::make('motif_rejet')
                    ->label('Motif de rejet')
                    ->placeholder('-')
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record->statut->value === 'rejetee' && $record->motif_rejet),
                RepeatableEntry::make('reponsesQuestions')
                    ->label('Réponses au questionnaire médical')
                    ->getStateUsing(function ($record) {
                        // Filtrer uniquement les réponses de l'assuré principal
                        return $record->reponsesQuestions()
                            ->where('user_id', $record->user_id)
                            ->with('question')
                            ->get();
                    })
                    ->schema([
                        TextEntry::make('question.libelle')
                            ->label('Question')
                            ->weight('bold'),
                        TextEntry::make('reponse')
                            ->label('Réponse')
                            ->formatStateUsing(function ($state) {
                                if (is_bool($state)) {
                                    return $state ? 'Oui' : 'Non';
                                }
                                if (is_numeric($state)) {
                                    return (string) $state;
                                }
                                if ($state instanceof \DateTime || $state instanceof \Carbon\Carbon) {
                                    return $state->format('d/m/Y');
                                }
                                return $state ?? '-';
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->reponsesQuestions()->where('user_id', $record->user_id)->count() > 0)
                    ->columnSpanFull(),
                RepeatableEntry::make('assurePrincipal.beneficiaires')
                    ->label('Bénéficiaires')
                    ->schema([
                        TextEntry::make('user.personne.nom')
                            ->label('Nom'),
                        TextEntry::make('user.personne.prenoms')
                            ->label('Prénoms'),
                        TextEntry::make('user.personne.date_naissance')
                            ->label('Date de naissance')
                            ->date('d/m/Y'),
                        TextEntry::make('user.personne.sexe')
                            ->label('Sexe')
                            ->badge(),
                        TextEntry::make('lien_parente')
                            ->label('Lien de parenté')
                            ->badge(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->assurePrincipal && $record->assurePrincipal->beneficiaires()->count() > 0)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
