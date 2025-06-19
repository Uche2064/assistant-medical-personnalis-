<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\TypePersonnelEnum;
use App\Enums\TypePrestataireEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ChangePasswordFormRequest;
use App\Http\Requests\auth\LoginWithEmailAndPasswordFormRequest;
use App\Models\Assure;
use App\Models\Otp;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Le numero de telephone est requis', 422, $validator->errors());
        }

        $validatedData = $validator->validated();

        $user = User::where('contact', $validatedData['phone'])->where('est_actif', true)
            ->with(['personnel', 'prestataire', 'assure'])
            ->first();

        dd($user);
        if (!$user) {
            return ApiResponse::error("Ce numéro n'est pas encore enregistré dans le système.", 403);
        }

        if ($user->assure) {
            $role = 'assure';
        } elseif ($user->personnel && $user->personnel->type_personnel === TypePersonnelEnum::COMMERCIAL->value) {
            $role = 'commercial';
        } elseif ($user->prestataire && $user->prestataire->type_prestataire === TypePrestataireEnum::PARTICULIER->value) {
            $role = 'particulier';
        } else {
            return ApiResponse::error("Ce numéro n'est pas autorisé à recevoir un OTP.", 403);
        }

        // Générer un OTP de 6 chiffres
        $otp = Otp::generateCode();

        // Sauvegarder l'OTP dans la base de données
        try {
            Otp::updateOrCreateOtp($validatedData['phone'], $otp);
        } catch (Exception $e) {
            return ApiResponse::error("Erreur lors de l'enregistrement de l'OTP.", 500);
        }


        return ApiResponse::success([
            'otp' => $otp, // À supprimer en production, c'est juste pour le test
            'role' => $role
        ], 'OTP envoyé avec succès', 200);
    }

    /**
     * Vérifie un OTP envoyé à un téléphone.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric',
            'otp'   => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                'Numéro ou OTP manquant ou invalide',
                422,
                $validator->errors()
            );
        }

        $validated = $validator->validated();

        // Récupérer l'OTP valide correspondant au téléphone et au code
        $otp = Otp::where('phone', $validated['phone'])
            ->where('code_otp', $validated['otp'])
            ->whereNull('verifier_a') // non encore utilisé
            ->first();

        // OTP introuvable ou expiré
        if (!$otp || $otp->isExpired()) {
            return ApiResponse::error('OTP invalide ou expiré.', 403);
        }

        // Marquer comme vérifié
        $otp->verifier_a = now();
        $otp->save();

        return ApiResponse::success(
            null,
            'OTP vérifié avec succès.',
            200
        );
    }

    public function loginWithEmailAndPassword(LoginWithEmailAndPasswordFormRequest $request)
    {
        // valider le formulaire
        $validatedData = $request->validated();

        // rechercher l'utilisateur
        $user = User::where('username', $validatedData['username'])
            ->where('est_actif', true)
            ->first();

        // vérifier le mot de passe
        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return ApiResponse::error('Identifiants incorrects', 401);
        }

        // Vérifier si le mot de passe doit être changé
        if ($user->must_change_password) {
            $token = $user->createToken('auth-token')->plainTextToken;
            return ApiResponse::success([
                'token' => $token,
                'must_change_password' => true,
            ], 'Changement de mot de passe obligatoire', 200);
        }

        // créer le token de connexion
        $token = $user->createToken('auth-token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenoms' => $user->prenoms,
                'username' => $user->username,
                'email' => $user->email,
                'sexe' => $user->sexe,
                'contact' => $user->contact,
                'adresse' => $user->adresse,
                'must_change_password' => $user->must_change_password,
                'creer_a' => $user->created_at,
                'modifier_a' => $user->updated_at,
                'role' => $user->getRoleNames()->first(),
            ],
        ], 'Connexion utilisateur réussie', 200);
    }

    public function changePassword(ChangePasswordFormRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['new_password']),
            'must_change_password' => false,
        ]);

        return ApiResponse::success(null, 'Mot de passe changé avec succès.', 200);
    }
}
