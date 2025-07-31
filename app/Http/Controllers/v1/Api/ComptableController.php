<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Enums\StatutFactureEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComptableController extends Controller
{
    /**
     * Dashboard du comptable
     */
    public function dashboard()
    {
        $user = Auth::user();
        $comptable = $user->personnel;

        if (!$comptable || !$comptable->isComptable()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        // Statistiques financières
        $stats = [
            'total_factures' => Facture::count(),
            'factures_en_attente' => Facture::where('statut', StatutFactureEnum::EN_ATTENTE)->count(),
            'factures_validees_technicien' => Facture::where('statut', StatutFactureEnum::VALIDEE_TECHNICIEN)->count(),
            'factures_validees_medecin' => Facture::where('statut', StatutFactureEnum::VALIDEE_MEDECIN)->count(),
            'factures_autorisees_comptable' => Facture::where('statut', StatutFactureEnum::AUTORISEE_COMPTABLE)->count(),
            'factures_remboursees' => Facture::where('statut', StatutFactureEnum::REMBOURSEE)->count(),
            'montant_total_rembourse' => Facture::where('statut', StatutFactureEnum::REMBOURSEE)->sum('montant'),
            'montant_en_attente' => Facture::whereIn('statut', [
                StatutFactureEnum::EN_ATTENTE,
                StatutFactureEnum::VALIDEE_TECHNICIEN,
                StatutFactureEnum::VALIDEE_MEDECIN
            ])->sum('montant'),
        ];

        // Factures récentes
        $facturesRecentes = Facture::with(['prestataire', 'assure'])
            ->latest()
            ->take(5)
            ->get();

        return ApiResponse::success([
            'comptable' => [
                'id' => $comptable->id,
                'nom' => $comptable->nom,
                'prenoms' => $comptable->prenoms,
                'email' => $comptable->email,
            ],
            'statistiques' => $stats,
            'factures_recentes' => $facturesRecentes,
        ], 'Dashboard comptable récupéré avec succès');
    }

    /**
     * Liste des factures
     */
    public function factures(Request $request)
    {
        $user = Auth::user();
        $comptable = $user->personnel;

        if (!$comptable || !$comptable->isComptable()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $query = Facture::with(['prestataire', 'assure', 'technicien', 'medecin']);

        // Filtres
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

        if ($request->has('montant_min')) {
            $query->where('montant', '>=', $request->montant_min);
        }

        if ($request->has('montant_max')) {
            $query->where('montant', '<=', $request->montant_max);
        }

        $factures = $query->paginate($request->get('per_page', 10));

        return ApiResponse::success($factures, 'Liste des factures récupérée avec succès');
    }

    /**
     * Valider un remboursement (autoriser le paiement)
     */
    public function validerRemboursement(Request $request, $id)
    {
        $user = Auth::user();
        $comptable = $user->personnel;

        if (!$comptable || !$comptable->isComptable()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $facture = Facture::find($id);

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Vérifier que la facture est validée par le médecin
        if ($facture->statut !== StatutFactureEnum::VALIDEE_MEDECIN) {
            return ApiResponse::error('La facture doit être validée par le médecin contrôleur avant autorisation comptable', 400);
        }

        // Autoriser le remboursement
        $facture->authorizeByComptable($comptable->id);

        return ApiResponse::success($facture, 'Remboursement autorisé avec succès');
    }

    /**
     * Effectuer le remboursement (paiement effectué)
     */
    public function effectuerRemboursement(Request $request, $id)
    {
        $user = Auth::user();
        $comptable = $user->personnel;

        if (!$comptable || !$comptable->isComptable()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $validated = $request->validate([
            'reference_paiement' => 'required|string|max:255',
            'methode_paiement' => 'required|string|in:virement,cheque,especes',
            'date_paiement' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $facture = Facture::find($id);

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Vérifier que la facture est autorisée par le comptable
        if ($facture->statut !== StatutFactureEnum::AUTORISEE_COMPTABLE) {
            return ApiResponse::error('La facture doit être autorisée par le comptable avant remboursement', 400);
        }

        // Effectuer le remboursement
        $facture->update([
            'statut' => StatutFactureEnum::REMBOURSEE,
            'reference_paiement' => $validated['reference_paiement'],
            'methode_paiement' => $validated['methode_paiement'],
            'date_paiement' => $validated['date_paiement'],
            'notes_comptable' => $validated['notes'],
            'rembourse_par_id' => $comptable->id,
            'rembourse_a' => now(),
        ]);

        return ApiResponse::success($facture, 'Remboursement effectué avec succès');
    }

    /**
     * Rejeter une facture
     */
    public function rejeterFacture(Request $request, $id)
    {
        $user = Auth::user();
        $comptable = $user->personnel;

        if (!$comptable || !$comptable->isComptable()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $validated = $request->validate([
            'motif_rejet' => 'required|string|max:500',
        ]);

        $facture = Facture::find($id);

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        // Rejeter la facture
        $facture->update([
            'statut' => StatutFactureEnum::REJETEE,
            'motif_rejet_comptable' => $validated['motif_rejet'],
            'rejetee_par_id' => $comptable->id,
            'rejetee_a' => now(),
        ]);

        return ApiResponse::success($facture, 'Facture rejetée avec succès');
    }

    /**
     * Rapports financiers
     */
    public function rapports(Request $request)
    {
        $user = Auth::user();
        $comptable = $user->personnel;

        if (!$comptable || !$comptable->isComptable()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $periode = $request->get('periode', 'mois'); // jour, semaine, mois, annee
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');

        $query = Facture::query();

        // Filtre par période
        if ($dateDebut && $dateFin) {
            $query->whereBetween('created_at', [$dateDebut, $dateFin]);
        } else {
            switch ($periode) {
                case 'jour':
                    $query->whereDate('created_at', today());
                    break;
                case 'semaine':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'mois':
                    $query->whereMonth('created_at', now()->month);
                    break;
                case 'annee':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        $rapport = [
            'periode' => $periode,
            'total_factures' => $query->count(),
            'montant_total' => $query->sum('montant'),
            'montant_rembourse' => $query->where('statut', StatutFactureEnum::REMBOURSEE)->sum('montant'),
            'montant_en_attente' => $query->whereIn('statut', [
                StatutFactureEnum::EN_ATTENTE,
                StatutFactureEnum::VALIDEE_TECHNICIEN,
                StatutFactureEnum::VALIDEE_MEDECIN,
                StatutFactureEnum::AUTORISEE_COMPTABLE
            ])->sum('montant'),
            'montant_rejete' => $query->where('statut', StatutFactureEnum::REJETEE)->sum('montant'),
            'taux_remboursement' => $query->count() > 0 ? 
                round(($query->where('statut', StatutFactureEnum::REMBOURSEE)->count() / $query->count()) * 100, 2) : 0,
            'repartition_par_statut' => $query->selectRaw('statut, COUNT(*) as count, SUM(montant) as total')
                ->groupBy('statut')
                ->get(),
        ];

        return ApiResponse::success($rapport, 'Rapport financier généré avec succès');
    }

    /**
     * Détails d'une facture
     */
    public function showFacture($id)
    {
        $user = Auth::user();
        $comptable = $user->personnel;

        if (!$comptable || !$comptable->isComptable()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $facture = Facture::with([
            'prestataire', 
            'assure', 
            'technicien', 
            'medecin', 
            'comptable'
        ])->find($id);

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        return ApiResponse::success($facture, 'Facture récupérée avec succès');
    }

    /**
     * Statistiques par prestataire
     */
    public function statistiquesPrestataires(Request $request)
    {
        $user = Auth::user();
        $comptable = $user->personnel;

        if (!$comptable || !$comptable->isComptable()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $query = Facture::with('prestataire');

        if ($request->has('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $statsPrestataires = $query->selectRaw('
                prestataire_id,
                COUNT(*) as total_factures,
                SUM(montant) as montant_total,
                SUM(CASE WHEN statut = ? THEN montant ELSE 0 END) as montant_rembourse,
                SUM(CASE WHEN statut = ? THEN 1 ELSE 0 END) as factures_remboursees
            ', [StatutFactureEnum::REMBOURSEE, StatutFactureEnum::REMBOURSEE])
            ->groupBy('prestataire_id')
            ->with('prestataire')
            ->get();

        return ApiResponse::success($statsPrestataires, 'Statistiques par prestataire récupérées avec succès');
    }
} 