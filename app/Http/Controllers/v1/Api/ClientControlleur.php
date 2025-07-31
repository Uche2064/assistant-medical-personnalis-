<?php

namespace App\Http\Controllers\v1\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientUpdateFormRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientControlleur extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('user', "assures");

        // Filtering by type_client if present
        if ($request->has('type_client')) {
            $query->where('type_client', $request->input('type_client'));
        }

        // Filtering by profession
        if ($request->has('profession')) {
            $query->where('profession', 'like', '%' . $request->input('profession') . '%');
        }

        // Optional: Filter by prime > 0 or date_paiement_prime range, etc.
        if ($request->has('prime_min')) {
            $query->where('prime', '>=', $request->input('prime_min'));
        }

        if ($request->has('date_paiement_prime')) {
            $query->whereDate('date_paiement_prime', $request->input('date_paiement_prime'));
        }

        // Paginate (default: 15 per page)
        $clients = $query->paginate($request->get('per_page', 10));

        return ApiResponse::success($clients, "Liste des clients récupérés");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = Client::with('user', 'assures')->find($id);
        if(!(bool)$client) {
            return ApiResponse::error('Client non enregistré', 404);
        }
        return ApiResponse::success($client, "Client récupéré avec succès");
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(ClientUpdateFormRequest $request, string $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validated();

        $client->update($validated);

        return ApiResponse::success($client, "Client mis à jour avec succès");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $client = Client::with('user', 'assures')->find($id);
        if(!(bool)$client) {
            return ApiResponse::error('Client non enregistré', 404);
        }
        $client->delete();

        return ApiResponse::success($client, "Client supprimé avec succès");
    }

    /**
     * Statistiques des clients
     */
    public function clientStats()
    {
        $stats = [
            'total' => Client::count(),
            
            'prospects' => Client::where('statut', 'prospect')->count(),
            
            'clients' => Client::where('statut', 'client')->count(),
            
            'assures' => Client::where('statut', 'assure')->count(),
            
            'physiques' => Client::where('type_client', 'physique')->count(),
            
            'moraux' => Client::where('type_client', 'moral')->count(),
            
            'repartition_par_sexe' => Client::selectRaw('sexe, COUNT(*) as count')
                ->groupBy('sexe')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->sexe ?? 'Non spécifié' => $item->count];
                }),
            
            'repartition_par_profession' => Client::selectRaw('profession, COUNT(*) as count')
                ->whereNotNull('profession')
                ->groupBy('profession')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->profession => $item->count];
                }),
            
            'repartition_statut_par_type' => Client::selectRaw('type_client, statut, COUNT(*) as count')
                ->groupBy('type_client', 'statut')
                ->get()
                ->groupBy('type_client')
                ->map(function ($group) {
                    return $group->mapWithKeys(function ($item) {
                        return [$item->statut => $item->count];
                    });
                }),
        ];

        return ApiResponse::success($stats, 'Statistiques des clients récupérées avec succès');
    }
}
