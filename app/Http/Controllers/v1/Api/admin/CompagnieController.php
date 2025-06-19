<?php

namespace App\Http\Controllers\v1\Api\admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\CompagnieFormRequest;
use App\Http\Requests\admin\CompagnieUpdateFormRequest;
use App\Models\Compagnie;
use Illuminate\Http\Request;

class CompagnieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $compagnies = Compagnie::with('gestionnaires')->get();
        if($compagnies->isEmpty()) {
            return ApiResponse::success($compagnies, 'Aucune compagnie trouvée');
        }
        return ApiResponse::success($compagnies, 'Liste des compagnies récupérée avec succès');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompagnieFormRequest $request)
    {
        $data = $request->validated();
        $compagnie = Compagnie::create($data);
        return ApiResponse::success($compagnie, 'Compagnie créée avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $compagnie = Compagnie::with('gestionnaires')->find($id);
        if($compagnie) {
            return ApiResponse::success($compagnie, 'Compagnie récupérée avec succès');
        }
        return ApiResponse::error('Compagnie non trouvée', 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompagnieUpdateFormRequest $request, int $id)
    {
        $compagnie = Compagnie::with('gestionnaires')->find($id);
        if($compagnie) {
            $data = $request->validated();
            $compagnie->update($data);
            return ApiResponse::success($compagnie, 'Compagnie mise à jour avec succès');
        }
        return ApiResponse::error('Compagnie non trouvée', 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $compagnie = Compagnie::with('gestionnaires')->find($id);
        if($compagnie) {
            $compagnie->delete();
            return ApiResponse::success(null, 'Compagnie supprimée avec succès', 204);
        }
        return ApiResponse::error('Compagnie non trouvée', 404);
    }
}
