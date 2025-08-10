<?php

namespace App\Http\Controllers\v1\Api\statistiques;

use App\Enums\RoleEnum;
use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Assure;
use App\Models\Client;
use App\Models\DemandeAdhesion;
use App\Models\Garantie;
use App\Models\CategorieGarantie;
use App\Models\Personnel;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatsController extends Controller
{
    /**
     * Statistiques adaptées au rôle de l'utilisateur connecté
     */
    public function dashboardStats()
    {
        $user = Auth::user();
        $stats = [];

        // Admin Global - Accès à tout
        if ($user->hasRole(RoleEnum::ADMIN_GLOBAL->value)) {
            $stats = array_merge($stats, [
                'gestionnaires' => $this->getGestionnaireStats(),
                'personnels' => $this->getPersonnelStats(),
                'clients' => $this->getClientStats(),
                'assures' => $this->getAssureStats(),
                'demandes_adhesion' => $this->getDemandeAdhesionStats(),
                'questions' => $this->getQuestionStats(),
                'garanties' => $this->getGarantieStats(),
                'categories_garanties' => $this->getCategorieGarantieStats(),
            ]);
        }
        // Gestionnaire - Accès aux personnels et clients
        elseif ($user->hasRole(RoleEnum::GESTIONNAIRE->value)) {
            $stats = array_merge($stats, [
                'personnels' => $this->getPersonnelStats(),
                'clients' => $this->getClientStats(),
            ]);
        }
        // Technicien - Accès aux clients, assurés, demandes physique/moral, garanties
        elseif ($user->hasRole(RoleEnum::TECHNICIEN->value)) {
            $stats = array_merge($stats, [
                'clients' => $this->getClientStats(),
                'assures' => $this->getAssureStats(),
                'demandes_adhesion' => $this->getDemandeAdhesionStats(['physique', 'autre']), // Seulement physique et moral
                'garanties' => $this->getGarantieStats(),
                'categories_garanties' => $this->getCategorieGarantieStats(),
            ]);
        }
        // Médecin Contrôleur - Accès aux clients, assurés, questions, garanties, demandes prestataires
        elseif ($user->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value)) {
            $stats = array_merge($stats, [
                'clients' => $this->getClientStats(),
                'assures' => $this->getAssureStats(),
                'questions' => $this->getQuestionStats(),
                'garanties' => $this->getGarantieStats(),
                'categories_garanties' => $this->getCategorieGarantieStats(),
                'demandes_adhesion' => $this->getDemandeAdhesionStats([
                    TypeDemandeurEnum::CENTRE_DE_SOINS->value,
                    TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC->value,
                    TypeDemandeurEnum::PHARMACIE->value,
                    TypeDemandeurEnum::OPTIQUE->value,
                ]), // Seulement prestataires
            ]);
        }

        return ApiResponse::success($stats, 'Statistiques du dashboard récupérées avec succès');
    }

    /**
     * Statistiques des gestionnaires
     */
    private function getGestionnaireStats()
    {
        return [
            'total' => Personnel::whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                });
            })->count(),
            
            'actifs' => Personnel::whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                })->where('est_actif', true);
            })->count(),
            
            'inactifs' => Personnel::whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                })->where('est_actif', false);
            })->count(),
            
            'repartition_par_sexe' => Personnel::whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                });
            })
            ->selectRaw('sexe, COUNT(*) as count')
            ->groupBy('sexe')
            ->get()
            ->mapWithKeys(function ($item) {
                return [($item->sexe !== null ? (string) $item->sexe : 'Non spécifié') => $item->count];
            }),
        ];
    }

    /**
     * Statistiques des personnels
     */
    private function getPersonnelStats()
    {
        $currentUser = Auth::user();
        $query = Personnel::query();

        // Si c'est un gestionnaire, filtrer par gestionnaire_id
        if ($currentUser->hasRole(RoleEnum::GESTIONNAIRE->value)) {
            $query->where('gestionnaire_id', $currentUser->personnel->id);
        }

        return [
            'total' => $query->count(),
            
            'actifs' => $query->clone()->whereHas('user', function ($q) {
                $q->where('est_actif', true);
            })->count(),
            
            'inactifs' => $query->clone()->whereHas('user', function ($q) {
                $q->where('est_actif', false);
            })->count(),
            
            'repartition_par_role' => $query->clone()
                ->whereHas('user.roles')
                ->with('user.roles')
                ->get()
                ->groupBy(function ($personnel) {
                    return $personnel->user->roles->first()->name ?? 'Aucun rôle';
                })
                ->map(function ($group) {
                    return $group->count();
                }),
            
            'repartition_par_sexe' => $query->clone()
                ->selectRaw('sexe, COUNT(*) as count')
                ->groupBy('sexe')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [($item->sexe !== null ? (string) $item->sexe : 'Non spécifié') => $item->count];
                }),
        ];
    }

    /**
     * Statistiques des assurés
     */
    public function getAssureStats()
    {
        $data = [
            'total' => Assure::count(),
            
            'principaux' => Assure::where('est_principal', true)->count(),
            
            'beneficiaires' => Assure::where('est_principal', false)->count(),
            
            'repartition_par_sexe' => Assure::selectRaw('sexe, COUNT(*) as count')
                ->groupBy('sexe')
                ->get()
                ->mapWithKeys(function ($item) {
                    // CORRECTION: Gérer l'enum sexe
                    $sexe = $item->sexe;
                    if (is_object($sexe) && method_exists($sexe, 'value')) {
                        $sexe = $sexe->value;
                    }
                    return [($sexe !== null ? $sexe->value : 'Non spécifié') => $item->count];
                }),
            
            'repartition_par_lien_parente' => Assure::selectRaw('lien_parente, COUNT(*) as count')
                ->groupBy('lien_parente')
                ->get()
                ->mapWithKeys(function ($item) {
                    // CORRECTION: Gérer l'enum lien_parente
                    $lienParente = $item->lien_parente;
                    if (is_object($lienParente) && method_exists($lienParente, 'value')) {
                        $lienParente = $lienParente->value;
                    }
                    return [($lienParente !== null ? $lienParente->value : 'Non spécifié') => $item->count];
                }),
            
            'repartition_principaux_beneficiaires' => [
                'principaux' => Assure::where('est_principal', true)->count(),
                'beneficiaires' => Assure::where('est_principal', false)->count(),
            ],
            
            'repartition_par_contrat' => Assure::selectRaw('contrat_id, COUNT(*) as count')
                ->whereNotNull('contrat_id')
                ->groupBy('contrat_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return ["Contrat " . ($item->contrat_id !== null ? (string) $item->contrat_id : 'Non spécifié') => $item->count];
                }),
        ];
        return ApiResponse::success($data, 'Statistiques des assurés récupérées avec succès');
    }

    /**
     * Statistiques des demandes d'adhésion
     */
    private function getDemandeAdhesionStats($types = null)
    {
        $query = DemandeAdhesion::query();
        
        if ($types) {
            $query->whereIn('type_demandeur', $types);
        }

        return [
            'total' => $query->count(),
            
            'en_attente' => $query->clone()->where('statut', 'en_attente')->count(),
            
            'validees' => $query->clone()->where('statut', 'validee')->count(),
            
            'rejetees' => $query->clone()->where('statut', 'rejetee')->count(),
            
            'repartition_par_type_demandeur' => $query->clone()
                ->selectRaw('type_demandeur, COUNT(*) as count')
                ->groupBy('type_demandeur')
                ->get()
                ->mapWithKeys(function ($item) {
                    // CORRECTION: Gérer l'enum type_demandeur
                    $typeDemandeur = $item->type_demandeur;
                    if (is_object($typeDemandeur) && method_exists($typeDemandeur, 'value')) {
                        $typeDemandeur = $typeDemandeur->value;
                    } elseif (is_string($typeDemandeur)) {
                        $typeDemandeur = $typeDemandeur;
                    } else {
                        $typeDemandeur = 'Non spécifié';
                    }
                    return [($typeDemandeur !== null ? (string) $typeDemandeur : 'Non spécifié') => $item->count];
                }),
            
            'repartition_par_statut' => $query->clone()
                ->selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->get()
                ->mapWithKeys(function ($item) {
                    // CORRECTION: Gérer le statut (qui peut être un enum ou une string)
                    $statut = $item->statut;
                    if (is_object($statut) && method_exists($statut, 'value')) {
                        $statut = $statut->value;
                    } elseif (is_string($statut)) {
                        $statut = $statut;
                    } else {
                        $statut = 'Non spécifié';
                    }
                    return [($statut !== null ? (string) $statut : 'Non spécifié') => $item->count];
                }),
            
            'repartition_statut_par_type' => $query->clone()
                ->selectRaw('type_demandeur, statut, COUNT(*) as count')
                ->groupBy('type_demandeur', 'statut')
                ->get()
                ->groupBy(function ($item) {
                    // CORRECTION: Gérer l'enum pour le groupBy
                    $typeDemandeur = $item->type_demandeur;
                    if (is_object($typeDemandeur) && method_exists($typeDemandeur, 'value')) {
                        $typeDemandeur = $typeDemandeur->value;
                    } elseif (is_string($typeDemandeur)) {
                        $typeDemandeur = $typeDemandeur;
                    } else {
                        $typeDemandeur = 'Non spécifié';
                    }
                    return $typeDemandeur;
                })
                ->map(function ($group) {
                    return $group->mapWithKeys(function ($item) {
                        // CORRECTION: Gérer l'enum statut
                        $statut = $item->statut;
                        if (is_object($statut) && method_exists($statut, 'value')) {
                            $statut = $statut->value;
                        } elseif (is_string($statut)) {
                            $statut = $statut;
                        } else {
                            $statut = 'Non spécifié';
                        }
                        return [($statut !== null ? (string) $statut : 'Non spécifié') => $item->count];
                    });
                }),
            
            'demandes_par_mois' => $query->clone()
                ->selectRaw('MONTH(created_at) as mois, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy('mois')
                ->orderBy('mois')
                ->get()
                ->mapWithKeys(function ($item) {
                    $mois = [
                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                    ];
                    return [$mois[(int) $item->mois] ?? "Mois " . ((int) $item->mois) => $item->count];
                }),
        ];
    }

    /**
     * Statistiques des questions
     */
    private function getQuestionStats()
    {
        return [
            'total' => Question::count(),
            
            'actives' => Question::where('est_actif', true)->count(),
            
            'inactives' => Question::where('est_actif', false)->count(),
            
            'obligatoires' => Question::where('obligatoire', true)->count(),
            
            'optionnelles' => Question::where('obligatoire', false)->count(),
            
            'repartition_par_destinataire' => Question::selectRaw('destinataire, COUNT(*) as count')
                ->groupBy('destinataire')
                ->get()
                ->mapWithKeys(function ($item) {
                    // CORRECTION: Gérer l'enum destinataire
                    $destinataire = $item->destinataire;
                    if (is_object($destinataire) && method_exists($destinataire, 'value')) {
                        $destinataire = $destinataire->value;
                    } elseif (is_string($destinataire)) {
                        $destinataire = $destinataire;
                    } else {
                        $destinataire = 'Non spécifié';
                    }
                    return [($destinataire !== null ? (string) $destinataire : 'Non spécifié') => $item->count];
                }),
            
            'repartition_par_type_donnee' => Question::selectRaw('type_donnee, COUNT(*) as count')
                ->groupBy('type_donnee')
                ->get()
                ->mapWithKeys(function ($item) {
                    // CORRECTION: Gérer l'enum type_donnee
                    $typeDonnee = $item->type_donnee;
                    if (is_object($typeDonnee) && method_exists($typeDonnee, 'value')) {
                        $typeDonnee = $typeDonnee->value;
                    } elseif (is_string($typeDonnee)) {
                        $typeDonnee = $typeDonnee;
                    } else {
                        $typeDonnee = 'Non spécifié';
                    }
                    return [($typeDonnee !== null ? (string) $typeDonnee : 'Non spécifié') => $item->count];
                }),
            
            'repartition_obligatoire_par_destinataire' => Question::selectRaw('destinataire, obligatoire, COUNT(*) as count')
                ->groupBy('destinataire', 'obligatoire')
                ->get()
                ->groupBy(function ($item) {
                    // CORRECTION: Gérer l'enum pour le groupBy
                    $destinataire = $item->destinataire;
                    if (is_object($destinataire) && method_exists($destinataire, 'value')) {
                        $destinataire = $destinataire->value;
                    } elseif (is_string($destinataire)) {
                        $destinataire = $destinataire;
                    } else {
                        $destinataire = 'Non spécifié';
                    }
                    return $destinataire;
                })
                ->map(function ($group) {
                    return $group->mapWithKeys(function ($item) {
                        return [($item->obligatoire ? 'obligatoires' : 'optionnelles') => $item->count];
                    });
                }),
        ];
    }

    /**
     * Statistiques des garanties
     */
    private function getGarantieStats()
    {
        return [
            'total' => Garantie::count(),
            
            'repartition_par_categorie' => Garantie::selectRaw('categorie_garantie_id, COUNT(*) as count')
                ->groupBy('categorie_garantie_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return ["Catégorie " . ($item->categorie_garantie_id !== null ? (string) $item->categorie_garantie_id : 'Non spécifié') => $item->count];
                }),
        ];
    }

    /**
     * Statistiques des catégories de garanties
     */
    private function getCategorieGarantieStats()
    {
        return [
            'total' => CategorieGarantie::count(),
            
            'repartition_par_garanties' => CategorieGarantie::withCount('garanties')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [($item->libelle !== null ? (string) $item->libelle : 'Non spécifié') => $item->garanties_count];
                }),
        ];
    }
}