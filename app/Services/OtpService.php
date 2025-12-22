<?php

namespace App\Services;

use App\Enums\OtpTypeEnum;
use App\Jobs\SendEmailJob;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public static function sendForgotPasswordOtp(string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();

        $otpExpiredAt = (int) env('OTP_EXPIRED_AT', 10);

        // Supprimer anciens OTP du même type
        Otp::where('email', $email)
            ->where('type', OtpTypeEnum::FORGOT_PASSWORD->value)
            ->delete();

        // Générer OTP
        $otp = Otp::generateOtp(
            $email,
            $otpExpiredAt,
            OtpTypeEnum::FORGOT_PASSWORD->value
        );

        Log::info("OTP généré (Forgot Password)", [
            'email' => $email,
            'otp' => $otp,
        ]);

        // Envoyer email
        dispatch(new SendEmailJob(
            $user->email,
            'Réinitialisation de votre mot de passe - SUNU Santé',
            'emails.otp_verification',
            [
                'user' => $user,
                'otp' => $otp,
                'expire_at' => now()->addMinutes($otpExpiredAt),
            ]
        ));
    }
}
