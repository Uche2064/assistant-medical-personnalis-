<?php

namespace App\Filament\Resources\DemandeAdhesions\Schemas;

use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                // Pour les prestataires : seulement nom, email, contact, date de soumission
                // Pour les clients : toutes les infos (nom, prénoms, date de naissance, sexe, profession)
                TextEntry::make('user.personne.nom')
                    ->label('Nom')
                    ->placeholder('-')
                    ->visible(fn ($record) => $record->type_demandeur === TypeDemandeurEnum::PRESTATAIRE || $record->user->personne),
                TextEntry::make('user.prestataire.raison_sociale')
                    ->label('Raison sociale')
                    ->placeholder('-')
                    ->visible(fn ($record) => $record->type_demandeur === TypeDemandeurEnum::PRESTATAIRE && $record->user->prestataire),
                TextEntry::make('user.personne.prenoms')
                    ->label('Prénoms')
                    ->placeholder('-')
                    ->visible(fn ($record) => $record->type_demandeur === TypeDemandeurEnum::CLIENT && $record->user->personne),
                TextEntry::make('user.personne.date_naissance')
                    ->label('Date de naissance')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->visible(fn ($record) => $record->type_demandeur === TypeDemandeurEnum::CLIENT && $record->user->personne),
                TextEntry::make('user.personne.sexe')
                    ->label('Sexe')
                    ->badge()
                    ->placeholder('-')
                    ->visible(fn ($record) => $record->type_demandeur === TypeDemandeurEnum::CLIENT && $record->user->personne),
                TextEntry::make('user.personne.profession')
                    ->label('Profession')
                    ->placeholder('-')
                    ->visible(fn ($record) => $record->type_demandeur === TypeDemandeurEnum::CLIENT && $record->user->personne),
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
                    ->label('Réponses au questionnaire')
                    ->getStateUsing(function ($record) {
                        // Filtrer uniquement les réponses du demandeur
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
                            ->formatStateUsing(function ($state, $record) {
                                $question = $record->question ?? null;
                                $isFile = $question && $question->type_de_donnee === TypeDonneeEnum::FILE;

                                if ($isFile && $state) {
                                    // Extraire le chemin du fichier depuis l'URL
                                    $fileUrl = $state;
                                    $parsedUrl = parse_url($fileUrl);
                                    $path = $parsedUrl['path'] ?? '';

                                    // Extraire le chemin relatif depuis /storage/
                                    $relativePath = null;
                                    $fileName = null;

                                    // Essayer d'extraire le chemin relatif depuis /storage/
                                    if (str_contains($path, '/storage/')) {
                                        $pathParts = explode('/storage/', $path);
                                        if (isset($pathParts[1])) {
                                            $relativePath = $pathParts[1]; // demandes_adhesions/email_folder/filename
                                            $fileName = basename($relativePath); // filename
                                        }
                                    } elseif (str_contains($fileUrl, 'storage/')) {
                                        // Si l'URL contient directement "storage/" sans le slash initial
                                        $pathParts = explode('storage/', $fileUrl);
                                        if (isset($pathParts[1])) {
                                            // Nettoyer le chemin (enlever les paramètres de requête, etc.)
                                            $relativePath = explode('?', $pathParts[1])[0];
                                            $fileName = basename($relativePath);
                                        }
                                    } else {
                                        // Si c'est juste un nom de fichier, essayer de le trouver
                                        $fileName = basename($path);
                                    }

                                    // Utiliser directement l'URL stockée pour la visualisation (via le lien symbolique)
                                    // Et créer une route de téléchargement via /api/download/{filename}
                                    if ($relativePath) {
                                        $viewUrl = asset('storage/' . $relativePath);
                                        $downloadUrl = url('/api/download/' . rawurlencode($fileName) . '?download=1');
                                    } else {
                                        $viewUrl = $fileUrl;
                                        $downloadUrl = url('/api/download/' . rawurlencode($fileName) . '?download=1');
                                    }

                                    // Déterminer si c'est une image
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                    $isImage = in_array($extension, $imageExtensions);

                                    // Construire le HTML avec un meilleur espacement
                                    $html = '<div style="display: flex; flex-direction: column; gap: 0.75rem;">';
                                    $html .= '<div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">';

                                    if ($isImage) {
                                        $html .= '<a href="' . htmlspecialchars($viewUrl) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background-color: rgb(59 130 246); color: white; border-radius: 0.375rem; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: background-color 0.2s;">';
                                        $html .= '<svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>';
                                        $html .= '<span>Visualiser</span>';
                                        $html .= '</a>';
                                    }

                                    $html .= '<a href="' . htmlspecialchars($downloadUrl) . '" download="' . htmlspecialchars($fileName) . '" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background-color: rgb(34 197 94); color: white; border-radius: 0.375rem; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: background-color 0.2s;">';
                                    $html .= '<svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>';
                                    $html .= '<span>Télécharger</span>';
                                    $html .= '</a>';
                                    $html .= '</div>';
                                    $html .= '<div style="font-size: 0.75rem; color: rgb(107 114 128); padding: 0.25rem 0; word-break: break-all;">' . htmlspecialchars($fileName) . '</div>';

                                    if ($isImage) {
                                        $html .= '<div style="margin-top: 0.5rem;"><img src="' . htmlspecialchars($viewUrl) . '" alt="' . htmlspecialchars($fileName) . '" style="max-width: 20rem; max-height: 12rem; border-radius: 0.375rem; border: 1px solid rgb(229 231 235);" loading="lazy" onerror="this.style.display=\'none\';"></div>';
                                    }

                                    $html .= '</div>';

                                    return new HtmlString($html);
                                }

                                // Pour les autres types de réponses
                                if (is_bool($state)) {
                                    $colorClass = $state ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                    return new HtmlString('<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ' . $colorClass . '">' . ($state ? 'Oui' : 'Non') . '</span>');
                                }

                                if (is_numeric($state)) {
                                    return number_format($state, 0, ',', ' ');
                                }

                                if ($state instanceof \DateTime || $state instanceof \Carbon\Carbon) {
                                    return $state->format('d/m/Y');
                                }

                                return $state ?? '-';
                            })
                            ->html(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->reponsesQuestions()->where('user_id', $record->user_id)->count() > 0)
                    ->columnSpanFull(),
                // Bénéficiaires : seulement pour les clients
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
                    ->visible(fn ($record) => $record->type_demandeur === TypeDemandeurEnum::CLIENT && $record->assurePrincipal && $record->assurePrincipal->beneficiaires()->count() > 0)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
