<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\StatutFactureEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DemandeAdhesion;
use App\Models\Facture;
use App\Models\Personnel;
use App\Models\User;
use App\Traits\DemandeAdhesionDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TechnicienController extends Controller
{
    use DemandeAdhesionDataTrait;
    /**
     * Dashboard du technicien
     */
    public function dashboard()
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        // Statistiques du technicien
        $stats = [
            'total_demandes' => DemandeAdhesion::count(),
            'demandes_en_attente' => DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE)->count(),
            'demandes_validees' => DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::VALIDEE)->count(),
            'demandes_rejetees' => DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::REJETEE)->count(),
            'contrats_proposes' => $technicien->contrats()->count(),
            'contrats_acceptes' => $technicien->contrats()->where('statut', 'accepte')->count(),
            'factures_validees' => $technicien->facturesValideesTechnicien()->count(),
        ];

        // Demandes récentes
        $demandesRecentes = DemandeAdhesion::with(['user', 'client', 'entreprise'])
            ->latest()
            ->take(5)
            ->get();

        // Factures en attente de validation
        $facturesEnAttente = Facture::where('statut', StatutFactureEnum::EN_ATTENTE)
            ->with(['prestataire', 'assure'])
            ->latest()
            ->take(5)
            ->get();

        return ApiResponse::success([
            'technicien' => [
                'id' => $technicien->id,
                'nom' => $technicien->nom,
                'prenoms' => $technicien->prenoms,
                'email' => $technicien->email,
            ],
            'statistiques' => $stats,
            'demandes_recentes' => $demandesRecentes,
            'factures_en_attente' => $facturesEnAttente,
        ], 'Dashboard technicien récupéré avec succès');
    }

    /**
     * Liste des demandes d'adhésion
     */
    public function demandesAdhesion(Request $request)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $query = DemandeAdhesion::with(['user', 'client', 'entreprise']);

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('type_demandeur')) {
            $query->where('type_demandeur', $request->type_demandeur);
        }

        if ($request->has('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $demandes = $query->paginate($request->get('per_page', 10));

        return ApiResponse::success($demandes, 'Liste des demandes d\'adhésion récupérée avec succès');
    }

    /**
     * Valider une demande d'adhésion
     */
    public function validerDemande(Request $request, $id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $validated = $request->validate([
            'motif_validation' => 'nullable|string|max:500',
            'notes_techniques' => 'nullable|string|max:1000',
        ]);

        $demande = DemandeAdhesion::find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        if ($demande->statut !== StatutDemandeAdhesionEnum::EN_ATTENTE) {
            return ApiResponse::error('Cette demande ne peut plus être validée', 400);
        }

        $demande->update([
            'statut' => StatutDemandeAdhesionEnum::VALIDEE,
            'valide_par_id' => $technicien->id,
            'valide_a' => now(),
            'motif_validation' => $validated['motif_validation'],
            'notes_techniques' => $validated['notes_techniques'],
        ]);

        // Notifier le client via l'application
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->createNotification(
            $demande->user->id,
            'Demande d\'adhésion validée',
            "Votre demande d'adhésion a été validée par notre équipe technique. Vous pouvez maintenant procéder à la souscription.",
            'demande_validee',
            [
                'demande_id' => $demande->id,
                'valide_par' => $technicien->nom . ' ' . ($technicien->prenoms ?? ''),
                'date_validation' => now()->format('d/m/Y à H:i'),
                'motif_validation' => $validated['motif_validation'] ?? null,
                'type' => 'demande_validee'
            ]
        );

        // Envoyer l'email
        dispatch(new \App\Jobs\SendEmailJob(
            $demande->user->email,
            'Demande d\'adhésion validée',
            \App\Enums\EmailType::ACCEPTED->value,
            [
                'demande' => $demande,
                'technicien' => $technicien,
            ]
        ));

        return ApiResponse::success($demande, 'Demande d\'adhésion validée avec succès');
    }

    /**
     * Rejeter une demande d'adhésion
     */
    public function rejeterDemande(Request $request, $id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $validated = $request->validate([
            'motif_rejet' => 'required|string|max:500',
            'notes_techniques' => 'nullable|string|max:1000',
        ]);

        $demande = DemandeAdhesion::find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        if ($demande->statut !== StatutDemandeAdhesionEnum::EN_ATTENTE) {
            return ApiResponse::error('Cette demande ne peut plus être rejetée', 400);
        }

        $demande->update([
            'statut' => StatutDemandeAdhesionEnum::REJETEE,
            'rejetee_par_id' => $technicien->id,
            'rejetee_a' => now(),
            'motif_rejet' => $validated['motif_rejet'],
            'notes_techniques' => $validated['notes_techniques'],
        ]);

        // Notifier le client via l'application
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->createNotification(
            $demande->user->id,
            'Demande d\'adhésion rejetée',
            "Votre demande d'adhésion a été rejetée. Consultez votre email pour plus de détails.",
            'demande_rejetee',
            [
                'demande_id' => $demande->id,
                'motif_rejet' => $validated['motif_rejet'],
                'rejetee_par' => $technicien->nom . ' ' . ($technicien->prenoms ?? ''),
                'date_rejet' => now()->format('d/m/Y à H:i'),
                'type' => 'demande_rejetee'
            ]
        );

        // Envoyer l'email
        dispatch(new \App\Jobs\SendEmailJob(
            $demande->user->email,
            'Demande d\'adhésion rejetée',
            \App\Enums\EmailType::REJETEE->value,
            [
                'demande' => $demande,
                'technicien' => $technicien,
            ]
        ));

        return ApiResponse::success($demande, 'Demande d\'adhésion rejetée avec succès');
    }

    /**
     * Proposer un contrat
     */
    public function proposerContrat(Request $request, $id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $validated = $request->validate([
            'type_contrat' => 'required|string|in:decouverte,standard,premium',
            'prime_standard' => 'required|numeric|min:0',
            'frais_gestion' => 'required|numeric|min:0|max:100',
            'commission_commercial' => 'required|numeric|min:0|max:100',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'garanties' => 'required|array',
            'garanties.*' => 'exists:garanties,id',
            'notes_techniques' => 'nullable|string|max:1000',
        ]);

        $demande = DemandeAdhesion::find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        if ($demande->statut !== StatutDemandeAdhesionEnum::VALIDEE) {
            return ApiResponse::error('Cette demande doit être validée avant de proposer un contrat', 400);
        }

        // Créer le contrat
        $contrat = DB::transaction(function () use ($validated, $technicien, $demande) {
            $contrat = \App\Models\Contrat::create([
                'numero_police' => \App\Models\Contrat::generateNumeroPolice(),
                'type_contrat' => $validated['type_contrat'],
                'prime_standard' => $validated['prime_standard'],
                'frais_gestion' => $validated['frais_gestion'],
                'commission_commercial' => $validated['commission_commercial'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'technicien_id' => $technicien->id,
                'statut' => 'propose',
                'est_actif' => false,
                'notes_techniques' => $validated['notes_techniques'],
            ]);

            // Attacher les garanties
            $contrat->categoriesGaranties()->attach($validated['garanties']);

            return $contrat;
        });

        // Notifier le client via l'application
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->createNotification(
            $demande->user->id,
            'Proposition de contrat reçue',
            "Un technicien a analysé votre demande et vous propose un contrat d'assurance. Consultez votre email pour les détails.",
            'contrat_propose',
            [
                'demande_id' => $demande->id,
                'contrat_id' => $contrat->id,
                'type_contrat' => $contrat->type_contrat,
                'prime_standard' => $contrat->prime_standard,
                'propose_par' => $technicien->nom . ' ' . ($technicien->prenoms ?? ''),
                'date_proposition' => now()->format('d/m/Y à H:i'),
                'type' => 'contrat_propose'
            ]
        );

        // Envoyer l'email
        dispatch(new \App\Jobs\SendEmailJob(
            $demande->user->email,
            'Votre proposition de contrat d\'assurance',
            \App\Enums\EmailType::CONTRAT_PRET->value,
            [
                'demande' => $demande,
                'contrat' => $contrat,
                'technicien' => $technicien,
            ]
        ));

        return ApiResponse::success($contrat, 'Contrat proposé avec succès');
    }

    /**
     * Liste des factures à valider
     */
    public function factures(Request $request)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $query = Facture::with(['prestataire', 'assure']);

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

        $factures = $query->paginate($request->get('per_page', 10));

        return ApiResponse::success($factures, 'Liste des factures récupérée avec succès');
    }

    /**
     * Valider une facture
     */
    public function validerFacture(Request $request, $id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $validated = $request->validate([
            'notes_validation' => 'nullable|string|max:500',
        ]);

        $facture = Facture::find($id);

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        if ($facture->statut !== StatutFactureEnum::EN_ATTENTE) {
            return ApiResponse::error('Cette facture ne peut plus être validée', 400);
        }

        $facture->update([
            'statut' => StatutFactureEnum::VALIDEE_TECHNICIEN,
            'technicien_id' => $technicien->id,
            'valide_par_technicien_a' => now(),
            'notes_technicien' => $validated['notes_validation'],
        ]);

        return ApiResponse::success($facture, 'Facture validée avec succès');
    }

    /**
     * Rejeter une facture
     */
    public function rejeterFacture(Request $request, $id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $validated = $request->validate([
            'motif_rejet' => 'required|string|max:500',
        ]);

        $facture = Facture::find($id);

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        if ($facture->statut !== StatutFactureEnum::EN_ATTENTE) {
            return ApiResponse::error('Cette facture ne peut plus être rejetée', 400);
        }

        $facture->update([
            'statut' => StatutFactureEnum::REJETEE,
            'rejetee_par_id' => $technicien->id,
            'rejetee_a' => now(),
            'motif_rejet_technicien' => $validated['motif_rejet'],
        ]);

        return ApiResponse::success($facture, 'Facture rejetée avec succès');
    }

    /**
     * Détails d'une demande d'adhésion
     */
    public function showDemande($id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $demande = $this->loadDemandeWithRelations($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        return ApiResponse::success($this->prepareDemandeData($demande), 'Demande d\'adhésion récupérée avec succès');
    }

    /**
     * Détails d'une facture
     */
    public function showFacture($id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $facture = Facture::with([
            'prestataire', 
            'assure', 
            'technicien', 
            'medecin'
        ])->find($id);

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        return ApiResponse::success($facture, 'Facture récupérée avec succès');
    }
} 