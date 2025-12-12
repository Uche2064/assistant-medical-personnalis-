<?php

namespace App\Filament\Resources\Assures\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Schema;

class AssureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('est_principal')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $state ? 'Assuré Principal' : 'Bénéficiaire')
                    ->color(fn ($state) => $state ? 'success' : 'info'),

                TextEntry::make('user.email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope'),

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
                    ->formatStateUsing(fn ($state) => $state === 'M' ? 'Masculin' : ($state === 'F' ? 'Féminin' : '-'))
                    ->color(fn ($state) => $state === 'M' ? 'primary' : 'pink'),

                TextEntry::make('user.personne.profession')
                    ->label('Profession')
                    ->placeholder('-'),

                TextEntry::make('user.contact')
                    ->label('Contact')
                    ->icon('heroicon-o-phone')
                    ->placeholder('-'),

                TextEntry::make('lien_parente')
                    ->label('Lien de parenté')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'N/A')
                    ->visible(fn ($record) => !$record->est_principal),

                TextEntry::make('client.user.email')
                    ->label('Email du client')
                    ->icon('heroicon-o-building-office')
                    ->placeholder('-'),

                // Si c'est un bénéficiaire, afficher l'assuré principal
                TextEntry::make('assurePrincipal.user.email')
                    ->label('Email de l\'assuré principal')
                    ->icon('heroicon-o-envelope')
                    ->visible(fn ($record) => !$record->est_principal && $record->assurePrincipal)
                    ->placeholder('-'),

                TextEntry::make('assurePrincipal.user.personne.nom')
                    ->label('Nom de l\'assuré principal')
                    ->visible(fn ($record) => !$record->est_principal && $record->assurePrincipal)
                    ->placeholder('-'),

                TextEntry::make('assurePrincipal.user.personne.prenoms')
                    ->label('Prénoms de l\'assuré principal')
                    ->visible(fn ($record) => !$record->est_principal && $record->assurePrincipal)
                    ->placeholder('-'),

                TextEntry::make('assurePrincipal.user.personne.date_naissance')
                    ->label('Date de naissance de l\'assuré principal')
                    ->date('d/m/Y')
                    ->visible(fn ($record) => !$record->est_principal && $record->assurePrincipal)
                    ->placeholder('-'),

                TextEntry::make('assurePrincipal.user.personne.sexe')
                    ->label('Sexe de l\'assuré principal')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'M' ? 'Masculin' : ($state === 'F' ? 'Féminin' : '-'))
                    ->color(fn ($state) => $state === 'M' ? 'primary' : 'pink')
                    ->visible(fn ($record) => !$record->est_principal && $record->assurePrincipal)
                    ->placeholder('-'),

                TextEntry::make('assurePrincipal.user.contact')
                    ->label('Contact de l\'assuré principal')
                    ->icon('heroicon-o-phone')
                    ->visible(fn ($record) => !$record->est_principal && $record->assurePrincipal)
                    ->placeholder('-'),

                // Si c'est un assuré principal, afficher ses bénéficiaires
                RepeatableEntry::make('beneficiaires')
                    ->label('Bénéficiaires')
                    ->schema([
                        TextEntry::make('user.email')
                            ->label('Email'),
                        TextEntry::make('user.personne.nom')
                            ->label('Nom'),
                        TextEntry::make('user.personne.prenoms')
                            ->label('Prénoms'),
                        TextEntry::make('user.personne.date_naissance')
                            ->label('Date de naissance')
                            ->date('d/m/Y'),
                        TextEntry::make('user.personne.sexe')
                            ->label('Sexe')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state === 'M' ? 'Masculin' : ($state === 'F' ? 'Féminin' : '-'))
                            ->color(fn ($state) => $state === 'M' ? 'primary' : 'pink'),
                        TextEntry::make('lien_parente')
                            ->label('Lien de parenté')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'N/A'),
                        TextEntry::make('user.contact')
                            ->label('Contact'),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record->est_principal && $record->beneficiaires()->count() > 0)
                    ->columnSpanFull(),

                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y à H:i')
                    ->icon('heroicon-o-calendar'),
            ])
            ->columns(2);
    }
}

