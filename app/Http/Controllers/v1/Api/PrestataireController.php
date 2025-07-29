<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\TypePrestataireEnum;
use App\Helpers\ApiResponse;
use App\Helpers\PrestataireDocumentHelper;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrestataireController extends Controller
{
    /**
     * Dashboard du prestataire
     */
    public function dashboard()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return ApiResponse::error('Prestataire non trouvé', 404);
        }

        return ApiResponse::success([
            'prestataire' => [
                'id' => $prestataire->id,
                'raison_sociale' => $prestataire->raison_sociale,
                'type_prestataire' => $prestataire->type_prestataire,
                'statut' => $prestataire->statut,
                'email' => $prestataire->email,
                'contact' => $prestataire->contact,
                'adresse' => $prestataire->adresse,
            ],
            'demandes_adhesion' => $prestataire->demandesAdhesions()->latest()->take(5)->get(),
            'statistiques' => [
                'total_demandes' => $prestataire->demandesAdhesions()->count(),
                'demandes_en_attente' => $prestataire->demandesAdhesions()->where('statut', 'en_attente')->count(),
                'demandes_validees' => $prestataire->demandesAdhesions()->where('statut', 'validee')->count(),
            ],
        ]);
    }

    /**
     * Obtenir le profil du prestataire
     */
    public function getProfile()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return ApiResponse::error('Prestataire non trouvé', 404);
        }

        return ApiResponse::success([
            'prestataire' => [
                'id' => $prestataire->id,
                'raison_sociale' => $prestataire->raison_sociale,
                'email' => $prestataire->email,
                'contact' => $prestataire->contact,
                'adresse' => $prestataire->adresse,
                'type_prestataire' => $prestataire->type_prestataire,
                'statut' => $prestataire->statut,
                'documents_requis' => $prestataire->documents_requis,
            ],
        ]);
    }

    /**
     * Mettre à jour le profil du prestataire
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return ApiResponse::error('Prestataire non trouvé', 404);
        }

        $validated = $request->validate([
            'raison_sociale' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'contact' => 'sometimes|string|max:20',
            'adresse' => 'sometimes|string|max:500',
        ]);

        $prestataire->update($validated);

        return ApiResponse::success([
            'message' => 'Profil mis à jour avec succès',
            'prestataire' => $prestataire->fresh(),
        ]);
    }

    /**
     * Obtenir les questions pour le type de prestataire
     */
    public function getQuestions()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return ApiResponse::error('Prestataire non trouvé', 404);
        }

        $questions = Question::where('destinataire', $prestataire->type_prestataire->value)
            ->where('est_actif', true)
            ->orderBy('ordre')
            ->get();

        return ApiResponse::success([
            'questions' => $questions,
            'type_prestataire' => $prestataire->type_prestataire,
        ]);
    }

    /**
     * Obtenir les documents requis selon le type de prestataire
     */
    public function getDocumentsRequis()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return ApiResponse::error('Prestataire non trouvé', 404);
        }

        $documentsRequis = PrestataireDocumentHelper::getDocumentsRequis($prestataire->type_prestataire);
        $messageInstruction = PrestataireDocumentHelper::getMessageInstruction($prestataire->type_prestataire);

        return ApiResponse::success([
            'type_prestataire' => $prestataire->type_prestataire,
            'message_instruction' => $messageInstruction,
            'email_contact' => PrestataireDocumentHelper::getEmailContact(),
            'documents_requis' => $documentsRequis,
            'documents_uploades' => $prestataire->documents_requis ?? [],
        ]);
    }

    /**
     * Valider les documents fournis
     */
    public function validerDocuments(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return ApiResponse::error('Prestataire non trouvé', 404);
        }

        $validation = PrestataireDocumentHelper::validerDocuments(
            $prestataire->type_prestataire,
            $prestataire->documents_requis ?? []
        );

        return ApiResponse::success([
            'validation' => $validation,
            'type_prestataire' => $prestataire->type_prestataire,
        ]);
    }

    /**
     * Soumettre une demande d'adhésion (redirige vers le contrôleur principal)
     */
    public function soumettreDemandeAdhesion(Request $request)
    {
        // Cette méthode redirige vers le contrôleur principal des demandes d'adhésion
        return app(\App\Http\Controllers\v1\Api\demande_adhesion\DemandeAdhesionController::class)
            ->soumettreDemandeAdhesionPrestataire($request);
    }
} 