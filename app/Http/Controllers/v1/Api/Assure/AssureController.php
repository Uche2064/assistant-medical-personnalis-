<?php

namespace App\Http\Controllers\v1\Api\Assure;

use App\Enums\LienParenteEnum;
use App\Enums\StatutAssureEnum;
use App\Enums\SexeEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Assure;
use Illuminate\Http\Request;

class AssureController extends Controller
{
    /**
     * Statistiques des assurés
     */
    public function assureStats()
    {
        $stats = [
            'total' => Assure::count(),
            
            'principaux' => Assure::where('est_principal', true)->count(),
            
            'beneficiaires' => Assure::where('est_principal', false)->count(),
            
            'actifs' => Assure::where('statut', 'actif')->count(),
            
            'inactifs' => Assure::where('statut', 'inactif')->count(),
            
            'suspendus' => Assure::where('statut', 'suspendu')->count(),
            
            'repartition_par_sexe' => Assure::selectRaw('sexe, COUNT(*) as count')
                ->groupBy('sexe')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->sexe ?? 'Non spécifié' => $item->count];
                }),
            
            'repartition_par_lien_parente' => Assure::selectRaw('lien_parente, COUNT(*) as count')
                ->groupBy('lien_parente')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->lien_parente ?? 'Non spécifié' => $item->count];
                }),
            
            'repartition_par_statut' => Assure::selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->statut ?? 'Non spécifié' => $item->count];
                }),
            
            'repartition_principaux_beneficiaires' => [
                'principaux' => Assure::where('est_principal', true)->count(),
                'beneficiaires' => Assure::where('est_principal', false)->count(),
            ],
            
            'repartition_par_contrat' => Assure::selectRaw('contrat_id, COUNT(*) as count')
                ->whereNotNull('contrat_id')
                ->groupBy('contrat_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return ["Contrat {$item->contrat_id}" => $item->count];
                }),
        ];

        return ApiResponse::success($stats, 'Statistiques des assurés récupérées avec succès');
    }
} 