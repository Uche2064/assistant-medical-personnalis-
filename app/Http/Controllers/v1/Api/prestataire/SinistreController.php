<?php

namespace App\Http\Controllers\v1\Api\prestataire;

use App\Enums\StatutFactureEnum;
use App\Enums\StatutSinistreEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Assure;
use App\Models\ClientContrat;
use App\Models\ClientPrestataire;
use App\Models\Contrat;
use App\Models\Facture;
use App\Models\Garantie;
use App\Models\LigneFacture;
use App\Models\Personnel;
use App\Models\Sinistre;
use App\Notifications\NouvelleFactureNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SinistreController extends Controller
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Afficher la liste des sinistres du prestataire
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return ApiResponse::error('Vous n\'êtes pas un prestataire', 403);
        }

        $perPage = $request->input('per_page', 10);

        $sinistres = Sinistre::query()
            ->where('prestataire_id', $prestataire->id)
            ->with(['assure.user', 'assure.contrat', 'factures'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('assure', function ($q) use ($request) {
                    $q->where('nom', 'like', '%' . $request->search . '%')
                        ->orWhere('prenoms', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('statut'), function ($query) use ($request) {
                $query->where('statut', $request->statut);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ApiResponse::success($sinistres, 'Liste des sinistres récupérée avec succès');
    }

    /**
     * Rechercher des assurés pour créer un sinistre
     * Seuls les assurés assignés au prestataire connecté sont retournés
     */
    public function searchAssures(Request $request)
    {
        $search = $request->input('search', '');

        $user = Auth::user();
        $prestataire = $user->prestataire;


        // Récupérer les client_contrats assignés à ce prestataire
        $clientContratsAssignes = ClientPrestataire::where('prestataire_id', $prestataire->id)
            ->where('statut', 'ACTIF')
            ->with(['clientContrat' => function ($query) {
                $query->where('statut', 'ACTIF')
                    ->where('date_debut', '<=', now())
                    ->where('date_fin', '>=', now());
            }])
            ->get()
            ->pluck('clientContrat')
            ->filter() // Enlever les null
            ->pluck('id')
            ->toArray();

        if (empty($clientContratsAssignes)) {
            return ApiResponse::success([], 'Aucun client ne vous est assigné');
        }

        // Récupérer les IDs des clients et contrats
        $clientContrats = ClientContrat::whereIn('id', $clientContratsAssignes)->get();
        $clientIds = $clientContrats->pluck('client_id')->toArray();
        $contratIds = $clientContrats->pluck('contrat_id')->toArray();

        // Rechercher les assurés correspondants
        $assures = collect();

        // 1. Rechercher les assurés directs (clients physiques)
        $assuresDirects = Assure::query()
            ->with(['user', 'contrat', 'entreprise', 'assurePrincipal'])
            ->whereHas('user', function ($query) use ($clientIds) {
                $query->whereIn('id', $clientIds);
            })
            ->whereIn('contrat_id', $contratIds)
            ->where(function ($query) use ($search) {
                $query->where('nom', 'like', '%' . $search . '%')
                    ->orWhere('prenoms', 'like', '%' . $search . '%');
            })
            ->limit(10)
            ->get();

        $assures = $assures->merge($assuresDirects);

        // 2. Rechercher les employés d'entreprises assignées
        $assuresEntreprises = Assure::query()
            ->with(['user', 'contrat', 'entreprise', 'assurePrincipal'])
            ->whereHas('entreprise.user', function ($query) use ($clientIds) {
                $query->whereIn('id', $clientIds);
            })
            ->where(function ($query) use ($search) {
                $query->where('nom', 'like', '%' . $search . '%')
                    ->orWhere('prenoms', 'like', '%' . $search . '%');
            })
            ->limit(10)
            ->get();

        $assures = $assures->merge($assuresEntreprises);

        // Formatter les résultats
        $assuresFormates = $assures->unique('id')->take(10)->map(function ($assure) {
            // Déterminer le type d'assuré
            $typeAssure = 'Inconnu';
            if ($assure->entreprise_id) {
                $typeAssure = 'Employé - ' . ($assure->entreprise->raison_sociale ?? 'Entreprise');
            } elseif ($assure->est_principal) {
                $typeAssure = 'Assuré Principal';
            } elseif ($assure->assure_principal_id) {
                $typeAssure = 'Bénéficiaire';
            }

            // Pour les bénéficiaires, récupérer le contrat du principal
            $contrat = $assure->contrat;
            if (!$contrat && $assure->assure_principal_id) {
                $contrat = $assure->assurePrincipal->contrat;
            }

            return [
                'id' => $assure->id,
                'nom' => $assure->nom,
                'prenoms' => $assure->prenoms,
                'email' => $assure->email,
                'contact' => $assure->contact,
                'type_assure' => $typeAssure,
                'est_principal' => $assure->est_principal,
                'contrat' => $contrat ? [
                    'id' => $contrat->id,
                    'libelle' => $contrat->libelle,
                    'est_actif' => $contrat->est_actif,
                    'statut' => $contrat->statut,
                ] : null,
            ];
        });

        return ApiResponse::success($assuresFormates, 'Assurés assignés trouvés');
    }

    /**
     * Récupérer les garanties disponibles pour un contrat
     */
    public function getGarantiesByContrat($contratId)
    {
        $contrat = Contrat::with(['categoriesGaranties.garanties'])
            ->find($contratId);

        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }

        if (!$contrat->isActive()) {
            return ApiResponse::error('Le contrat n\'est pas actif', 400);
        }

        $garanties = [];
        foreach ($contrat->categoriesGaranties as $categorie) {
            foreach ($categorie->garanties as $garantie) {
                $garanties[] = [
                    'id' => $garantie->id,
                    'libelle' => $garantie->libelle,
                    'prix_standard' => $garantie->prix_standard,
                    'taux_couverture' => $garantie->taux_couverture,
                    'plafond' => $garantie->plafond,
                    'categorie' => [
                        'id' => $categorie->id,
                        'libelle' => $categorie->libelle,
                        'couverture' => $categorie->pivot->couverture ?? $garantie->taux_couverture,
                    ],
                ];
            }
        }

        return ApiResponse::success($garanties, 'Garanties récupérées avec succès');
    }

    /**
     * Créer un nouveau sinistre (Étape 1)
     */
    public function store(Request $request)
    {
        $request->validate([
            'assure_id' => 'required|exists:assures,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return ApiResponse::error('Vous n\'êtes pas un prestataire', 403);
        }

        // Vérifier que l'assuré est assigné au prestataire et a un contrat actif
        $assure = Assure::with('contrat', 'assurePrincipal.contrat', 'user', 'entreprise.user')->find($request->assure_id);

        if (!$assure) {
            return ApiResponse::error('Assuré non trouvé', 404);
        }

        // Vérifier l'assignation du prestataire à cet assuré
        $isAssigned = false;

        // Cas 1: Assuré principal (physique ou entreprise)
        if ($assure->est_principal) {
            // Pour les clients physiques
            if ($assure->user) {
                $isAssigned = ClientPrestataire::whereHas('clientContrat', function ($query) use ($assure) {
                    $query->where('user_id', $assure->user->id)
                        ->where('statut', 'actif')
                        ->where('date_debut', '<=', now())
                        ->where('date_fin', '>=', now());
                })->where('prestataire_id', $prestataire->id)
                    ->where('statut', 'actif')
                    ->exists();
            }

            // Pour les employés d'entreprise
            if (!$isAssigned && $assure->entreprise && $assure->entreprise->user) {
                $isAssigned = ClientPrestataire::whereHas('clientContrat', function ($query) use ($assure) {
                    $query->where('user_id', $assure->entreprise->user->id)
                        ->where('statut', 'actif')
                        ->where('date_debut', '<=', now())
                        ->where('date_fin', '>=', now());
                })->where('prestataire_id', $prestataire->id)
                    ->where('statut', 'actif')
                    ->exists();
            }
        }
        // Cas 2: Bénéficiaire - vérifier l'assignation de l'assuré principal
        else if ($assure->assure_principal_id) {
            $assurePrincipal = Assure::with('user', 'entreprise.user')->find($assure->assure_principal_id);

            if ($assurePrincipal) {
                // Vérifier l'assignation de l'assuré principal
                if ($assurePrincipal->user) {
                    $isAssigned = ClientPrestataire::whereHas('clientContrat', function ($query) use ($assurePrincipal) {
                        $query->where('user_id', $assurePrincipal->user->id)
                            ->where('statut', 'actif')
                            ->where('date_debut', '<=', now())
                            ->where('date_fin', '>=', now());
                    })->where('prestataire_id', $prestataire->id)
                        ->where('statut', 'actif')
                        ->exists();
                }

                // Pour les employés d'entreprise (assurés principaux)
                if (!$isAssigned && $assurePrincipal->entreprise && $assurePrincipal->entreprise->user) {
                    $isAssigned = ClientPrestataire::whereHas('clientContrat', function ($query) use ($assurePrincipal) {
                        $query->where('user_id', $assurePrincipal->entreprise->user->id)
                            ->where('statut', 'actif')
                            ->where('date_debut', '<=', now())
                            ->where('date_fin', '>=', now());
                    })->where('prestataire_id', $prestataire->id)
                        ->where('statut', 'actif')
                        ->exists();
                }
            }
        }

        if (!$isAssigned) {
            return ApiResponse::error('Cet assuré ne vous est pas assigné', 403);
        }



        if (!$assure->hasContratActif()) {
            return ApiResponse::error('L\'assuré n\'a pas de contrat actif', 400);
        }

        try {
            DB::beginTransaction();

            $sinistre = Sinistre::create([
                'assure_id' => $request->assure_id,
                'prestataire_id' => $prestataire->id,
                'description' => $request->description,
                'date_sinistre' => now(),
                'statut' => StatutSinistreEnum::EN_COURS->value,
            ]);

            DB::commit();

            $sinistre->load(['assure.user', 'assure.contrat']);

            return ApiResponse::success($sinistre, 'Sinistre créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création sinistre: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la création du sinistre', 500);
        }
    }

    /**
     * Créer une facture pour un sinistre (Étape 2)
     */
    public function createFacture(Request $request, $sinistreId)
    {
        $request->validate([
            'diagnostic' => 'required|string|max:1000',
            'lignes_facture' => 'required|array|min:1',
            'lignes_facture.*.garantie_id' => 'required|exists:garanties,id',
            'lignes_facture.*.libelle_acte' => 'required|string|max:255',
            'lignes_facture.*.quantite' => 'required|integer|min:1',
            'photo_justificatifs' => 'nullable|array',
            'photo_justificatifs.*' => 'string', // URLs des photos uploadées
        ]);

        $user = Auth::user();

        $sinistre = Sinistre::where('id', $sinistreId)
            ->where('prestataire_id', $user->id)
            ->with(['assure.contrat', 'assure.assurePrincipal.contrat'])
            ->first();

        if (!$sinistre) {
            return ApiResponse::error('Sinistre non trouvé', 404);
        }

        // Récupérer le contrat (principal ou bénéficiaire)
        $assure = $sinistre->assure;

        if (!$assure->hasContratActif()) {
            return ApiResponse::error('L\'assuré n\'a pas de contrat actif', 400);
        }

        try {
            DB::beginTransaction();

            // Calculer les montants totaux
            $montantTotal = 0;
            $montantCouvert = 0;
            $ticketModerateur = 0;

            $lignesFactureData = [];

            foreach ($request->lignes_facture as $ligneData) {
                $garantie = Garantie::find($ligneData['garantie_id']);

                if (!$garantie) {
                    throw new \Exception("Garantie non trouvée: {$ligneData['garantie_id']}");
                }

                $prixUnitaire = $garantie->prix_standard;
                $quantite = $ligneData['quantite'];
                $prixTotal = $prixUnitaire * $quantite;
                $tauxCouverture = $garantie->taux_couverture;
                $montantLigneCouvert = $prixTotal * ($tauxCouverture / 100);
                $ticketModérateurLigne = $prixTotal - $montantLigneCouvert;

                $lignesFactureData[] = [
                    'garantie_id' => $garantie->id,
                    'libelle_acte' => $ligneData['libelle_acte'],
                    'prix_unitaire' => $prixUnitaire,
                    'quantite' => $quantite,
                    'prix_total' => $prixTotal,
                    'taux_couverture' => $tauxCouverture,
                    'montant_couvert' => $montantLigneCouvert,
                    'ticket_moderateur' => $ticketModérateurLigne,
                ];

                $montantTotal += $prixTotal;
                $montantCouvert += $montantLigneCouvert;
                $ticketModerateur += $ticketModérateurLigne;
            }

            // Créer la facture
            $numeroFacture = 'FAC-' . date('Y') . '-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) . '-' . str_pad(Facture::count() + 1, 6, '0', STR_PAD_LEFT);

            $facture = Facture::create([
                'numero_facture' => $numeroFacture,
                'sinistre_id' => $sinistre->id,
                'prestataire_id' => $user->id,
                'montant_reclame' => $montantTotal,
                'montant_a_rembourser' => $montantCouvert,
                'diagnostic' => $request->diagnostic,
                'photo_justificatifs' => $request->photo_justificatifs ?? [],
                'ticket_moderateur' => $ticketModerateur,
                'statut' => StatutFactureEnum::EN_ATTENTE,
            ]);

            // Créer les lignes de facture
            foreach ($lignesFactureData as $ligneData) {
                $facture->lignesFacture()->create($ligneData);
            }

            // Mettre à jour le statut du sinistre
            $sinistre->updateStatus(StatutSinistreEnum::CLOTURE->value);

            // Envoyer notification aux techniciens
            $techniciens = Personnel::with('user')
                ->whereHas('user', function ($userQuery) {
                    $userQuery->whereNotNull('email_verified_at')
                        ->whereHas('roles', function ($roleQuery) {
                            $roleQuery->where('name', 'technicien');
                        });
                })
                ->get();

            foreach ($techniciens as $technicien) {
                // Envoyer notification aux techniciens
                $this->notificationService->createNotification(
                    $technicien->user->id,
                    'Sinistre cloturé avec succès',
                    "Le sinistre a été cloturé avec succès. Veuillez vérifier la facture et valider",
                    'sinistre_cloture',
                    [
                        'sinistre_id' => $sinistre->id,
                        'type' => 'sinistre_cloture',
                        'prestataire_id' => $user->id,
                        'assure_id' => $sinistre->assure_id,
                        'montant_reclame' => $sinistre->montant_reclame,
                        'montant_a_rembourser' => $sinistre->montant_a_rembourser,
                        'ticket_moderateur' => $sinistre->ticket_moderateur,
                        'diagnostic' => $sinistre->diagnostic,
                    ]
                );
            }


            DB::commit();

            $facture->load(['lignesFacture.garantie', 'sinistre.assure']);

            return ApiResponse::success($facture, 'Facture créée avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création facture: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la création de la facture: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Afficher un sinistre spécifique
     */
    public function show($id)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        $sinistre = Sinistre::where('assure_id', $id)
            ->where('prestataire_id', $prestataire->id)
            ->where('statut', StatutSinistreEnum::EN_COURS->value)
            ->with(['assure.user', 'assure.contrat', 'factures.lignesFacture.garantie'])
            ->first();

        Log::info($sinistre);

        if (!$sinistre) {
            return ApiResponse::error('Sinistre non trouvé', 404);
        }

        return ApiResponse::success($sinistre, 'Sinistre récupéré avec succès');
    }

    public function existingSinistre($id)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        $sinistre = Sinistre::where('assure_id', $id)
            ->where('prestataire_id', $prestataire->id)
            ->where('statut', StatutSinistreEnum::EN_COURS->value)
            ->with(['assure.user', 'assure.contrat', 'factures.lignesFacture.garantie'])
            ->first();

        $data = [
            'existing' => (bool) $sinistre,
            'statut' => $sinistre->statut->value ?? null,
            'sinistre' => $sinistre,
        ];

        return ApiResponse::success($data, 'Sinistre récupéré avec succès');
    }
}
