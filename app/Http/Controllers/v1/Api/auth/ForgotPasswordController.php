<?php

namespace App\Http\Controllers\v1\Api\auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Http\Requests\auth\SendResetPasswordLinkRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(SendResetPasswordLinkRequest $request)
    {
        $validated = $request->validated();

        $status = Password::sendResetLink($validated);

        return $status === Password::RESET_LINK_SENT
            ? ApiResponse::success([], 'Un lien de réinitialisation a été envoyé à votre email.')
            : ApiResponse::error('Échec lors de l\'envoi du lien de réinitialisation', 400);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();


        $status = Password::reset(
            $validated,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'mot_de_passe_a_changer' => false
                ])->update();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? ApiResponse::success([], 'Mot de passe réinitialisé avec succès.')
            : ApiResponse::error(__($status), 400);
    }
}
