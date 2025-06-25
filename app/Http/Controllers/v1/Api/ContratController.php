<?php

namespace App\Http\Controllers\v1\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContratFormRequest;
use App\Models\Contrat;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContratController extends Controller
{
    /**
     * Récupérer tous les contrats
     * Filtrable par statut (actif, suspendu, résilié)
     */
    public function index(Request $request)
    {
        $query = Contrat::with(['client', 'technicien']);

        // Filtrage par statut si fourni
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filtrage par client si fourni
        if ($request->has('client_id')) {
            $query->where('client_id', $request->query('client_id'));
        }

        $contrats = $query->orderBy('created_at', 'desc')->get();

        if ($contrats->isEmpty()) {
            return ApiResponse::success([], 'Aucun contrat trouvé');
        }

        return ApiResponse::success($contrats, 'Liste des contrats récupérée avec succès');
    }

    /**
     * Enregistrer un nouveau contrat
     */
    public function store(ContratFormRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Création du contrat
            $contrat = Contrat::create($data);

            DB::commit();

            return ApiResponse::success([
                'contrat_id' => $contrat->id,
                'uuid' => $contrat->uuid,
            ], 'Contrat enregistré avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Afficher les détails d'un contrat
     */
    public function show(string $uuid)
    {
        $contrat = Contrat::with(['client', 'technicien'])
            ->where('uuid', $uuid)
            ->first();

        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }

        return ApiResponse::success($contrat, 'Détails du contrat');
    }

    /**
     * Mettre à jour un contrat
     */
    public function update(ContratFormRequest $request, string $uuid)
    {
        $contrat = Contrat::where('uuid', $uuid)->first();

        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }

        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Enregistrement des informations de l'utilisateur qui modifie le contrat
            $data['updated_by'] = Auth::id();

            $contrat->update($data);

            DB::commit();

            return ApiResponse::success([
                'contrat_id' => $contrat->id,
                'uuid' => $contrat->uuid,
            ], 'Contrat mis à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Changer le statut d'un contrat (suspension, résiliation)
     */
    public function changeStatus(Request $request, string $uuid)
    {
        $request->validate([
            'status' => 'required|string|in:actif,suspendu,résilié',
            'motif' => 'sometimes|string'
        ]);

        $contrat = Contrat::where('uuid', $uuid)->first();

        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }

        try {
            $oldStatus = $contrat->status;
            $contrat->status = $request->status;
            $contrat->updated_by = Auth::id();
            
            // Enregistrer le motif du changement de statut si fourni
            if ($request->has('motif')) {
                $infosComplementaires = $contrat->infos_complementaires ?? [];
                $infosComplementaires['changements_status'][] = [
                    'date' => now()->toDateTimeString(),
                    'ancien_status' => $oldStatus,
                    'nouveau_status' => $request->status,
                    'motif' => $request->motif,
                    'utilisateur_id' => Auth::id()
                ];
                
                $contrat->infos_complementaires = $infosComplementaires;
            }
            
            $contrat->save();

            return ApiResponse::success([
                'contrat_id' => $contrat->id,
                'uuid' => $contrat->uuid,
                'status' => $contrat->status
            ], 'Statut du contrat modifié avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}