<?php

namespace App\Filament\Resources\Questions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuestionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('libelle')
                    ->label('Libellé'),

                TextEntry::make('type_de_donnee')
                    ->label('Type de donnée')
                    ->getStateUsing(fn ($record) => $record->type_de_donnee->getLabel()),

                TextEntry::make('destinataire')
                    ->label('Destinataire'),

                TextEntry::make('options')
                    ->label('Options')
                    ->getStateUsing(fn ($record) => $record->options ? json_encode($record->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Aucune'),

                IconEntry::make('est_obligatoire')
                    ->label('Obligatoire')
                    ->boolean(),

                IconEntry::make('est_active')
                    ->label('Active')
                    ->boolean(),

                TextEntry::make('creeePar.user.personne.nom_complet')
                    ->label('Créé par')
                    ->default('N/A'),

                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->columns(2);
    }
}

