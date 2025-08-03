<?php

namespace App\Http\Controllers\v1\Api\auth;

use App\Enums\EmailType;
use App\Enums\OtpTypeEnum;
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
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error("Erreur de validation", 422, $validator->errors());
        }

        $email = $request->email;

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ApiResponse::error('Aucun compte trouvé avec cet email.', 404);
        }

        // Générer un OTP unique
        $otp = Otp::generateOtp($email, 10, OtpTypeEnum::FORGOT_PASSWORD->value);

        // Envoyer l'email avec l'OTP
        dispatch(new SendEmailJob(
            $email,
            'Réinitialisation de votre mot de passe',
            EmailType::PASSWORD_RESET_OTP->value,
            [
                'user' => $user,
                'otp' => $otp,
                'expire_at' => now()->addMinutes(10)
            ]
        ));

        return ApiResponse::success(null, 'Code OTP envoyé avec succès.');
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
            ->where('type', OtpTypeEnum::FORGOT_PASSWORD)
            ->where('expire_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return ApiResponse::error('Code OTP invalide ou expiré.', 400);
        }

        // Supprimer l'OTP utilisé
        $otpRecord->delete();

        return ApiResponse::success(null, 'Code OTP vérifié avec succès. Vous pouvez maintenant définir votre nouveau mot de passe.');
    }

    /**
     * Réinitialiser le mot de passe après vérification OTP
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();
        // $resetToken = $validated['token'];
        $newPassword = $validated['password'];
        $email = $validated['email'];


        // Récupérer l'utilisateur
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ApiResponse::error('Utilisateur non trouvé.', 404);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($newPassword),
            'mot_de_passe_a_changer' => false
        ]);

        // Envoyer un email de confirmation
        dispatch(new SendEmailJob(
            $user->email,
            'Mot de passe modifié avec succès',
            EmailType::PASSWORD_CHANGED->value,
            [
                'user' => $user,
                'changed_at' => now()
            ]
        ));

        return ApiResponse::success(null, 'Mot de passe modifié avec succès.');
    }
}
