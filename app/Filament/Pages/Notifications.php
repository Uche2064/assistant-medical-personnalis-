<?php

namespace App\Filament\Pages;

use App\Models\Notification;
use Filament\Actions\Action as PageAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class Notifications extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bell';

    protected string $view = 'filament.pages.notifications';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?int $navigationSort = 100;

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::auth()->check() || Auth::check();
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Filament::auth()->user() ?? Auth::user();
        if (!$user) {
            return null;
        }

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('est_lu', false)
            ->count();

        return $unreadCount > 0 ? (string) $unreadCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function table(Table $table): Table
    {
        $user = Filament::auth()->user() ?? Auth::user();

        if (!$user) {
            return $table->query(Notification::query()->whereRaw('1 = 0'));
        }

        // Debug: Logger pour vérifier l'utilisateur connecté
        \Illuminate\Support\Facades\Log::info('Notifications - User connecté:', [
            'user_id' => $user->id,
            'email' => $user->email,
            'notifications_count' => Notification::where('user_id', $user->id)->count()
        ]);

        return $table
            ->query(
                Notification::query()
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                IconColumn::make('est_lu')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-bell')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($record) => $record->est_lu ? null : 'primary'),
                TextColumn::make('message')
                    ->label('Message')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'info' => 'info',
                        'success' => 'success',
                        'warning' => 'warning',
                        'danger' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
            ])
            ->filters([
                SelectFilter::make('est_lu')
                    ->label('Statut')
                    ->options([
                        '0' => 'Non lues',
                        '1' => 'Lues',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] !== null) {
                            return $query->where('est_lu', $data['value']);
                        }
                        return $query;
                    }),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(function () use ($user) {
                        if (!$user) {
                            return [];
                        }
                        return Notification::where('user_id', $user->id)
                            ->distinct()
                            ->pluck('type', 'type')
                            ->toArray();
                    }),
            ])
            ->actions([
                PageAction::make('markAsRead')
                    ->label('Marquer comme lue')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => !$record->est_lu)
                    ->action(function ($record) {
                        $record->markAsRead();
                        $this->dispatch('notification-updated');
                    }),
                PageAction::make('markAsUnread')
                    ->label('Marquer comme non lue')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->visible(fn ($record) => $record->est_lu)
                    ->action(function ($record) {
                        $record->update(['est_lu' => false]);
                        $this->dispatch('notification-updated');
                    }),
                PageAction::make('delete')
                    ->label('Supprimer')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->delete();
                        $this->dispatch('notification-updated');
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    PageAction::make('markAllAsRead')
                        ->label('Tout marquer comme lues')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->markAsRead();
                            $this->dispatch('notification-updated');
                        }),
                    DeleteBulkAction::make()
                        ->label('Supprimer'),
                ]),
            ])
            ->emptyStateHeading('Aucune notification')
            ->emptyStateDescription('Vous n\'avez aucune notification pour le moment.')
            ->emptyStateIcon('heroicon-o-bell');
    }

    protected function getHeaderActions(): array
    {
        $user = Filament::auth()->user() ?? Auth::user();

        if (!$user) {
            return [];
        }

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('est_lu', false)
            ->count();

        return [
            PageAction::make('markAllAsRead')
                ->label('Tout marquer comme lues')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible($unreadCount > 0)
                ->action(function () use ($user) {
                    if (!$user) {
                        return;
                    }
                    Notification::where('user_id', $user->id)
                        ->where('est_lu', false)
                        ->update(['est_lu' => true]);
                    $this->dispatch('notification-updated');
                }),
            PageAction::make('deleteRead')
                ->label('Supprimer les lues')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () use ($user) {
                    if (!$user) {
                        return;
                    }
                    Notification::where('user_id', $user->id)
                        ->where('est_lu', true)
                        ->delete();
                    $this->dispatch('notification-updated');
                }),
        ];
    }
}

