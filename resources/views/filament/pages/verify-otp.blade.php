<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Vérification du code</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Un code de vérification a été envoyé à <strong>{{ session('forgot_password_email') }}</strong>.
                Entrez le code à 6 chiffres pour continuer.
            </p>

            <form wire:submit="verifyOtp">
                {{ $this->form }}

                <div style="margin-top: 20px;" class="mt-6 flex justify-between items-center">
                    <a href="{{ route('filament.admin.pages.forgot-password') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                        Retour
                    </a>
                    <x-filament::button type="submit" color="primary">
                        Vérifier le code
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>

