<?php

namespace App\Http\Controllers\v1\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\GestionnaireFormRequest;
use App\Models\Gestionnaire;
use Illuminate\Http\Request;

class GestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gestionnaires = Gestionnaire::with('compagnie')->get();

        if($gestionnaires->isEmpty()) {
            return ApiResponse::success($gestionnaires, 'Aucun gestionnaire trouvé');
        }
        return ApiResponse::success($gestionnaires, 'Liste des gestionnaires récupérée avec succès');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GestionnaireFormRequest $request)
    {
        $data = $request->validated();
        $gestionnaire = Gestionnaire::create($data);
        return ApiResponse::success($gestionnaire, 'Gestionnaire créé avec succès');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
