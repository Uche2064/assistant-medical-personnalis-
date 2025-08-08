<?php

namespace App\Http\Controllers\v1\Api\client;

use App\Enums\TypeDemandeurEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DemandeAdhesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Récupérer la liste des clients pour le technicien (avec recherche)
     */
    public function getClientsTechnicien(Request $request)
    {
        try {
            // Vérifier que l'utilisateur est un technicien
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $query = DemandeAdhesion::with(['user', 'user.assure', 'user.entreprise'])
                ->whereIn('type_demandeur', [TypeDemandeurEnum::PHYSIQUE->value, TypeDemandeurEnum::ENTREPRISE->value])
                ->whereIn('statut', [
                    StatutDemandeAdhesionEnum::EN_ATTENTE->value,
                    StatutDemandeAdhesionEnum::EN_PROPOSITION->value,
                    StatutDemandeAdhesionEnum::ACCEPTEE->value
                ]);

            // Recherche par nom ou email
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $demandes = $query->get()->map(function ($demande) {
                return [
                    'id' => $demande->id,
                    'client_id' => $demande->user->id,
                    'nom' => $demande->user->nom ?? $demande->user->name,
                    'email' => $demande->user->email,
                    'type_demandeur' => $demande->type_demandeur?->value ?? $demande->type_demandeur,
                    'statut' => $demande->statut?->value ?? $demande->statut,
                    'date_soumission' => $demande->created_at->format('Y-m-d'),
                    'duree_attente' => $demande->created_at->diffForHumans()
                ];
            });

            return ApiResponse::success($demandes, 'Liste des clients récupérée avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des clients', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des clients: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les statistiques des clients pour le technicien
     */
    public function getStatistiquesClients()
    {
        try {
            // Vérifier que l'utilisateur est un technicien
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $totalClients = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::PHYSIQUE->value, 
                TypeDemandeurEnum::ENTREPRISE->value
            ])->count();

            $clientsEnAttente = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::PHYSIQUE->value, 
                TypeDemandeurEnum::ENTREPRISE->value
            ])->where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count();

            $clientsEnProposition = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::PHYSIQUE->value, 
                TypeDemandeurEnum::ENTREPRISE->value
            ])->where('statut', StatutDemandeAdhesionEnum::EN_PROPOSITION->value)->count();

            $clientsAcceptes = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::PHYSIQUE->value, 
                TypeDemandeurEnum::ENTREPRISE->value
            ])->where('statut', StatutDemandeAdhesionEnum::ACCEPTEE->value)->count();

            $repartitionParType = [
                'physique' => DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PHYSIQUE->value)->count(),
                'entreprise' => DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::ENTREPRISE->value)->count(),
            ];

            $statistiques = [
                'total_clients' => $totalClients,
                'clients_en_attente' => $clientsEnAttente,
                'clients_en_proposition' => $clientsEnProposition,
                'clients_acceptes' => $clientsAcceptes,
                'repartition_par_type' => $repartitionParType,
                'taux_acceptation' => $totalClients > 0 ? round(($clientsAcceptes / $totalClients) * 100, 2) : 0
            ];

            return ApiResponse::success($statistiques, 'Statistiques des clients récupérées avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques des clients', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les détails d'un client spécifique
     */
    public function show(int $id)
    {
        try {
            // Vérifier que l'utilisateur est un technicien
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $demande = DemandeAdhesion::with([
                'user', 
                'user.assure', 
                'user.entreprise',
                'reponsesQuestionnaire.question'
            ])->find($id);

            if (!$demande) {
                return ApiResponse::error('Client non trouvé', 404);
            }

            // Vérifier que c'est bien un client (physique ou entreprise)
            if (!in_array($demande->type_demandeur, [
                TypeDemandeurEnum::PHYSIQUE->value, 
                TypeDemandeurEnum::ENTREPRISE->value
            ])) {
                return ApiResponse::error('Ce n\'est pas un client valide', 400);
            }

            $clientData = [
                'id' => $demande->id,
                'client_id' => $demande->user->id,
                'nom' => $demande->user->nom ?? $demande->user->name,
                'email' => $demande->user->email,
                'contact' => $demande->user->contact,
                'adresse' => $demande->user->adresse,
                'type_demandeur' => $demande->type_demandeur?->value ?? $demande->type_demandeur,
                'statut' => $demande->statut?->value ?? $demande->statut,
                'date_soumission' => $demande->created_at,
                'date_mise_a_jour' => $demande->updated_at,
                'duree_attente' => $demande->created_at->diffForHumans(),
                'reponses_questionnaire' => $demande->reponsesQuestionnaire->map(function ($reponse) {
                    return [
                        'question_id' => $reponse->question_id,
                        'question_libelle' => $reponse->question->libelle,
                        'reponse_text' => $reponse->reponse_text,
                        'reponse_number' => $reponse->reponse_number,
                        'reponse_bool' => $reponse->reponse_bool,
                        'reponse_date' => $reponse->reponse_date,
                        'reponse_fichier' => $reponse->reponse_fichier,
                    ];
                })
            ];

            // Ajouter les données spécifiques selon le type
            if ($demande->type_demandeur === TypeDemandeurEnum::PHYSIQUE->value) {
                $clientData['assure'] = $demande->user->assure ? [
                    'id' => $demande->user->assure->id,
                    'nom' => $demande->user->assure->nom,
                    'prenoms' => $demande->user->assure->prenoms,
                    'date_naissance' => $demande->user->assure->date_naissance,
                    'sexe' => $demande->user->assure->sexe,
                    'profession' => $demande->user->assure->profession,
                    'photo_url' => $demande->user->assure->photo_url,
                ] : null;
            } elseif ($demande->type_demandeur === TypeDemandeurEnum::ENTREPRISE->value) {
                $clientData['entreprise'] = $demande->user->entreprise ? [
                    'id' => $demande->user->entreprise->id,
                    'raison_sociale' => $demande->user->entreprise->raison_sociale,
                    'nombre_employe' => $demande->user->entreprise->nombre_employe,
                    'secteur_activite' => $demande->user->entreprise->secteur_activite,
                ] : null;
            }

            return ApiResponse::success($clientData, 'Détails du client récupérés avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du client', [
                'error' => $e->getMessage(),
                'client_id' => $id,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération du client: ' . $e->getMessage(), 500);
        }
    }
} 