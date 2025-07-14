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
}
