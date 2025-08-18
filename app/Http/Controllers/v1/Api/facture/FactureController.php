<?php

namespace App\Http\Controllers\v1\Api\facture;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Assure;
use App\Models\ClientPrestataire;
use App\Models\Facture;
use App\Models\Sinistre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FactureController extends Controller
{
     /**
     * Liste des factures à valider
     */
    public function factures(Request $request)
    {
        $user = Auth::user();
        $query = Facture::with([
            'prestataire',
            'sinistre.assure', 
            'technicien', 
            'medecin', 
            'comptable'
        ]);

        // Filtrage selon le type d'utilisateur
        if ($user->hasRole('prestataire') && $user->prestataire) {
            // Pour les prestataires : afficher seulement les factures qu'ils ont délivrées
            $query->where('prestataire_id', $user->prestataire->id);
        } elseif ($user->hasRole('entreprise') && $user->entreprise) {
            // Pour les entreprises : afficher les factures de tous leurs assurés
            $assureIds = $user->entreprise->assures()->pluck('id');
            $query->whereHas('sinistre', function ($q) use ($assureIds) {
                $q->whereIn('assure_id', $assureIds);
            });
        } elseif ($user->hasRole('physique') && $user->assure) {
            // Pour les assurés physiques : afficher leurs propres factures + celles de leurs bénéficiaires
            $assureIds = [$user->assure->id]; // L'assuré principal
            
            // Ajouter les IDs des bénéficiaires
            $beneficiairesIds = $user->assure->beneficiaires()->pluck('id')->toArray();
            $assureIds = array_merge($assureIds, $beneficiairesIds);
            
            $query->whereHas('sinistre', function ($q) use ($assureIds) {
                $q->whereIn('assure_id', $assureIds);
            });
        } elseif ($user->hasRole('technicien') || $user->hasRole('medecin_controleur') || $user->hasRole('comptable') || $user->hasRole('admin_global') || $user->hasRole('gestionnaire')) {
            // Pour le personnel : afficher toutes les factures (pas de filtrage)
            // Ces rôles peuvent voir toutes les factures
        } else {
            // Pour les autres utilisateurs : pas d'accès aux factures
            return ApiResponse::error('Accès non autorisé aux factures', 403);
        }

        // Filtres additionnels
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('prestataire_id')) {
            $query->where('prestataire_id', $request->prestataire_id);
        }

        if ($request->has('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $factures = $query->paginate($request->get('per_page', 10));

        return ApiResponse::success($factures, 'Liste des factures récupérée avec succès');
    }

    public function showFacture($id)
    {
        $user = Auth::user();
        $query = Facture::with([
            'prestataire', 
            'sinistre.assure', 
            'technicien', 
            'medecin', 
            'comptable'
        ])->where('numero_facture', $id);

        // Filtrage selon le type d'utilisateur
        if ($user->hasRole('prestataire') && $user->prestataire) {
            // Pour les prestataires : vérifier que c'est une facture qu'ils ont délivrée
            $query->where('prestataire_id', $user->prestataire->id);
        } elseif ($user->hasRole('entreprise') && $user->entreprise) {
            // Pour les entreprises : vérifier que la facture concerne un de leurs assurés
            $assureIds = $user->entreprise->assures()->pluck('id');
            $query->whereHas('sinistre', function ($q) use ($assureIds) {
                $q->whereIn('assure_id', $assureIds);
            });
        } elseif ($user->hasRole('physique') && $user->assure) {
            // Pour les assurés physiques : vérifier que c'est leur facture ou celle de leurs bénéficiaires
            $assureIds = [$user->assure->id]; // L'assuré principal
            
            // Ajouter les IDs des bénéficiaires
            $beneficiairesIds = $user->assure->beneficiaires()->pluck('id')->toArray();
            $assureIds = array_merge($assureIds, $beneficiairesIds);
            
            $query->whereHas('sinistre', function ($q) use ($assureIds) {
                $q->whereIn('assure_id', $assureIds);
            });
        } elseif ($user->hasRole('technicien') || $user->hasRole('medecin_controleur') || $user->hasRole('comptable') || $user->hasRole('admin_global') || $user->hasRole('gestionnaire')) {
            // Pour le personnel : pas de restriction (peut voir toutes les factures)
        } else {
            // Pour les autres utilisateurs : pas d'accès aux factures
            return ApiResponse::error('Accès non autorisé aux factures', 403);
        }

        $facture = $query->first();

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        Log::info($facture);

        return ApiResponse::success($facture, 'Facture récupérée avec succès');
    }

    /**
     * Statistiques des factures pour les prestataires
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un prestataire
        if (!$user->hasRole('prestataire') || !$user->prestataire) {
            return ApiResponse::error('Accès réservé aux prestataires', 403);
        }

        $prestataire = $user->prestataire;

        // 1. Récupérer tous les assurés assignés à ce prestataire via ClientPrestataire
        // Logique correcte : ClientPrestataire → ClientContrat → User → Assuré principal + Bénéficiaires
        $clientPrestataires = ClientPrestataire::where('prestataire_id', $prestataire->id)
            ->where('statut', 'actif')
            ->with(['clientContrat.client.assure', 'clientContrat.client.entreprise.assures'])
            ->get();

        $assuresIds = [];
        foreach ($clientPrestataires as $clientPrestataire) {
            $clientContrat = $clientPrestataire->clientContrat;
            
            // Vérifier que le contrat est actif
            if ($clientContrat && 
                $clientContrat->statut === 'actif' && 
                $clientContrat->date_debut <= now() && 
                $clientContrat->date_fin >= now()) {
                
                $user = $clientContrat->client;
                
                // Cas 1: Assuré physique principal
                if ($user->assure) {
                    // Si c'est un assuré principal OU si c'est le seul assuré (pas de bénéficiaire)
                    if ($user->assure->est_principal || !$user->assure->assure_principal_id) {
                        $assuresIds[] = $user->assure->id;
                    }
                }
                
                // Cas 2: Entreprise (récupérer tous les employés principaux)
                if ($user->entreprise) {
                    $employesPrincipaux = $user->entreprise->assures()
                        ->where('est_principal', true)
                        ->pluck('id')
                        ->toArray();
                    $assuresIds = array_merge($assuresIds, $employesPrincipaux);
                }
            }
        }

        // Récupérer les bénéficiaires de ces assurés principaux
        $beneficiairesIds = Assure::whereIn('assure_principal_id', $assuresIds)
            ->where('est_principal', false)
            ->pluck('id')
            ->toArray();

        // Combiner tous les IDs uniques
        $tousPatients = array_unique(array_merge($assuresIds, $beneficiairesIds));
        
        // FALLBACK: Si aucun assuré trouvé, récupérer tous les assurés liés aux contrats de ce prestataire
        if (empty($tousPatients)) {
            $userIds = $clientPrestataires->pluck('clientContrat.user_id')->toArray();
            
            $tousPatients = Assure::whereIn('user_id', $userIds)
                ->orWhereHas('assurePrincipal', function ($query) use ($userIds) {
                    $query->whereIn('user_id', $userIds);
                })
                ->pluck('id')
                ->toArray();
        }
        
        $nombreTotalAssures = count($tousPatients);



        // 2. Répartition des patients par sexe
        $repartitionSexe = [];
        if (!empty($tousPatients)) {
            $repartitionSexe = Assure::whereIn('id', $tousPatients)
                ->selectRaw('sexe, COUNT(*) as count')
                ->groupBy('sexe')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->sexe->value ?? 'Non renseigné' => $item->count];
                })
                ->toArray();
        }

        // 3. Nombre total de sinistres
        $totalSinistres = Sinistre::where('prestataire_id', $prestataire->id)->count();

        // 4. Nombre total de factures
        $totalFactures = Facture::where('prestataire_id', $prestataire->id)->count();

        // 5. Montants totaux
        $montants = Facture::where('prestataire_id', $prestataire->id)
            ->selectRaw('
                SUM(montant_reclame) as montant_total_reclame,
                SUM(ticket_moderateur) as montant_total_ticket_moderateur
            ')
            ->first();

        $soldeTotal = $montants->montant_total_reclame ?? 0;
        $resteARembourser = ($montants->montant_total_reclame ?? 0) - ($montants->montant_total_ticket_moderateur ?? 0);
        $ticketModerateurTotal = $montants->montant_total_ticket_moderateur ?? 0;

        // 6. Répartition des sinistres par mois (sur les 12 derniers mois)
        $driver = DB::connection()->getDriverName();
        $monthExpression = match ($driver) {
            'pgsql' => "EXTRACT(MONTH FROM sinistres.created_at)",
            'mysql' => "MONTH(sinistres.created_at)",
            default => "MONTH(sinistres.created_at)"
        };

        $sinistresParMois = Sinistre::where('prestataire_id', $prestataire->id)
            ->selectRaw("$monthExpression as mois, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->map(function ($item) {
                return [
                    'mois' => $item->mois,
                    'nom_mois' => date('F', mktime(0, 0, 0, $item->mois, 1)),
                    'count' => $item->count
                ];
            });

        $stats = [
            'nombre_total_assures' => $nombreTotalAssures,
            'repartition_sexe' => $repartitionSexe,
            'total_sinistres' => $totalSinistres,
            'total_factures' => $totalFactures,
            'montants' => [
                'solde_total' => $soldeTotal,
                'reste_a_rembourser' => $resteARembourser,
                'ticket_moderateur_total' => $ticketModerateurTotal
            ],
            'sinistres_par_mois' => $sinistresParMois
        ];

        return ApiResponse::success($stats, 'Statistiques du prestataire récupérées avec succès');
    }
}
