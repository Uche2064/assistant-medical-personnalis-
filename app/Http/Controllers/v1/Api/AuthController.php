<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\TypePersonnelEnum;
use App\Enums\TypePrestataireEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ChangePasswordFormRequest;
use App\Http\Requests\auth\LoginWithEmailAndPasswordFormRequest;
use App\Http\Requests\auth\SendOtpFormRequest;
use App\Http\Requests\auth\VerifyOtpFormRequest;
use App\Models\Otp;
use App\Models\User;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function sendOtp(SendOtpFormRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('contact', $validated['phone'])->where('est_actif', true)
            ->first();

        if (!$user) {
            return ApiResponse::error("Ce numéro n'est pas encore enregistré dans le système.", 403);
        }
        // Générer un OTP de 6 chiffres
        $otp = Otp::generateCode();

        // Sauvegarder l'OTP dans la base de données
        try {
            Otp::updateOrCreateOtp($validated['phone'], $otp);
        } catch (Exception $e) {
            return ApiResponse::error("Erreur lors de l'enregistrement de l'OTP.", 500);
        }


        return ApiResponse::success([
            'otp' => $otp,
        ], 'OTP envoyé avec succès');
    }

    /**
     * Vérifie un OTP envoyé à un téléphone.
     */
    public function verifyOtp(VerifyOtpFormRequest $request)
    {
        $validated = $request->validated();

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
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])
            ->where('est_actif', true)
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return ApiResponse::error('Identifiants incorrects', 401);
        }

        if ($user->must_change_password) {
            return ApiResponse::success(['must_change_password' => true], 'Changement de mot de passe obligatoire', 200);
        }

        if (! $token = JWTAuth::fromUser($user)) {
            return ApiResponse::error('Impossible de créer le token', 500);
        }

        $this->notificationService->sendEmail($user->email, 'Connexion Réussie', 'emails.login_successful', [
            'user' => $user,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);

        return $this->respondWithToken($token, $user);
    }

    public function refreshToken()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return $this->respondWithToken($token, auth('api')->user());
        } catch (TokenInvalidException $e) {
            return ApiResponse::error('Token invalide', 401);
        } catch (JWTException $e) {
            return ApiResponse::error('Token absent', 401);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return ApiResponse::success(null, 'Déconnexion réussie');
        } catch (JWTException $e) {
            return ApiResponse::error('Déconnexion impossible', 500);
        }
    }

    public function changePassword(ChangePasswordFormRequest $request)
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();

        // vérifier si le mot de passe courant dans la base de données est idem que celui que l'utilisateur nous envoie
        if (!Hash::check($validated['current_password'], $user->password)) {
            return ApiResponse::error('Mot de passe actuel incorrect', 401);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
            'must_change_password' => false
        ]);

        $this->notificationService->sendEmail($user->email, 'Mot de passe changé', 'emails.password_changed', [
            'user' => $user,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);

        return ApiResponse::success(null, 'Mot de passe changé avec succès.', 200);
    }

    protected function respondWithToken($token, $user)
    {
        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => $user,
        ], 'Connexion réussie');
    }
}
