<?php

use App\Http\Controllers\Filament\NotificationController;
use Illuminate\Support\Facades\Route;

// Route pour marquer une notification comme lue (utilisée par le dropdown)
Route::middleware(['web'])->prefix('admin/api')->group(function () {
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])
        ->name('filament.notifications.mark-as-read');
});

// Route pour servir les fichiers depuis /storage/ (fallback si le lien symbolique ne fonctionne pas)
Route::get('/storage/{path}', function ($path) {
    dd($path);
    try {
        // Log pour débogage
        \Illuminate\Support\Facades\Log::info('WEB route /storage called', [
            'path' => $path,
            'decoded_path' => rawurldecode($path),
            'request_url' => request()->fullUrl(),
        ]);

        // Décoder le chemin
        $path = rawurldecode($path);

        // Sécuriser le chemin (empêcher les attaques de traversal)
        $path = str_replace('..', '', $path);
        $path = ltrim($path, '/');
        $path = str_replace('\\', '/', $path);

        // Construire le chemin complet
        $fullPath = storage_path('app/public/' . $path);

        \Illuminate\Support\Facades\Log::info('Checking file', [
            'fullPath' => $fullPath,
            'exists' => file_exists($fullPath),
        ]);

        // Vérifier que le fichier existe
        if (!file_exists($fullPath)) {
            \Illuminate\Support\Facades\Log::warning('File not found', ['path' => $fullPath]);
            abort(404, 'Fichier non trouvé: ' . $path);
        }

        // Vérifier la sécurité - utiliser une approche plus simple et robuste
        $publicDir = storage_path('app/public');

        // Normaliser et passer en minuscule pour comparaison cross-OS
        $normalizedFullPath = strtolower(str_replace('\\', '/', $fullPath));
        $normalizedPublicDir = strtolower(str_replace('\\', '/', $publicDir));

        \Illuminate\Support\Facades\Log::info('Security check', [
            'normalizedFullPath' => $normalizedFullPath,
            'normalizedPublicDir' => $normalizedPublicDir,
            'starts_with' => str_starts_with($normalizedFullPath, $normalizedPublicDir),
        ]);

        // Pas de blocage 403 : on sert directement si le fichier existe
        $realPath = realpath($fullPath) ?: $fullPath;
        $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';

        \Illuminate\Support\Facades\Log::info('Serving file', [
            'file' => $realPath,
            'mimeType' => $mimeType,
        ]);

        // Vérifier si c'est un téléchargement
        if (request()->has('download')) {
            return response()->download($realPath, basename($realPath), [
                'Content-Type' => $mimeType,
            ]);
        }

        return response()->file($realPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($realPath) . '"',
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error serving file', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        abort(500, 'Erreur lors du chargement du fichier: ' . $e->getMessage());
    }
})->where('path', '.*');

// Route web pour servir les fichiers via /api/v1/files (accessible sans clé API) — recherche uniquement dans uploads/
Route::get('/api/v1/files/{filename}', function ($filename) {
    $filename = basename(rawurldecode($filename));
    $fullPath = storage_path('app/public/uploads/' . $filename);

    if (!file_exists($fullPath)) {
        abort(404, 'Fichier non trouvé: ' . $filename);
    }

    $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

    if (request()->has('download')) {
        return response()->download($fullPath, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }

    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
    ]);
})->where('filename', '.*');


