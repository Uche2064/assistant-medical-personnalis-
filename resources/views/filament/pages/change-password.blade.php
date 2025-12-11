<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Changement de mot de passe obligatoire</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Pour des raisons de sécurité, vous devez changer votre mot de passe temporaire avant de continuer.
            </p>

            <form wire:submit="save">
                {{ $this->form }}

                <div style="margin-top: 20px;" class="mt-6 flex justify-end">
                    <x-filament::button type="submit" color="primary">
                        Changer le mot de passe
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>

