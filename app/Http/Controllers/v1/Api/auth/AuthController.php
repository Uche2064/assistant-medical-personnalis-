<?php

namespace App\Http\Controllers\v1\Api\auth;

use App\Enums\EmailType;
use App\Enums\OtpTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\StatutClientEnum;
use App\Enums\StatutPrestataireEnum;
use App\Enums\TypeClientEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypePrestataireEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\ChangePasswordFormRequest;
use App\Http\Requests\auth\LoginWithEmailAndPasswordFormRequest;
use App\Http\Requests\auth\RegisterRequest;
use App\Http\Requests\auth\SendOtpFormRequest;
use App\Http\Requests\auth\VerifyOtpFormRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendEmailJob;
use App\Jobs\SendLoginNotificationJob;
use App\Models\Client;
use App\Models\Entreprise;
use App\Models\Otp;
use App\Models\Prestataire;
use App\Models\User;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Inscription d'un demandeur (physique, moral, ou prestataire)
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        $photoUrl = null;
        // Gestion de l'upload de la photo
        if (isset($validated['photo'])) {
            $photoUrl = ImageUploadHelper::uploadImage($validated['photo'], 'uploads/users/'.$validated['email']);
            if (!$photoUrl) {
                return ApiResponse::error('Erreur lors de l\'upload de la photo', 422);
            }
        }

        DB::beginTransaction();

        try {
            // Vérifier si l'email existe déjà
            if (User::where('email', $validated['email'])->exists()) {
                return ApiResponse::error('Cet email est déjà utilisé', 409);
            }

            // Créer l'utilisateur
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'contact' => $validated['contact'],
                'photo' => $photoUrl,  
                'est_actif' => false,
                'adresse' => $validated['adresse'],
                'mot_de_passe_a_changer' => false,
            ]);

            // Créer l'entité selon le type de demandeur
            switch ($validated['type_demandeur']) {
                case TypeDemandeurEnum::PHYSIQUE->value:
                    $this->authService->createClientPhysique($user, $validated);
                    $user->assignRole(RoleEnum::PHYSIQUE->value);
                    break;

                case TypeDemandeurEnum::ENTREPRISE->value: // Client moral (entreprise)
                    $this->authService->createEntreprise($user, $validated);
                    $user->assignRole(RoleEnum::ENTREPRISE->value);
                    break;

                default: // Prestataires de soins
                    $this->authService->createPrestataire($user, $validated);
                    $user->assignRole(RoleEnum::PRESTATAIRE->value);
                    break;
            }

            // Générer et envoyer l'OTP
            $otp = Otp::generateOtp($validated['email'], 10, OtpTypeEnum::REGISTER);


            // Envoyer l'OTP par email
            dispatch(new SendEmailJob(
                $user->email,
                'Validation de votre compte - SUNU Santé',
                'emails.otp_verification',
                [
                    'user' => $user,
                    'otp' => $otp,
                    'type_demandeur' => $validated['type_demandeur']
                ]
            ));

            DB::commit();

            return ApiResponse::success([
                'user' => new UserResource($user->load('client', 'entreprise', 'prestataire')),
            ], 'Inscription réussie. Vérifiez votre email pour valider votre compte.'); 
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'inscription: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de l\'inscription', 500, $e->getMessage());
        }
    }

       /**
     * Vérifier l'OTP et activer le compte
     */
    public function verifyOtp(Request $request)
    {
        $user = User::where('email', $request['email'])
            ->first();

        if (!$user) {
            return ApiResponse::error('Aucun compte en attente de validation trouvé.', 404);
        }

        // Vérifier l'OTP
        $otp = Otp::where('email', $request['email'])
            ->where('otp', $request['otp'])
            ->whereNull('verifier_a')
            ->where('type', OtpTypeEnum::REGISTER)
            ->first();

        if (!$otp || $otp->isExpired()) {
            return ApiResponse::error('Code de validation invalide ou expiré.', 403);
        }

        DB::beginTransaction();

            try {
            // Activer le compte
            $user->update([
                'est_actif' => true,
                'email_verified_at' => now(),
                'verifier_a' => now()
            ]);

            DB::commit();

            // Envoyer email de bienvenue APRÈS le commit
            dispatch(new SendEmailJob(
                $user->email,
                'Compte validé - Bienvenue chez SUNU Santé',
                'emails.account_verified',
                ['user' => $user]
            ));

            // Générer le token JWT
            $token = $this->authService->generateToken($user);

            return ApiResponse::success([
                'user' => new UserResource($user->load('client', 'entreprise', 'prestataire')),
                'access_token' => $token,
            ], 'Votre compte a été validé avec succès. Vous pouvez maintenant vous connecter.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la validation du compte.', 500);
        }
    }
        /**
     * Authentification avec email et mot de passe
     */


    public function login(LoginWithEmailAndPasswordFormRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return ApiResponse::error('Identifiants incorrects ou compte non validé', 401);
        }

        // Vérifier si le mot de passe doit être changé
        if ($user->mot_de_passe_a_changer) {
            return ApiResponse::success(
                [
                    'mot_de_passe_a_changer' => $user->mot_de_passe_a_changer   ,
                ],
                'Changement de mot de passe obligatoire',
                202
            );
        }

        // Générer le token JWT
        $token = $this->authService->generateToken($user);

        // Envoyer notification de connexion
        dispatch(new SendLoginNotificationJob($user));

        return $this->authService->respondWithToken($token, $user);
    }


    /**
     * Envoyer un OTP pour validation email
     */
    // public function sendOtp(SendOtpFormRequest $request)
    // {
    //     $validated = $request->validated();

    //     $user = User::where('email', $validated['email'])
    //         ->first();

    //     if (!$user) {
    //         return ApiResponse::error('Aucun compte en attente de validation trouvé avec cet email.', 404);
    //     }

    //     // Générer un nouveau OTP
    //     $otp = Otp::generateOtp($validated['email'], 10, OtpTypeEnum::FORGOT_PASSWORD);

    //     // Envoyer l'OTP par email
    //     dispatch(new SendEmailJob(
    //         $user->email,
    //         'Code de validation - SUNU Santé',
    //         'emails.otp_verification',
    //         [
    //             'user' => $user,
    //             'otp' => $otp
    //         ]
    //     ));

    //     return ApiResponse::success([
    //         'email' => $user->email,
    //         'message' => 'OTP envoyé avec succès'
    //     ], 'Code de validation envoyé à votre email.');
    // }

    /**
     * Récupérer l'utilisateur connecté
     */
    public function getCurrentUser()
    {
        $user = Auth::user();
        if(is_null($user)) {
            return ApiResponse::error('Utilisateur non connecté', 400);
        }

        return ApiResponse::success(
            new UserResource($user),
            'Utilisateur récupéré avec succès'
        );
    }

    /**
     * Rafraîchir le token JWT
     */
    // public function refreshToken()
    // {
    //     try {
    //         $token = JWTAuth::refresh();
    //         $user = Auth::user();

    //         return $this->authService->respondWithToken($token, $user);
    //     } catch (JWTException $e) {
    //         return ApiResponse::error('Token invalide', 401);
    //     }
    // }

    /**
     * Déconnexion
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return ApiResponse::success(null, 'Déconnexion réussie');
        } catch (JWTException $e) {
            return ApiResponse::error('Erreur lors de la déconnexion', 500);
        }
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(ChangePasswordFormRequest $request)
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();
        // dd($user);
        // Vérifier l'ancien mot de passe
        if (!Hash::check($validated['current_password'], $user->password)) {
            return ApiResponse::error('Mot de passe actuel incorrect', 422);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($validated['new_password']),
            'mot_de_passe_a_changer' => false,
            'est_actif' => true,
        ]);
        dispatch(new SendEmailJob(
            $user->email,
            'Mot de passe modifié - SUNU Santé',
            EmailType::PASSWORD_CHANGED->value,
            [
                'user' => $user,
                'changed_at' => now()
            ]
        ));

        // Invalider tous les tokens existants
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            Log::warning('Impossible d\'invalider le token: ' . $e->getMessage());
        }

        return ApiResponse::success(null, 'Mot de passe modifié avec succès');
    }

    /**
     * Vérifier l'unicité d'un email
     */
    public function checkUnique(Request $request)
    {
        $request->validate([
            'field' => 'required|string',
            'value' => 'required|string'
        ]);

        $exists = User::where($request->field, $request->value)->exists();
       

        return ApiResponse::success([
            'exists' => $exists,
            'message' => $exists ? 'Ce ' . $request->field . ' est déjà utilisé' : 'Ce ' . $request->field . ' est disponible'
        ]);
    }

    /**
     * Test endpoint to check user roles
     */
    public function testRoles()
    {
        $user = Auth::user();

        return ApiResponse::success([
            'user_id' => $user->id,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'has_admin_global' => $user->hasRole('admin_global'),
            'has_gestionnaire' => $user->hasRole('gestionnaire'),
            'all_roles' => $user->getAllPermissions()->pluck('name'),
        ], 'User roles information');
    }
}
