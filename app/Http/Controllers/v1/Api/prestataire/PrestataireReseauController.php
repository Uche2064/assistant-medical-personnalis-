<?php

namespace App\Http\Controllers\v1\Api\prestataire;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ClientPrestataire;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PrestataireReseauController extends Controller
{
    /**
     * Récupérer les clients assignés au prestataire connecté
     */
    public function mesClients(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('prestataire')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $prestataire = Prestataire::where('user_id', $user->id)->first();
            if (!$prestataire) {
                return ApiResponse::error('Prestataire non trouvé', 404);
            }

            $perPage = $request->input('per_page', 20);

            $query = ClientPrestataire::where('prestataire_id', $prestataire->id)
                ->where('statut', 'actif')
                ->with([
                    'clientContrat.user.assure',
                    'clientContrat.user.entreprise',
                    'clientContrat.contrat'
                ])
                ->whereHas('clientContrat', function ($q) {
                    $q->where('statut', 'actif')
                      ->where('date_debut', '<=', now())
                      ->where('date_fin', '>=', now());
                });

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('clientContrat.user', function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('entreprise', function ($eq) use ($search) {
                          $eq->where('raison_sociale', 'like', "%{$search}%");
                      });
                });
            }

            $assignations = $query->paginate($perPage);

            $clientsData = $assignations->getCollection()->map(function ($assignation) {
                $clientContrat = $assignation->clientContrat;
                $user = $clientContrat->user;
                $isEntreprise = $user->entreprise !== null;

                return [
                    'id' => $user->id,
                    'nom' => $user->nom ?? $user->name,
                    'prenoms' => $user->prenoms ?? null,
                    'email' => $user->email,
                    'contact' => $user->contact,
                    'type_client' => $isEntreprise ? 'entreprise' : 'client',
                    'raison_sociale' => $isEntreprise ? $user->entreprise->raison_sociale : null,
                    'contrat' => [
                        'id' => $clientContrat->contrat->id,
                        'libelle' => $clientContrat->contrat->libelle,
                        'date_debut' => $clientContrat->date_debut,
                        'date_fin' => $clientContrat->date_fin,
                    ],
                    'assignation' => [
                        'date_assignation' => $assignation->created_at,
                        'statut' => $assignation->statut,
                    ],
                ];
            });

            return ApiResponse::success([
                'data' => $clientsData,
                'pagination' => [
                    'current_page' => $assignations->currentPage(),
                    'per_page' => $assignations->perPage(),
                    'total' => $assignations->total(),
                    'last_page' => $assignations->lastPage(),
                ]
            ], 'Clients assignés récupérés avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des clients du prestataire', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les statistiques du réseau de clients
     */
    public function statistiquesClients()
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('prestataire')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $prestataire = Prestataire::where('user_id', $user->id)->first();
            if (!$prestataire) {
                return ApiResponse::error('Prestataire non trouvé', 404);
            }

            $totalClients = ClientPrestataire::where('prestataire_id', $prestataire->id)
                ->where('statut', 'ACTIF')
                ->whereHas('clientContrat', function ($q) {
                    $q->where('statut', 'ACTIF')
                      ->where('date_debut', '<=', now())
                      ->where('date_fin', '>=', now());
                })
                ->count();

            return ApiResponse::success([
                'total_clients' => $totalClients,
                'date_derniere_assignation' => ClientPrestataire::where('prestataire_id', $prestataire->id)
                    ->where('statut', 'ACTIF')
                    ->latest()
                    ->value('created_at'),
            ], 'Statistiques du réseau récupérées avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques réseau prestataire', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
        }
    }
}