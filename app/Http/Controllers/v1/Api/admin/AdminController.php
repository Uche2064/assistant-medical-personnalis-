<?php

namespace App\Http\Controllers\v1\Api\admin;

use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\StoreGestionnaireRequest;
use App\Models\Gestionnaire;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function storeGestionnaire(StoreGestionnaireRequest $request)
    {
        $validated = $request->validated();
        $password = User::genererMotDePasse();
        $photoUrl = null;
        
        if (isset($validated['photo_url'])) {
            $photo = $validated['photo_url'];
            $photoUrl = ImageUploadHelper::uploadImage($photo, 'uploads/users');
        } else {
            $photo = null;
        }
        $user = User::create([
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'],
            'email' => $validated['email'],
            'sexe' => $validated['sexe'] ?? null,
            'date_naissance' => $validated['date_naissance'] ?? null,
            'contact' => $validated['contact'],
            'adresse' => $validated['adresse'] ?? null,
            'password' => Hash::make($password),
            'est_actif' => false,
            'mot_de_passe_a_changer' => true,
            'photo_url' => $photoUrl,
        ]);

        Log::info($password);
        

        $user->assignRole('gestionnaire');

        $gestionnaire = Personnel::create([
            'user_id' => $user->id,
        ]);

        return ApiResponse::success([
            'gestionnaire' => $gestionnaire,
            'user' => $user,
        ], 'Gestionnaire créé avec succès');
    }

    public function indexGestionnaires()
    {
        $gestionnaires = Personnel::with('user')->latest()->paginate(10);

        return ApiResponse::success($gestionnaires, 'Liste des gestionnaires récupérée avec succès');
    }

    public function showGestionnaire($id)
    {
        $gestionnaire = Personnel::with('user')->find($id);
        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }

        return ApiResponse::success($gestionnaire, 'Détails du gestionnaire');
    }

    public function destroyGestionnaire(int $id)
    {
        $gestionnaire = Personnel::with('user')->find($id);
        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }
        $gestionnaire->delete();
        return ApiResponse::success(null, 'Gestionnaire supprimé avec succès', 204);
    }
}
