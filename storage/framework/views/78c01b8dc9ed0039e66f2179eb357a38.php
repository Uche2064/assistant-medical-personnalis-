<?php
    $user = \Filament\Facades\Filament::auth()->user();

    if (!$user) {
        $notifications = collect([]);
        $unreadCount = 0;
    } else {
        $notifications = \App\Models\Notification::where('user_id', $user->id)
            ->where('est_lu', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = $notifications->count();
    }

    // Fonction helper pour générer l'URL de redirection
    $getNotificationUrl = function($notification) {
        $data = $notification->data ?? [];
        $typeNotification = $data['type_notification'] ?? null;

        switch ($typeNotification) {
            case 'nouvelle_demande_adhésion':
            case 'nouvelle_demande_prestataire':
                $demandeId = $data['demande_id'] ?? null;
                if ($demandeId) {
                    return url('/admin/demandes-adhesions/' . $demandeId);
                }
                return url('/admin/demandes-adhesions');

            case 'nouvelle_facture_technicien':
            case 'nouvelle_facture_medecin':
            case 'nouvelle_facture_comptable':
            case 'facture_validee_technicien':
            case 'facture_validee_medecin':
            case 'facture_autorisee_comptable':
            case 'facture_rejetee_technicien':
            case 'facture_rejetee_medecin':
            case 'facture_rejetee_comptable':
            case 'facture_remboursee':
                $factureId = $data['facture_id'] ?? null;
                if ($factureId) {
                    return url('/admin/factures/' . $factureId);
                }
                return url('/admin/factures');

            case 'nouveau_client_parraine':
                $clientId = $data['client_id'] ?? null;
                if ($clientId) {
                    return url('/admin/clients/' . $clientId);
                }
                return url('/admin/clients');

            case 'beneficiaire_ajoute':
            case 'beneficiaire_supprime':
                $clientId = $data['client_id'] ?? null;
                if ($clientId) {
                    return url('/admin/clients/' . $clientId);
                }
                return url('/admin/clients');

            default:
                return null;
        }
    };
?>

<!-- NOTIFICATIONS DROPDOWN START -->
<div class="fi-topbar-item" x-data="{ open: false }" style="display: flex !important; align-items: center; position: relative; visibility: visible !important;">
    <div class="relative">
        <button
            @click="open = !open"
            class="fi-topbar-item-button relative inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors"
            title="Notifications"
            type="button"
            style="display: inline-flex !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 10 !important;"
        >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unreadCount > 0): ?>
                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full min-w-[1.25rem]">
                    <?php echo e($unreadCount > 99 ? '99+' : $unreadCount); ?>

                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </button>

        <!-- Dropdown -->
        <div
            x-show="open"
            @click.away="open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
            style="display: none;"
        >
            <div class="p-2">
                <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Notifications
                    </h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unreadCount > 0): ?>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            <?php echo e($unreadCount); ?> non lue<?php echo e($unreadCount > 1 ? 's' : ''); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="max-h-96 overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $url = $getNotificationUrl($notification);
                            $data = $notification->data ?? [];
                        ?>
                        <a
                            href="<?php echo e($url ?? '#'); ?>"
                            <?php if($url): ?>
                                onclick="
                                    event.preventDefault();
                                    fetch('<?php echo e(route('filament.notifications.mark-as-read', $notification->id)); ?>', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json'
                                        }
                                    }).then(() => {
                                        window.location.href = '<?php echo e($url); ?>';
                                    });
                                "
                            <?php endif; ?>
                            class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors <?php echo e(!$notification->est_lu ? 'bg-blue-50 dark:bg-blue-900/20' : ''); ?>"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 mt-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$notification->est_lu): ?>
                                        <div class="w-2 h-2 bg-primary-600 rounded-full"></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 <?php echo e(!$notification->est_lu ? 'font-semibold' : ''); ?>">
                                        <?php echo e($notification->titre); ?>

                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                        <?php echo e($notification->message); ?>

                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        <?php echo e($notification->created_at->diffForHumans()); ?>

                                    </p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="px-3 py-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Aucune notification
                            </p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unreadCount > 0): ?>
                    <div class="border-t border-gray-200 dark:border-gray-700 px-3 py-2">
                        <a
                            href="<?php echo e(url('/admin/demandes-adhesions')); ?>"
                            class="text-xs text-primary-600 dark:text-primary-400 hover:underline"
                            @click="open = false"
                        >
                            Voir toutes les notifications
                        </a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php /**PATH G:\projects\amp\amp_backend\resources\views\filament\components\notifications-dropdown.blade.php ENDPATH**/ ?>