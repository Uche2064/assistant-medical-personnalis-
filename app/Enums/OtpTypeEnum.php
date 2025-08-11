<?php

namespace App\Enums;

enum OtpTypeEnum: string
{
    case REGISTER = 'register';
    case FORGOT_PASSWORD = 'forgot_password';
    case CHANGE_PASSWORD = 'change_password';
    case CHANGE_EMAIL = 'change_email';
    case VERIFY_EMAIL = 'verify_email';
    case VERIFY_PHONE = 'verify_phone';
    case VERIFY_IDENTITY = 'verify_identity';
    case VERIFY_ADDRESS = 'verify_address';

    public static function values(): array
    {   
        return [
            self::REGISTER,
            self::FORGOT_PASSWORD,
            self::CHANGE_PASSWORD,
            self::CHANGE_EMAIL,
            self::VERIFY_EMAIL,
            self::VERIFY_PHONE,
            self::VERIFY_IDENTITY,
            self::VERIFY_ADDRESS,
        ];
    }
}
