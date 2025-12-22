<div class="space-y-4">
    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notification->est_lu): ?>
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                <?php else: ?>
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    <?php echo e($notification->titre); ?>

                </h3>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        <?php if($notification->type === 'info'): ?> bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                        <?php elseif($notification->type === 'success'): ?> bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        <?php elseif($notification->type === 'warning'): ?> bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        <?php elseif($notification->type === 'danger'): ?> bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        <?php else: ?> bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                        <?php endif; ?>">
                        <?php echo e($notification->type); ?>

                    </span>
                    <span class="ml-2 text-gray-500 dark:text-gray-400">
                        <?php echo e($notification->created_at->format('d/m/Y à H:i')); ?>

                    </span>
                </div>
                <div class="prose prose-sm max-w-none dark:prose-invert">
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
                        <?php echo e($notification->message); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notification->data && !empty($notification->data)): ?>
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-2">Données supplémentaires :</h4>
            <pre class="text-xs text-blue-800 dark:text-blue-300 overflow-x-auto"><?php echo e(json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php /**PATH G:\projects\amp\amp_backend\resources\views/filament/pages/notification-full-message.blade.php ENDPATH**/ ?>