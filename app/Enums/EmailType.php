<?php

namespace App\Enums;

enum EmailType: String
{
    case LOGIN = 'emails.login_successful';
    case PASSWORD_CHANGED = 'emails.password_changed';
    case OTP = 'emails.otp';
    case ACCEPTED = 'emails.acceptee';
    case REJETEE = 'emails.rejetee';
    case CREDENTIALS = 'emails.credentials';
    case EN_ATTENTE = 'emails.en_attente';
    case REGISTERED = 'emails.registered';
}
