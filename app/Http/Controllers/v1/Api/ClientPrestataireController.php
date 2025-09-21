<?php

namespace App\Http\Controllers\v1\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ClientPrestataire;
use App\Models\ClientContrat;
use App\Models\User;
use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClientPrestataireController extends Controller
{
    /**
     * Récupérer toutes les assignations clients-prestataires avec pagination
     */
    public function index(Request $request)
    {
        try {
       

            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            $query = ClientPrestataire::with([
                'clientContrat.client.entreprise',
                'prestataire'
            ]);

            // Filtres
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->filled('type_prestataire')) {
                $query->where('type_prestataire', $request->type_prestataire);
            }

            if ($request->filled('client_id')) {
                $query->whereHas('clientContrat', function ($q) use ($request) {
                    $q->where('user_id', $request->client_id);
                });
            }

            if ($request->filled('prestataire_id')) {
                $query->where('prestataire_id', $request->prestataire_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    // Recherche par nom/prénom du client
                    $q->whereHas('clientContrat.client', function ($userQuery) use ($search) {
                        $userQuery->where('nom', 'like', "%{$search}%")
                                 ->orWhere('prenoms', 'like', "%{$search}%");
                    })
                    // Recherche par raison sociale du prestataire
                    ->orWhereHas('prestataire', function ($prestataireQuery) use ($search) {
                        $prestataireQuery->where('raison_sociale', 'like', "%{$search}%");
                    });
                });
            }

            // Tri
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $assignations = $query->paginate($perPage);

            $data = $assignations->getCollection()->map(function ($assignation) {
                $clientContrat = $assignation->clientContrat;
                $user = $clientContrat->client;
                $prestataire = $assignation->prestataire;

                // Déterminer le nom du client (nom/prénom ou raison sociale)
                $nomClient = $user->entreprise ? $user->entreprise->raison_sociale : ($user->assure->nom . ' ' . $user->assure->prenoms);

                return [
                    'id' => $assignation->id,
                    'client' => $nomClient,
                    'prestataire' => $prestataire->raison_sociale,
                    'type_prestataire' => $assignation->type_prestataire,
                    'date_assignation' => $assignation->created_at,
                    'statut' => $assignation->statut,
                ];
            });

            // Créer un nouveau paginator avec les données transformées
            $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
                $data,
                $assignations->total(),
                $assignations->perPage(),
                $assignations->currentPage(),
                [
                    'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );

            return ApiResponse::success($paginatedData, 'Assignations clients-prestataires récupérées avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des assignations clients-prestataires', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer une assignation spécifique
     */
    public function show($id)
    {
        try {
            // Vérifier les permissions
            if (!Auth::user()->hasRole(['admin_global', 'technicien', 'medecin_controleur'])) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $assignation = ClientPrestataire::with([
                'clientContrat.client.assure',
                'clientContrat.client.entreprise',
                'clientContrat.contrat',
                'prestataire.user'
            ])->find($id);

            if (!$assignation) {
                return ApiResponse::error('Assignation non trouvée', 404);
            }

            $clientContrat = $assignation->clientContrat;
            $user = $clientContrat->client;
            $prestataire = $assignation->prestataire;

            $data = [
                'id' => $assignation->id,
                'client' => [
                    'id' => $user->id,
                    'nom' => $user->nom ?? $user->name,
                    'prenoms' => $user->prenoms,
                    'email' => $user->email,
                    'contact' => $user->contact,
                    'adresse' => $user->adresse,
                    'type_client' => $user->entreprise ? 'entreprise' : 'client',
                    'raison_sociale' => $user->entreprise ? $user->entreprise->raison_sociale : null,
                    'assure' => $user->assure ? [
                        'id' => $user->assure->id,
                        'nom' => $user->assure->nom,
                        'prenoms' => $user->assure->prenoms,
                        'date_naissance' => $user->assure->date_naissance,
                        'sexe' => $user->assure->sexe,
                        'profession' => $user->assure->profession,
                    ] : null,
                ],
                'prestataire' => [
                    'id' => $prestataire->id,
                    'raison_sociale' => $prestataire->raison_sociale,
                    'type_prestataire' => $prestataire->type_prestataire,
                    'adresse' => $prestataire->user->adresse,
                    'contact' => $prestataire->user->contact,
                    'email' => $prestataire->user->email,
                    'statut' => $prestataire->statut,
                    'medecin_controleur' => $prestataire->medecinControleur ? [
                        'id' => $prestataire->medecinControleur->id,
                        'nom' => $prestataire->medecinControleur->nom,
                        'prenoms' => $prestataire->medecinControleur->prenoms,
                    ] : null,
                ],
                'contrat' => [
                    'id' => $clientContrat->contrat->id,
                    'libelle' => $clientContrat->contrat->libelle,
                    'numero_police' => $clientContrat->numero_police,
                    'date_debut' => $clientContrat->date_debut,
                    'date_fin' => $clientContrat->date_fin,
                    'statut' => $clientContrat->statut,
                ],
                'assignation' => [
                    'type_prestataire' => $assignation->type_prestataire,
                    'statut' => $assignation->statut,
                    'date_assignation' => $assignation->created_at,
                    'date_modification' => $assignation->updated_at,
                ]
            ];

            return ApiResponse::success($data, 'Assignation récupérée avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'assignation', [
                'error' => $e->getMessage(),
                'assignation_id' => $id,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Modifier le statut d'une assignation
     */
    public function update(Request $request, $id)
    {
        try {
            // Vérifier les permissions
            if (!Auth::user()->hasRole(['admin_global', 'technicien'])) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $request->validate([
                'statut' => 'required|in:actif,inactif',
                'motif' => 'nullable|string|max:500',
            ]);

            $assignation = ClientPrestataire::find($id);

            if (!$assignation) {
                return ApiResponse::error('Assignation non trouvée', 404);
            }

            $ancienStatut = $assignation->statut;
            $assignation->update([
                'statut' => $request->statut,
            ]);

            // Log de la modification
            Log::info('Statut d\'assignation modifié', [
                'assignation_id' => $id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $request->statut,
                'motif' => $request->motif,
                'modifie_par' => Auth::id(),
            ]);

            return ApiResponse::success([
                'id' => $assignation->id,
                'statut' => $assignation->statut,
                'date_modification' => $assignation->updated_at,
            ], 'Statut de l\'assignation modifié avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la modification de l\'assignation', [
                'error' => $e->getMessage(),
                'assignation_id' => $id,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la modification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Supprimer une assignation
     */
    public function destroy($id)
    {
        try {
            // Vérifier les permissions
            if (!Auth::user()->hasRole(['admin_global', 'technicien'])) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $assignation = ClientPrestataire::find($id);

            if (!$assignation) {
                return ApiResponse::error('Assignation non trouvée', 404);
            }

            $assignation->delete();

            Log::info('Assignation supprimée', [
                'assignation_id' => $id,
                'supprime_par' => Auth::id(),
            ]);

            return ApiResponse::success(null, 'Assignation supprimée avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'assignation', [
                'error' => $e->getMessage(),
                'assignation_id' => $id,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la suppression: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Statistiques des assignations
     */
    public function statistiques()
    {
        try {
            // Vérifier les permissions
            if (!Auth::user()->hasRole(['admin_global', 'technicien', 'medecin_controleur'])) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $stats = [
                'total_assignations' => ClientPrestataire::count(),
                'assignations_actives' => ClientPrestataire::where('statut', 'actif')->count(),
                'assignations_inactives' => ClientPrestataire::where('statut', 'inactif')->count(),
                'par_type_prestataire' => ClientPrestataire::select('type_prestataire', DB::raw('count(*) as total'))
                    ->groupBy('type_prestataire')
                    ->get()
                    ->pluck('total', 'type_prestataire'),
                'clients_uniques' => ClientPrestataire::distinct('client_contrat_id')->count(),
                'prestataires_uniques' => ClientPrestataire::distinct('prestataire_id')->count(),
                'assignations_ce_mois' => ClientPrestataire::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];

            return ApiResponse::success($stats, 'Statistiques récupérées avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }
}
