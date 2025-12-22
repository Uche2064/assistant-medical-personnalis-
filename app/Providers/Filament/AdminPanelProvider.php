<?php

namespace App\Providers\Filament;

use App\Http\Middleware\FilamentAuthenticate;
use App\Http\Middleware\FilamentRoleMiddleware;
use App\Http\Middleware\RequirePasswordChange;
use App\Http\Middleware\SetFilamentLocale;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Provider pour le panel d'administration Filament
 *
 * Configure le panel admin avec authentification, middleware et ressources
 */
class AdminPanelProvider extends PanelProvider
{

    /**
     * Configure le panel d'administration
     *
     * @param Panel $panel
     * @return Panel
     */
    public function panel(Panel $panel): Panel
    {
        // Ajouter le lien "Mot de passe oublié" après le formulaire de login
        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
            function (): string {
                try {
                    $forgotPasswordUrl = \App\Filament\Pages\ForgotPassword::getUrl();
                } catch (\Exception $e) {
                    // Si la route n'existe pas encore, utiliser l'URL directe
                    $forgotPasswordUrl = url('/admin/forgot-password');
                }
                return Blade::render('
                    <div class="mt-4 text-center">
                        <a
                            href="' . $forgotPasswordUrl . '"
                            class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
                        >
                            Mot de passe oublié ?
                        </a>
                    </div>
                ');
            }
        );


        return $panel
            // Configuration de base du panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->profile()
            ->colors([
                'primary' => '#c7183e',
            ])
            ->brandName('SUNU Santé Admin')
            ->brandLogo(asset('sunu-logo.png'))
            ->brandLogoHeight('3rem')
            ->darkModeBrandLogo(asset('sunu-logo.png'))
            // Découverte automatique des ressources, pages et widgets
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
                    // Dashboard par défaut désactivé - chaque rôle a son propre dashboard
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            // Middleware standard Laravel
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            // Middleware d'authentification Filament (personnalisé pour exclure les pages de réinitialisation)
            ->authMiddleware([
                FilamentAuthenticate::class,
            ])
            // Middleware personnalisé pour vérifier les rôles internes (après authentification)
            ->middleware([
                SetFilamentLocale::class,
                RequirePasswordChange::class, // Vérifier si le mot de passe doit être changé
                FilamentRoleMiddleware::class,
            ], isPersistent: false)
            // Configuration de l'authentification
            ->authGuard('web')
            ->authPasswordBroker('users');
    }
}
