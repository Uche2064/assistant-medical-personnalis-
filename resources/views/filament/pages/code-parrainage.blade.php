<x-filament-panels::page>
    @php
        $currentCode = $this->getCurrentCode();
        $history = $this->getHistory();
    @endphp

    <div class="space-y-8">
        {{-- Code actuel --}}
        <x-filament::section class="mb-8" style="margin-bottom: 20px;">
            <x-slot name="heading">
                Code Parrainage Actuel
            </x-slot>

            @if($currentCode)
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Code Parrainage</p>
                            <p class="text-2xl font-bold text-primary-600">{{ $currentCode->code_parrainage }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Date de création</p>
                            <p class="text-lg">{{ $currentCode->date_debut->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Date d'expiration</p>
                            <p class="text-lg">{{ $currentCode->date_expiration->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Jours restants</p>
                            <p class="text-lg font-semibold {{ $currentCode->isExpired() ? 'text-danger-600' : 'text-success-600' }}">
                                {{ $currentCode->isExpired() ? 'Expiré' : now()->diffInDays($currentCode->date_expiration, false) . ' jours' }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-gray-500">Aucun code parrainage actif pour le moment.</p>
            @endif
        </x-filament::section>

        {{-- Historique --}}
        <x-filament::section class="mt-8">
            <x-slot name="heading">
                Historique des Codes Parrainage
            </x-slot>

            @if($history->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date début</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date expiration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($history as $code)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $code['code_parrainage'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $code['date_debut'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $code['date_expiration'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $code['est_actif'] && !$code['est_expire'] ? 'bg-success-100 text-success-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $code['est_actif'] && !$code['est_expire'] ? 'Actif' : ($code['est_expire'] ? 'Expiré' : 'Inactif') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $code['created_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500">Aucun historique disponible.</p>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
