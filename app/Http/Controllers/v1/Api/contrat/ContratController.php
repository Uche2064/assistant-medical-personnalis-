<?php

namespace App\Http\Controllers\v1\Api\contrat;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\contrat\StoreContratFormRequest;
use App\Http\Requests\contrat\UpdateContratFormRequest;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Récupération des filtres depuis la requête
        $type = $request->query('type'); // basic, standard, premium
        $min = $request->query('min');   // montant minimum
        $max = $request->query('max');   // montant maximum

        $query = Contrat::query();

        // Filtre par type de contrat
        if ($type) {
            $query->where('type_contrat', $type);
        }

        // Filtre par montant
        if ($min !== null) {
            $query->where('prime_standard', '>=', $min);
        }

        if ($max !== null) {
            $query->where('prime_standard', '<=', $max);
        }

        // Tu peux ajouter une pagination si tu veux
        $contrats = $query->with(['client', 'technicien'])->latest()->get();

        return ApiResponse::success($contrats, 'Liste des contrats récupérée avec succès');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContratFormRequest $request)
    {
        $validatedData = $request->validated();

        // Création du contrat
        $contrat = Contrat::create(
            [
                'type_contrat' => $validatedData['type_contrat'],
                'prime_standard' => $validatedData['prime_standard'],
                'technicien_id' => Auth::id() 
            ]
        );

        // Chargement des relations nécessaires
        $contrat->load(['technicien']);

        return ApiResponse::success($contrat, 'Contrat créé avec succès');
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContratFormRequest $request, string $id)
    {
        // Validate the request
        $validatedData = $request->validated();

        // Update the contrat
        $contrat = Contrat::find($id);
        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }
        $contrat->update($validatedData);

        return ApiResponse::success($contrat, 'Contrat mis à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the contrat
        $contrat = Contrat::find($id);        
        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }

        // Delete the contrat
        $contrat->delete();        

        return ApiResponse::success('Contrat supprimé avec succès');
    }
}
