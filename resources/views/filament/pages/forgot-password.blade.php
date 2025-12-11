<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Mot de passe oublié</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Entrez votre adresse email pour recevoir un code de réinitialisation.
            </p>

            <form wire:submit="sendOtp">
                {{ $this->form }}

                <div style="margin-top: 20px;" class="mt-6 flex justify-between items-center">
                    <a href="{{ route('filament.admin.auth.login') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                        Retour à la connexion
                    </a>
                    <x-filament::button type="submit" color="primary">
                        Envoyer le code
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>

