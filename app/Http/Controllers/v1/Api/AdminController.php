<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\AuthFormRequest;
use App\Http\Requests\admin\CompagnieFormRequest;
use App\Http\Requests\admin\GestionnaireFormRequest;
use App\Models\Compagnie;
use App\Models\Gestionnaire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Authentifie uniquement l'admin global par nom d'utilisateur et mot de passe.
     */
    public function login(AuthFormRequest $request)
    {
        $credentials = $request->validated();

        // Recherche de l'utilisateur admin actif par username
        $admin = User::where('username', $credentials['username'])
            ->where('est_actif', true)
            ->first();

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return ApiResponse::error('Identifiants incorrects', 401);
        }

        // Générer un token Sanctum
        $token = $admin->createToken('admin-api-token')->plainTextToken;

        // Charger les gestionnaires créés par cet admin (supposé: user_id = admin.id)
        $gestionnaires = Gestionnaire::where('user_id', $admin->id)
            ->with(['compagnie'])
            ->get()
            ->map(function($g) {
                return [
                    'id' => $g->id,
                    'nom' => $g->utilisateur->nom ?? null,
                    'prenoms' => $g->utilisateur->prenoms ?? null,
                    'email' => $g->utilisateur->email ?? null,
                    'contact' => $g->utilisateur->contact ?? null,
                    'compagnie_id' => $g->compagnie_id,
                    'compagnie' => $g->compagnie ? $g->compagnie->nom : null,
                ];
            });

        return ApiResponse::success([
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'nom' => $admin->nom,
                'prenoms' => $admin->prenoms,
                'username' => $admin->username,
                'email' => $admin->email,
                'role' => $admin->getRoleNames()->first(),
            ],
            'gestionnaires' => $gestionnaires
        ], 'Connexion admin réussie', 200);
    }


    
}

