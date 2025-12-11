<?php

use App\Http\Controllers\Filament\NotificationController;
use Illuminate\Support\Facades\Route;

// Route pour marquer une notification comme lue (utilisée par le dropdown)
Route::middleware(['web'])->prefix('admin/api')->group(function () {
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])
        ->name('filament.notifications.mark-as-read');
});
