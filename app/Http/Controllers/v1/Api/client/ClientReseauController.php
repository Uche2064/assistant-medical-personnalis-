<?php

namespace App\Http\Controllers\v1\Api\client;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ClientContrat;
use App\Models\ClientPrestataire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientReseauController extends Controller
{
    /**
     * Récupérer les prestataires assignés au client connecté
     */
    public function mesPrestataires(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Vérifier que l'utilisateur est un client (assure ou entreprise)
            if (!$user->hasRole('assure') && !$user->hasRole('entreprise')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $perPage = $request->input('per_page', 20);

            // Récupérer les contrats actifs du client
            $clientContrats = ClientContrat::where('client_id', $user->id)
                ->with(['contrat', 'prestataires.prestataire.user'])
                ->where('statut', 'actif')
                ->where('date_debut', '<=', now())
                ->where('date_fin', '>=', now())
                ->get();

            $prestatairesData = collect();

            foreach ($clientContrats as $clientContrat) {
                $prestatairesActifs = $clientContrat->prestataires()
                    ->where('statut', 'actif')
                    ->with('prestataire.user')
                    ->get();

                foreach ($prestatairesActifs as $clientPrestataire) {
                    $prestataire = $clientPrestataire->prestataire;
                    
                    // Filtrer par type de prestataire si demandé
                    if ($request->filled('type_prestataire') && 
                        $prestataire->type_prestataire !== $request->type_prestataire) {
                        continue;
                    }

                    // Filtrer par recherche si demandé
                    if ($request->filled('search')) {
                        $search = strtolower($request->search);
                        $raisonSociale = strtolower($prestataire->raison_sociale);
                        $adresse = strtolower($prestataire->adresse ?? '');
                        
                        if (strpos($raisonSociale, $search) === false && 
                            strpos($adresse, $search) === false) {
                            continue;
                        }
                    }

                    $prestatairesData->push([
                        'id' => $prestataire->id,
                        'raison_sociale' => $prestataire->raison_sociale,
                        'type_prestataire' => $prestataire->type_prestataire,
                        'adresse' => $prestataire->adresse,
                        'contact' => $prestataire->user->contact ?? null,
                        'email' => $prestataire->user->email,
                        'date_assignation' => $clientPrestataire->created_at,
                        'statut_assignation' => $clientPrestataire->statut,
                        'contrat' => [
                            'id' => $clientContrat->contrat->id,
                            'libelle' => $clientContrat->contrat->libelle,
                            'date_debut' => $clientContrat->date_debut,
                            'date_fin' => $clientContrat->date_fin,
                        ],
                    ]);
                }
            }

            // Paginer manuellement les résultats
            $total = $prestatairesData->count();
            $currentPage = $request->input('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedData = $prestatairesData->slice($offset, $perPage)->values();

            return ApiResponse::success([
                'data' => $paginatedData,
                'pagination' => [
                    'current_page' => $currentPage,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                ]
            ], 'Prestataires assignés récupérés avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des prestataires du client', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les statistiques du réseau de prestataires du client
     */
    public function statistiquesReseau()
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('assure') && !$user->hasRole('entreprise')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            // Compter les prestataires par type
            $stats = ClientContrat::where('client_id', $user->id)
                ->where('statut', 'actif')
                ->where('date_debut', '<=', now())
                ->where('date_fin', '>=', now())
                ->with('prestataires.prestataire')
                ->get()
                ->flatMap(function ($clientContrat) {
                    return $clientContrat->prestataires()
                        ->where('statut', 'actif')
                        ->with('prestataire')
                        ->get()
                        ->pluck('prestataire');
                })
                ->groupBy('type_prestataire')
                ->map(function ($group) {
                    return $group->count();
                });

            $totalPrestataires = $stats->sum();

            return ApiResponse::success([
                'total_prestataires' => $totalPrestataires,
                'par_type' => $stats,
                'types_disponibles' => [
                    'CENTRE_SOINS' => 'Centre de soins',
                    'PHARMACIE' => 'Pharmacie',
                    'LABORATOIRE' => 'Laboratoire',
                    'OPTIQUE' => 'Optique',
                ],
            ], 'Statistiques du réseau récupérées avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques réseau client', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les détails d'un prestataire assigné
     */
    public function detailsPrestataire($prestataireId)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('assure') && !$user->hasRole('entreprise')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            // Vérifier que le prestataire est bien assigné au client
            $assignation = ClientPrestataire::whereHas('clientContrat', function ($q) use ($user) {
                $q->where('client_id', $user->id)
                  ->where('statut', 'ACTIF')
                  ->where('date_debut', '<=', now())
                  ->where('date_fin', '>=', now());
            })
            ->where('prestataire_id', $prestataireId)
            ->where('statut', 'ACTIF')
            ->with(['prestataire.user', 'clientContrat.contrat'])
            ->first();

            if (!$assignation) {
                return ApiResponse::error('Prestataire non assigné ou non trouvé', 404);
            }

            $prestataire = $assignation->prestataire;
            $contrat = $assignation->clientContrat->contrat;

            return ApiResponse::success([
                'prestataire' => [
                    'id' => $prestataire->id,
                    'raison_sociale' => $prestataire->raison_sociale,
                    'type_prestataire' => $prestataire->type_prestataire,
                    'adresse' => $prestataire->adresse,
                    'contact' => $prestataire->user->contact ?? null,
                    'email' => $prestataire->user->email,
                    'statut' => $prestataire->statut,
                ],
                'assignation' => [
                    'date_assignation' => $assignation->created_at,
                    'statut' => $assignation->statut,
                ],
                'contrat' => [
                    'id' => $contrat->id,
                    'libelle' => $contrat->libelle,
                    'date_debut' => $assignation->clientContrat->date_debut,
                    'date_fin' => $assignation->clientContrat->date_fin,
                ],
            ], 'Détails du prestataire récupérés avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des détails prestataire', [
                'error' => $e->getMessage(),
                'prestataire_id' => $prestataireId,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
        }
    }
}