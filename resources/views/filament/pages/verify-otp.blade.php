<x-filament-panels::page>
    <div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Vérification du code</h2>

            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Un code de vérification a été envoyé à
                <strong>{{ session('forgot_password_email') }}</strong>.
                Entrez le code à 6 chiffres pour continuer.
            </p>

            <form wire:submit="verifyOtp">
                {{ $this->form }}

                <div
                    x-data="{
                        timer: @entangle('resendTimer'),
                        start() {
                            let interval = setInterval(() => {
                                if (this.timer > 0) {
                                    this.timer--
                                } else {
                                    clearInterval(interval)
                                }
                            }, 1000)
                        }
                    }"
                    x-init="start()"
                    style="display:flex; justify-content: space-between; align-items: center; margin-top: 20px;"
                >
                    <a href="{{ route('filament.admin.pages.forgot-password') }}"
                       class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                        Retour
                    </a>

                    <div>
                        <x-filament::button type="submit" color="primary">
                            Vérifier le code
                        </x-filament::button>

                        <x-filament::button
                            type="button"
                            color="gray"
                            wire:click="resendOtp"
                            x-bind:disabled="timer > 0"
                            style="cursor: pointer;"
                        >
                            <span x-show="timer === 0">
                                Renvoyer le code
                            </span>

                            <span x-show="timer > 0">
                                Renvoyer dans <span x-text="timer"></span>s
                            </span>
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
