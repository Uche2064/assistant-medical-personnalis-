<?php

namespace App\Http\Controllers\v1\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Récupérer les notifications de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Notification::where('user_id', $user->id);

        // Filtrage par type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filtrage par statut (lu/non lu)
        if ($request->filled('lu')) {
            $isRead = filter_var($request->input('lu'), FILTER_VALIDATE_BOOLEAN);
            $query->where('lu', $isRead);
        }

        // Tri par date de création (plus récentes en premier)
        $query->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $request->input('per_page', 10);
        $notifications = $query->paginate($perPage);

        // Statistiques
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->where('lu', false)->count(),
            'read' => $user->notifications()->where('lu', true)->count(),
        ];

        return ApiResponse::success([
            'notifications' => $notifications,
            'statistiques' => $stats
        ], 'Notifications récupérées avec succès.');
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return ApiResponse::error('Notification non trouvée', 404);
        }

        $notification->markAsRead();

        return ApiResponse::success($notification, 'Notification marquée comme lue.');
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        $updated = Notification::where('user_id', $user->id)
            ->where('lu', false)
            ->update(['lu' => true]);

        return ApiResponse::success([
            'notifications_marquees' => $updated
        ], "{$updated} notification(s) marquée(s) comme lue(s).");
    }

    /**
     * Marquer une notification comme non lue
     */
    public function markAsUnread($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return ApiResponse::error('Notification non trouvée', 404);
        }

        $notification->markAsUnread();

        return ApiResponse::success($notification, 'Notification marquée comme non lue.');
    }

    /**
     * Supprimer une notification
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return ApiResponse::error('Notification non trouvée', 404);
        }

        $notification->delete();

        return ApiResponse::success(null, 'Notification supprimée avec succès.');
    }

    /**
     * Supprimer toutes les notifications lues
     */
    public function destroyRead()
    {
        $user = Auth::user();
        
        $deleted = Notification::where('user_id', $user->id)
            ->where('lu', true)
            ->delete();

        return ApiResponse::success([
            'notifications_supprimees' => $deleted
        ], "{$deleted} notification(s) supprimée(s).");
    }

    /**
     * Statistiques des notifications
     */
    public function stats()
    {
        $user = Auth::user();
        
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->where('lu', false)->count(),
            'read' => $user->notifications()->where('lu', true)->count(),
            'par_type' => $user->notifications()
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'recentes' => $user->notifications()
                ->where('lu', false)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'titre', 'message', 'type', 'created_at'])
        ];

        return ApiResponse::success($stats, 'Statistiques des notifications récupérées.');
    }
} 