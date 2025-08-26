<?php

namespace App\Http\Controllers\v1\Api\Common;

use App\Helpers\ApiResponse;
use App\Helpers\CommonHelpers;
use App\Http\Controllers\Controller;
use App\Models\DemandeAdhesion;
use App\Models\Facture;
use App\Models\Sinistre;
use App\Models\Assure;
use App\Models\ClientPrestataire;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadFileController extends Controller
{
    public function downloadDemandeAdhesion($id)
    {
        $demande = DemandeAdhesion::with([
            'user',
            'user.entreprise',
            'user.prestataire',
            'user.assure',
            'validePar', // validePar est déjà un Personnel
            'reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'reponsesQuestionnaire.question',
            'assures' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'assures.reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'assures.reponsesQuestionnaire.question',
            'employes' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'employes.reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'employes.reponsesQuestionnaire.question',
            'assures.beneficiaires' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'assures.beneficiaires.reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'assures.beneficiaires.reponsesQuestionnaire.question'
        ])->find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        // Calculer les statistiques
        $statistiques = CommonHelpers::calculerStatistiquesDemande($demande);

        // Préparer les données pour le PDF
        $data = [
            'demande' => $demande,
            'baseUrl' => url('/'), // URL de base pour les fichiers
            'statistiques' => $statistiques,
        ];

        // Choisir le template selon le type de demandeur
        $template = CommonHelpers::getTemplateByDemandeurType($demande->type_demandeur);

        // Générez le PDF
        $pdf = Pdf::loadView($template, $data);

        // Retournez le PDF en téléchargement
        return $pdf->download("{$demande->nom}-{$demande->prenoms}.pdf");
    }


    public function downloadFacture($id)
    {
        try {
            $user = Auth::user();
            
            // Récupérer la facture avec toutes ses relations
            $facture = Facture::with([
                'sinistre.assure.user',
                'sinistre.assure.entreprise.user',
                'sinistre.assure.assurePrincipal.user',
                'sinistre.assure.assurePrincipal.entreprise.user',
                'prestataire',
                'lignesFacture.garantie',
                'technicien',
                'medecin',
                'comptable'
            ])->where('numero_facture', $id)->first();

            // Vérifier les permissions selon le type d'utilisateur
            $this->checkFacturePermissions($user, $facture);

            // Préparer les informations du patient
            $patientInfo = $this->getPatientInfo($facture->sinistre->assure);
            
            // Préparer les informations du sinistre
            $sinistreInfo = $this->getSinistreInfo($facture->sinistre);
            
            // Préparer les informations de la facture
            $factureInfo = $this->getFactureInfo($facture);

            // Données pour le template
            $data = [
                'facture' => $facture,
                'patient' => $patientInfo,
                'sinistre' => $sinistreInfo,
                'facture_details' => $factureInfo,
                'entreprise' => $this->getEntrepriseInfo(),
                'dateGeneration' => now()->format('d/m/Y H:i')
            ];

            // Générer le PDF
            $pdf = Pdf::loadView('pdf.facture', $data);
            
            // Configuration du PDF
            $pdf->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'Arial',
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true
                ]);

            // Nom du fichier
            $filename = "facture-{$facture->numero_facture}-{$patientInfo['nom']}-{$patientInfo['prenoms']}.pdf";

            // Retourner le PDF en téléchargement
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF', ['error' => $e->getMessage()]);           
            return response()->json([
                'message' => 'Erreur lors de la génération du PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prévisualise une facture en HTML (pour debug)
     */
    public function previewPdf($id)
    {
        $facture = Facture::with([
            'sinistre.assure',
            'prestataire',
            'lignesFacture.garantie',
            'technicien',
            'medecin',
            'comptable'
        ])->findOrFail($id);

        $data = [
            'facture' => $facture,
            'entreprise' => $this->getEntrepriseInfo(),
            'dateGeneration' => now()->format('d/m/Y H:i')
        ];

        return view('factures.pdf-template', $data);
    }

    /**
     * Informations de l'entreprise
     */
    private function getEntrepriseInfo()
    {
        return [
            'nom' => config('app.name', 'Votre Assurance'),
            'adresse' => 'Immeuble SUNU, 812 boulevard du 13 janvier, 07 BP 7022, Lomé - Togo',
            'telephone' => '(228) 22 20 12 57',
            'email' => 'info@sunu-sante.com',
            'site_web' => 'www.sunu-sante.com',
            'logo_path' => public_path('images/sunu-logo.png'), // Chemin vers votre logo
            'logo_base64' => file_exists(public_path('images/sunu-logo.png')) ? base64_encode(file_get_contents(public_path('images/sunu-logo.png'))) : null
        ];
    }

    /**
     * Sauvegarde le PDF sur le serveur (optionnel)
     */
    private function savePdfToStorage($pdf, $filename)
    {
        $path = "factures/pdf/" . date('Y/m');
        Storage::disk('public')->put($path . '/' . $filename, $pdf->output());
        
        return $path . '/' . $filename;
    }

    /**
     * Vérifier les permissions pour télécharger une facture
     */
    private function checkFacturePermissions($user, $facture)
    {
        // Prestataire : peut télécharger ses propres factures
        if ($user->hasRole('prestataire') && $user->prestataire) {
            if ($facture->prestataire_id !== $user->prestataire->id) {
                abort(403, 'Vous n\'êtes pas autorisé à télécharger cette facture');
            }
        }
        // Entreprise : peut télécharger les factures de ses assurés
        elseif ($user->hasRole('entreprise') && $user->entreprise) {
            $assureIds = $user->entreprise->assures()->pluck('id');
            if (!in_array($facture->sinistre->assure_id, $assureIds)) {
                abort(403, 'Vous n\'êtes pas autorisé à télécharger cette facture');
            }
        }
        // Physique : peut télécharger ses propres factures et celles de ses bénéficiaires
        elseif ($user->hasRole('physique') && $user->assure) {
            $assureIds = [$user->assure->id];
            $beneficiairesIds = $user->assure->beneficiaires()->pluck('id')->toArray();
            $assureIds = array_merge($assureIds, $beneficiairesIds);
            
            if (!in_array($facture->sinistre->assure_id, $assureIds)) {
                abort(403, 'Vous n\'êtes pas autorisé à télécharger cette facture');
            }
        }
        // Personnel : peut télécharger toutes les factures
        elseif ($user->hasRole('technicien') || $user->hasRole('medecin_controleur') || 
                $user->hasRole('comptable') || $user->hasRole('admin_global') || 
                $user->hasRole('gestionnaire')) {
            // Pas de restriction
        }
        else {
            abort(403, 'Accès non autorisé');
        }
    }

    /**
     * Préparer les informations du patient
     */
    private function getPatientInfo($assure)
    {
        Log::info($assure);
        $patientInfo = [
            'id' => $assure->id,
            'nom' => $assure->nom,
            'prenoms' => $assure->prenoms,
            'date_naissance' => $assure->date_naissance ? $assure->date_naissance->format('d/m/Y') : null,
            'sexe' => $assure->sexe,
            'profession' => $assure->profession,
            'contact' => $assure->contact ?? ($assure->user ? $assure->user->contact : null),
            'email' => $assure->email ?? ($assure->user ? $assure->user->email : null),
            'type' => $assure->est_principal ? 'Assuré Principal' : 'Bénéficiaire',
            'lien_parente' => $assure->lien_parente,
        ];

        // Informations de l'entreprise si c'est un employé
        if ($assure->entreprise && $assure->entreprise->user) {
            $patientInfo['entreprise'] = [
                'raison_sociale' => $assure->entreprise->raison_sociale ?? 'N/A',
                'adresse' => $assure->entreprise->user->adresse ?? 'N/A',
                'contact' => $assure->entreprise->user->contact ?? 'N/A',
                'email' => $assure->entreprise->user->email ?? 'N/A',
            ];
        }

        return $patientInfo;
    }



    /**
     * Préparer les informations du sinistre
     */
    private function getSinistreInfo($sinistre)
    {
        return [
            'id' => $sinistre->id,
            'numero_sinistre' => $sinistre->numero_sinistre,
            'date_sinistre' => $sinistre->date_sinistre ? $sinistre->date_sinistre->format('d/m/Y H:i') : null,
            'description' => $sinistre->description,
            'statut' => $sinistre->statut,
            'created_at' => $sinistre->created_at ? $sinistre->created_at->format('d/m/Y H:i') : null,
        ];
    }

    /**
     * Préparer les informations détaillées de la facture
     */
    private function getFactureInfo($facture)
    {
        $montantTotal = $facture->lignesFacture->sum(function ($ligne) {
            return $ligne->prix_unitaire * $ligne->quantite;
        });

        $montantRembourse = $facture->montant_a_rembourser ?? 0;
        $montantPatient = $facture->ticket_moderateur ?? 0;

        return [
            'numero_facture' => $facture->numero_facture,
            'date_facture' => $facture->created_at ? $facture->created_at->format('d/m/Y') : null,
            'statut' => $facture->statut,
            'montant_total' => number_format($montantTotal, 0, ',', ' '),
            'montant_rembourse' => number_format($montantRembourse, 0, ',', ' '),
            'montant_patient' => number_format($montantPatient, 0, ',', ' '),
            'lignes' => $facture->lignesFacture->map(function ($ligne) {
                return [
                    'garantie' => $ligne->garantie->libelle,
                    'libelle_acte' => $ligne->libelle_acte,
                    'prix_unitaire' => number_format($ligne->prix_unitaire, 0, ',', ' '),
                    'quantite' => $ligne->quantite,
                    'total_ligne' => number_format($ligne->prix_unitaire * $ligne->quantite, 0, ',', ' '),
                ];
            }),
            'prestataire' => [
                'nom' => $facture->prestataire->raison_sociale ?? 'N/A',
                'adresse' => $facture->prestataire->user ? ($facture->prestataire->user->adresse ?? 'N/A') : 'N/A',
                'contact' => $facture->prestataire->user ? ($facture->prestataire->user->contact ?? 'N/A') : 'N/A',
                'email' => $facture->prestataire->user ? ($facture->prestataire->user->email ?? 'N/A') : 'N/A',
            ],
        ];
    }

}
