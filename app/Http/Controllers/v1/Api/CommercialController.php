<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommercialController extends Controller
{
    /**
     * Dashboard du commercial
     */
    public function dashboard()
    {
        $user = Auth::user();
        $commercial = $user->personnel;

        if (!$commercial || !$commercial->isCommercial()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        // Statistiques du commercial
        $stats = [
            'total_prospects' => $commercial->clients()->count(),
            'prospects_convertis' => $commercial->clients()->where('statut', 'client')->count(),
            'prospects_en_cours' => $commercial->clients()->where('statut', 'prospect')->count(),
            'codes_parrainage_generes' => $commercial->clients()->whereNotNull('code_parainage')->count(),
            'commission_totale' => $commercial->clients()->sum('prime'),
        ];

        // Prospects récents
        $prospectsRecents = $commercial->clients()
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        return ApiResponse::success([
            'commercial' => [
                'id' => $commercial->id,
                'nom' => $commercial->nom,
                'prenoms' => $commercial->prenoms,
                'code_parainage' => $commercial->code_parainage,
                'email' => $commercial->email,
            ],
            'statistiques' => $stats,
            'prospects_recents' => $prospectsRecents,
        ], 'Dashboard commercial récupéré avec succès');
    }

    /**
     * Liste des prospects du commercial
     */
    public function prospects(Request $request)
    {
        $user = Auth::user();
        $commercial = $user->personnel;

        if (!$commercial || !$commercial->isCommercial()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $query = $commercial->clients()->with('user');

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('type_client')) {
            $query->where('type_client', $request->type_client);
        }

        if ($request->has('profession')) {
            $query->where('profession', 'like', '%' . $request->profession . '%');
        }

        $prospects = $query->paginate($request->get('per_page', 10));

        return ApiResponse::success($prospects, 'Liste des prospects récupérée avec succès');
    }

    /**
     * Générer un nouveau code de parrainage
     */
    public function genererCodeParrainage()
    {
        $user = Auth::user();
        $commercial = $user->personnel;

        if (!$commercial || !$commercial->isCommercial()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $code = Personnel::genererCodeParainage();
        $commercial->update(['code_parainage' => $code]);

        return ApiResponse::success([
            'code_parainage' => $code
        ], 'Code de parrainage généré avec succès');
    }

    /**
     * Statistiques détaillées du commercial
     */
    public function statistiques(Request $request)
    {
        $user = Auth::user();
        $commercial = $user->personnel;

        if (!$commercial || !$commercial->isCommercial()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $periode = $request->get('periode', 'mois'); // jour, semaine, mois, annee

        $query = $commercial->clients();

        // Filtre par période
        switch ($periode) {
            case 'jour':
                $query->whereDate('created_at', today());
                break;
            case 'semaine':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'mois':
                $query->whereMonth('created_at', now()->month);
                break;
            case 'annee':
                $query->whereYear('created_at', now()->year);
                break;
        }

        $stats = [
            'total_prospects' => $query->count(),
            'prospects_convertis' => $query->where('statut', 'client')->count(),
            'taux_conversion' => $query->count() > 0 ? 
                round(($query->where('statut', 'client')->count() / $query->count()) * 100, 2) : 0,
            'commission_totale' => $query->sum('prime'),
            'moyenne_prime' => $query->avg('prime'),
        ];

        return ApiResponse::success($stats, 'Statistiques récupérées avec succès');
    }

    /**
     * Commissions et paiements du commercial
     */
    public function commissions(Request $request)
    {
        $user = Auth::user();
        $commercial = $user->personnel;

        if (!$commercial || !$commercial->isCommercial()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $query = $commercial->clients()
            ->where('statut', 'client')
            ->whereNotNull('prime');

        // Filtre par période
        if ($request->has('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $commissions = $query->paginate($request->get('per_page', 10));

        $totalCommission = $query->sum('prime');

        return ApiResponse::success([
            'commissions' => $commissions,
            'total_commission' => $totalCommission,
        ], 'Commissions récupérées avec succès');
    }

    /**
     * Détails d'un prospect spécifique
     */
    public function showProspect($id)
    {
        $user = Auth::user();
        $commercial = $user->personnel;

        if (!$commercial || !$commercial->isCommercial()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $prospect = $commercial->clients()
            ->with(['user', 'assures'])
            ->find($id);

        if (!$prospect) {
            return ApiResponse::error('Prospect non trouvé', 404);
        }

        return ApiResponse::success($prospect, 'Prospect récupéré avec succès');
    }

    /**
     * Mettre à jour les informations d'un prospect
     */
    public function updateProspect(Request $request, $id)
    {
        $user = Auth::user();
        $commercial = $user->personnel;

        if (!$commercial || !$commercial->isCommercial()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $prospect = $commercial->clients()->find($id);

        if (!$prospect) {
            return ApiResponse::error('Prospect non trouvé', 404);
        }

        $validated = $request->validate([
            'profession' => 'sometimes|string|max:255',
            'code_parainage' => 'sometimes|string|max:50',
            'notes' => 'sometimes|string',
        ]);

        $prospect->update($validated);

        return ApiResponse::success($prospect, 'Prospect mis à jour avec succès');
    }
} 