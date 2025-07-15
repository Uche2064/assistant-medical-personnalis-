<?php

namespace App\Http\Controllers\v1\Api\auth;

use App\Enums\EmailType;
use App\Enums\TypePersonnelEnum;
use App\Enums\TypePrestataireEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ChangePasswordFormRequest;
use App\Http\Requests\auth\LoginWithEmailAndPasswordFormRequest;
use App\Http\Requests\auth\RegisterProspectRequest;
use App\Http\Requests\auth\SendOtpFormRequest;
use App\Http\Requests\auth\VerifyOtpFormRequest;
use App\Jobs\SendEmailJob;
use App\Jobs\SendLoginNotificationJob;
use App\Models\InvitationEmployes;
use App\Models\Otp;
use App\Models\Prospect;
use App\Models\User;
use App\Services\AuthService;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    protected NotificationService $notificationService;
    protected AuthService $authService;


    public function __construct(NotificationService $notificationService, AuthService $authService)
    {
        $this->notificationService = $notificationService;
        $this->authService = $authService;
    }
    // public function sendOtp(SendOtpFormRequest $request)
    // {
    //     $validated = $request->validated();

    //     $user = User::where('contact', $validated['phone'])->where('est_actif', true)
    //         ->first();

    //     if (!$user) {
    //         return ApiResponse::error("Ce numéro n'est pas encore enregistré dans le système.", 403);
    //     }
    //     // Générer un OTP de 6 chiffres
    //     $otp = Otp::generateCode();

    //     // Sauvegarder l'OTP dans la base de données
    //     try {
    //         Otp::updateOrCreateOtp($validated['phone'], $otp);
    //     } catch (Exception $e) {
    //         return ApiResponse::error("Erreur lors de l'enregistrement de l'OTP.", 500);
    //     }


    //     return ApiResponse::success([
    //         'otp' => $otp,
    //     ], 'OTP envoyé avec succès');
    // }

    /**
     * Vérifie un OTP envoyé à un téléphone.
     */
    // public function verifyOtp(VerifyOtpFormRequest $request)
    // {
    //     $validated = $request->validated();

    //     // Récupérer l'OTP valide correspondant au téléphone et au code
    //     $otp = Otp::where('phone', $validated['phone'])
    //         ->where('code_otp', $validated['otp'])
    //         ->whereNull('verifier_a') // non encore utilisé
    //         ->first();

    //     // OTP introuvable ou expiré
    //     if (!$otp || $otp->isExpired()) {
    //         return ApiResponse::error('OTP invalide ou expiré.', 403);
    //     }

    //     // Marquer comme vérifié
    //     $otp->verifier_a = now();
    //     $otp->save();

    //     return ApiResponse::success(
    //         null,
    //         'OTP vérifié avec succès.',
    //         200
    //     );
    // }

    public function loginWithEmailAndPassword(LoginWithEmailAndPasswordFormRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return ApiResponse::error('Identifiants incorrects');
        }

        if ($user->mot_de_passe_a_changer) {
            return ApiResponse::success(
                ['mot_de_passe_a_changer' => true],
                'Changement de mot de passe obligatoire',
                202
            );
        }

        $token = $this->authService->generateToken($user);

        dispatch(new SendLoginNotificationJob($user,));

        return $this->authService->respondWithToken($token, $user);
    }

    public function register(RegisterProspectRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            // Crée le compte utilisateur
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'contact' => $validated['contact'],
                'adresse' => $validated['adresse'],
                'nom' => $validated['nom'] ?? null,
                'prenoms' => $validated['prenoms'] ?? null,
                'raison_sociale' => $validated['raison_sociale'] ?? null,
                'mot_de_passe_a_changer' => false,
            ]);

            // Crée le prospect lié
            $prospect = Prospect::create([
                'user_id' => $user->id,
                'type_prospect' => $validated['type_prospect'],
                'nom' => $validated['nom'] ?? null,
                'sexe' => $validated['sexe'] ?? null,
                'prenoms' => $validated['prenoms'] ?? null,
                'date_naissance' => $validated['date_naissance'] ?? null,
                'profession' => $validated['profession'] ?? null,
                'raison_sociale' => $validated['raison_sociale'] ?? null,
                'contact' => $validated['contact'],
                'email' => $validated['email'],
                'adresse' => $validated['adresse'],
                'nombre_de_beneficiaires' => $validated['nombre_employes'] ?? null,
            ]);

            // Tu peux assigner le rôle "client" ici si tu utilises Spatie
            $user->assignRole('client');

            // Envoi d'un email de confirmation
            SendEmailJob::dispatch(
                $user->email,
                'Compte créé',
                EmailType::REGISTERED->value,
                ['user' => $user]
            );


            DB::commit();
            $inviteLink = null;
            if ($validated['type_prospect'] === 'moral') {
                $token = Str::uuid()->toString();

                InvitationEmployes::create([
                    'prospect_id' => $prospect->id,
                    'token' => $token,
                    'expire_at' => now()->addDays(30)
                ]);

                $inviteLink = url("v1/Api/employes/formulaire/{$token}");
            }

            return ApiResponse::success([
                'user' => $user,
                'invite_link' => $inviteLink
            ], 'Compte créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la création du compte', 500, $e->getMessage());
        }
    }

    public function refreshToken()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return $this->authService->respondWithToken($token, auth('api')->user());
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
        try {
            $validated = $request->validated();
            $user = User::where('email', $validated['email'])->first();

            // vérifier si le mot de passe courant dans la base de données est idem que celui que l'utilisateur nous envoie
            if (!Hash::check($validated['current_password'], $user->password)) {
                return ApiResponse::error('Mot de passe actuel incorrect', 401);
            }

            $user->update([
                'password' => Hash::make($validated['new_password']),
                'est_actif' => true,
                'email_verified_at' => now(),
                'mot_de_passe_a_changer' => false
            ]);

            SendEmailJob::dispatch(
                $user->email,
                'Mot de passe modifié',
                EmailType::PASSWORD_CHANGED->value,
                ['user' => $user]
            );

            return ApiResponse::success(null, 'Mot de passe changé avec succès.', 200);
        } catch (\Throwable $th) {
            return ApiResponse::error('Erreur lors de la modification du mot de passe', 500, $th);
        }
    }

    public function checkUnique(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field' => 'required|in:username,email,contact',
            'value' => 'required|string'
        ]);

        if ($validator->fails()) {
            ApiResponse::error('Erreur de validation', 422, $validator->errors());
        }

        $field = $request->input('field');
        $value = $request->input('value');

        $exists = User::where($field, $value)->exists();

        return ApiResponse::success([
            'exists' => $exists,
        ], 'Vérification de l\'unicité réussie');
    }
}
