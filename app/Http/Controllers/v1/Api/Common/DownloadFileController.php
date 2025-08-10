<?php

namespace App\Http\Controllers\v1\Api\common;

use App\Helpers\ApiResponse;
use App\Helpers\CommonHelpers;
use App\Http\Controllers\Controller;
use App\Models\DemandeAdhesion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

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
}
