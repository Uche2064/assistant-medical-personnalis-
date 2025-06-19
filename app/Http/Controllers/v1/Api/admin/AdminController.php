<?php

namespace App\Http\Controllers\v1\Api\admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\AuthFormRequest;
use App\Models\Gestionnaire;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Authentifie uniquement l'admin global par nom d'user et mot de passe.
     */
    public function login(AuthFormRequest $request)
    {
        $credentials = $request->validated();

        // Recherche de l'user admin actif par username
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
                    'nom' => $g->user->nom ?? null,
                    'prenoms' => $g->user->prenoms ?? null,
                    'email' => $g->user->email ?? null,
                    'contact' => $g->user->contact ?? null,
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

