<?php

namespace App\Http\Controllers\v1\Api\auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Http\Requests\auth\SendResetPasswordLinkRequest;
use App\Jobs\SendEmailJob;
use App\Mail\GenericMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Envoyer un OTP pour réinitialiser le mot de passe
     */
    public function sendResetLink(SendResetPasswordLinkRequest $request)
    {
        $validated = $request->validated();
        $email = $validated['email'];

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ApiResponse::error('Aucun compte trouvé avec cet email.', 404);
        }

        // Générer un OTP unique
        $otp = Otp::generateOtp($email, 10, 'password_reset');

        // Supprimer les anciens OTP pour cet email
        Otp::where('email', $email)->delete();

        Otp::create([
            'email' => $email,
            'otp' => $otp->otp,
            'expire_at' => $otp->expire_at,
            'type' => 'password_reset'
        ]);

        // Envoyer l'email avec l'OTP
        dispatch(new SendEmailJob(
            $email,
            'Réinitialisation de votre mot de passe',
            'emails.password_reset_otp',
            [
                'user' => $user,
                'otp' => $otp,
                'expire_at' => $otp->expire_at
            ]
        ));

        return ApiResponse::success([
            'email' => $email,
            'message' => 'Un code OTP a été envoyé à votre email.'
        ], 'Code OTP envoyé avec succès.');
    }

    /**
     * Vérifier l'OTP pour la réinitialisation du mot de passe
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);
        
        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", 422, $validator->errors());
        }
        

        $email = $request->email;
        $otp = $request->otp;

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ApiResponse::error('Aucun compte trouvé avec cet email.', 404);
        }

        // Vérifier l'OTP
        $otpRecord = Otp::where('email', $email)
            ->where('otp', $otp)
            ->where('type', 'password_reset')
            ->where('expire_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return ApiResponse::error('Code OTP invalide ou expiré.', 400);
        }

        // Supprimer l'OTP utilisé
        $otpRecord->delete();

        // Générer un token temporaire pour la réinitialisation
        $resetToken = Str::random(60);
        $tokenExpiration = now()->addMinutes(15);

        // Stocker le token (utiliser le cache ou une table temporaire)
        \Illuminate\Support\Facades\Cache::put("password_reset_{$resetToken}", [
            'user_id' => $user->id,
            'email' => $email,
            'expires_at' => $tokenExpiration
        ], $tokenExpiration);

        return ApiResponse::success([
            'reset_token' => $resetToken,
            'expires_at' => $tokenExpiration,
            'message' => 'Code OTP vérifié. Vous pouvez maintenant définir votre nouveau mot de passe.'
        ], 'Code OTP vérifié avec succès.');
    }

    /**
     * Réinitialiser le mot de passe après vérification OTP
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();
        $resetToken = $validated['token'];
        $newPassword = $validated['password'];

        // Récupérer les données du token
        $tokenData = \Illuminate\Support\Facades\Cache::get("password_reset_{$resetToken}");
        
        if (!$tokenData) {
            return ApiResponse::error('Token de réinitialisation invalide ou expiré.', 400);
        }

        // Vérifier que l'email correspond
        if ($tokenData['email'] !== $validated['email']) {
            return ApiResponse::error('Email ne correspond pas au token de réinitialisation.', 400);
        }

        // Récupérer l'utilisateur
        $user = User::find($tokenData['user_id']);
        if (!$user) {
            return ApiResponse::error('Utilisateur non trouvé.', 404);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($newPassword),
            'mot_de_passe_a_changer' => false
        ]);

        // Supprimer le token utilisé
        \Illuminate\Support\Facades\Cache::forget("password_reset_{$resetToken}");

        // Envoyer un email de confirmation
        dispatch(new SendEmailJob(
            $user->email,
            'Mot de passe modifié avec succès',
            'emails.password_changed',
            [
                'user' => $user,
                'changed_at' => now()
            ]
        ));

        return ApiResponse::success([
            'message' => 'Mot de passe réinitialisé avec succès.'
        ], 'Mot de passe modifié avec succès.');
    }
}
