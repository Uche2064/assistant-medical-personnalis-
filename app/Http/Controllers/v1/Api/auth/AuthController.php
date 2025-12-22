<?php

namespace App\Http\Controllers\v1\Api\auth;

use App\Enums\ClientTypeEnum;
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
use App\Http\Requests\auth\CheckUniqueFieldRequest;
use App\Http\Requests\auth\LoginWithEmailAndPasswordFormRequest;
use App\Http\Requests\auth\RegisterRequest;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Http\Requests\auth\SendOtpFormRequest;
use App\Http\Requests\auth\VerifyOtpFormRequest;
use App\Http\Requests\auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendEmailJob;
use App\Jobs\SendLoginNotificationJob;
use App\Models\Client;
use App\Models\CommercialParrainageCode;
use App\Models\Entreprise;
use App\Models\Otp;
use App\Models\Prestataire;
use App\Models\Personne;
use App\Models\User;
use App\Services\AuthService;
use App\Services\NotificationService;
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
    protected NotificationService $notificationService;

    public function __construct(AuthService $authService, NotificationService $notificationService)
    {
        $this->authService = $authService;
        $this->notificationService = $notificationService;
    }

    /**
     * Inscription d'un demandeur (physique, moral, ou prestataire)
     */
    public function register(RegisterRequest $request)
    {
        Log::info('Register request: ' . json_encode($request->all()));
        $validated = $request->validated();
        $otp_expired_at = (int) env('OTP_EXPIRED_AT', 10);
        $photoUrl = null;
        // Gestion de l'upload de la photo
        if (isset($validated['photo'])) {
            $photoUrl = ImageUploadHelper::uploadImage($validated['photo'], 'uploads', $validated['email'], 'user_photo');
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

            // Gestion du code parrainage (optionnel pour l'inscription client)
            $commercialId = null;
            $commercial = null;
            $codeParrainage = null;
            if (isset($validated['code_parrainage']) && !empty($validated['code_parrainage'])) {
                // Chercher le code de parrainage dans la table commercial_parrainage_codes
                $parrainageCode = CommercialParrainageCode::where('code_parrainage', $validated['code_parrainage'])
                    ->where('est_actif', true)
                    ->where('date_expiration', '>=', now())
                    ->first();

                if ($parrainageCode) {
                    $commercial = User::find($parrainageCode->commercial_id);
                    if ($commercial && $commercial->hasRole(RoleEnum::COMMERCIAL->value)) {
                        $commercialId = $commercial->id;
                        $codeParrainage = $validated['code_parrainage'];
                    } else {
                        return ApiResponse::error('Code parrainage invalide', 400);
                    }
                } else {
                    return ApiResponse::error('Code parrainage invalide ou expiré', 400);
                }
            }
            // Créer d'abord la personne
            $personne = Personne::create([
                'nom' => $validated['nom'] ?? null,
                'prenoms' => $validated['prenoms'] ?? null,
                'date_naissance' => $validated['date_naissance'] ?? null,
                'sexe' => $validated['sexe'] ?? null,
                'profession' => $validated['profession'] ?? null,
            ]);
            // Créer l'utilisateur avec le personne_id
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'contact' => $validated['contact'],
                'photo_url' => $photoUrl,
                'est_actif' => false,
                'adresse' => $validated['adresse'],
                'mot_de_passe_a_changer' => false,
                'personne_id' => $personne->id,
            ]);
            // Créer l'entité selon le type de demandeur
            switch ($validated['type_demandeur']) {
                case TypeDemandeurEnum::CLIENT->value:
                    $client = null;
                    if($validated['type_client'] === ClientTypeEnum::PHYSIQUE) {
                        $client = $this->authService->createClientPhysique($user, $validated, $codeParrainage);
                    } else {
                        $client = $this->authService->createClientMoral($user, $validated, $codeParrainage);
                    }

                    // Mettre à jour le client avec commercial_id si un code a été fourni
                    if ($client && $commercialId && $codeParrainage) {
                        $client->update([
                            'commercial_id' => $commercialId,
                        ]);

                        // Notifier le commercial qu'un nouveau client s'est inscrit avec son code de parrainage
                        $this->notificationService->notifyCommercialNouveauClient($client, $commercial);
                    }

                    $user->assignRole(RoleEnum::CLIENT->value);
                    // Notifier les techniciens d'un nouveau compte physique
                    $this->notificationService->notifyTechniciensNouveauCompte($user, 'client');
                    break;
                default: // Prestataires de soins
                    $this->authService->createPrestataire($user, $validated);
                    $user->assignRole(RoleEnum::PRESTATAIRE->value);
                    // Notifier les médecins contrôleurs d'un nouveau prestataire
                    $this->notificationService->notifyMedecinsControleursNouveauPrestataire($user);
                    break;
            }
            // Générer et envoyer l'OTP
            $otp = Otp::generateOtp($validated['email'], $otp_expired_at, OtpTypeEnum::REGISTER->value);
            // Envoyer l'OTP par email
            dispatch(new SendEmailJob(
                $user->email,
                'Validation de votre compte - SUNU Santé',
                'emails.otp_verification',
                [
                    'user' => $user,
                    'otp' => $otp,
                    'expire_at' => now()->addMinutes($otp_expired_at),
                    'type_demandeur' => $validated['type_demandeur']
                ]
            ));
            Log::info("Email:" . $validated['email'] . " otp: " . $otp);
            DB::commit();
            return ApiResponse::success(new UserResource($user->load('prestataire', 'assure', 'personne')), 'Inscription réussie. Vérifiez votre email pour valider votre compte.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'inscription: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de l\'inscription', 500, $e->getMessage());
        }
    }

    /**
     * Vérifier l'OTP et activer le compte
     */
    public function verifyOtp(VerifyOtpRequest $request)
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
            ->where('type', $request['type'])
            ->first();

        Log::info($otp);
        if (!$otp || $otp->isExpired()) {
            return ApiResponse::error('Code de validation invalide ou expiré.', 403);
        }

        DB::beginTransaction();

        try {
            // Activer le compte
            $user->update([
                'est_actif' => true,
                'email_verifier_a' => now(),
                'verifier_a' => now()
            ]);

            $otp->delete();
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
                'access_token' => $token,
                'user' => new UserResource($user->load('prestataire', 'assure', 'personne')),
            ], 'Votre compte a été validé avec succès.');
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

        $user = User::where('email', $validated['email'])->first();

        // 1. Vérifier si l'utilisateur existe
        if (!$user) {
            return ApiResponse::error('Identifiants invalides', 401);
        }

        // 2. Vérifier statut du compte (bloqué ou non)
        $this->authService->checkAccountStatus($user);

        // 3. Vérifier le mot de passe
        if (!Hash::check($validated['password'], $user->password)) {
            // 4. Gérer tentative échouée
            $this->authService->handleFailedAttempt($user);
            return ApiResponse::error('Identifiants invalides', 401);
        }

        // 5. Succès → reset des tentatives
        $this->authService->resetAttempts($user);

        // 6. Vérifier si le mot de passe doit être changé
        if ($user->mot_de_passe_a_changer) {
            return ApiResponse::success(
                ['mot_de_passe_a_changer' => $user->mot_de_passe_a_changer],
                'Changement de mot de passe obligatoire',
                202
            );
        }

        // 7. Générer le token JWT
        $token = $this->authService->generateToken($user);

        // 8. Envoyer notification de connexion
        dispatch(new SendLoginNotificationJob($user));

        // 9. Retourner la réponse avec le token
        return $this->authService->respondWithToken($token, $user);
    }

    /**
     * Récupérer l'utilisateur connecté
     */
    public function getCurrentUser()
    {
        $user = Auth::user();
        if (is_null($user)) {
            return ApiResponse::error('Utilisateur non connecté', 400);
        }

        return ApiResponse::success(
            new UserResource($user),
            'Utilisateur récupéré avec succès'
        );
    }
    /**
     * Envoyer un OTP pour validation email
     */
    public function sendOtp(SendOtpFormRequest $request)
    {
        $validated = $request->validated();
        $otp_expired_at = (int) env('OTP_EXPIRED_AT', 10);

        $user = User::where('email', $validated['email'])
            ->first();

        if (!$user) {
            return ApiResponse::error('Aucun compte en attente de validation trouvé avec cet email.', 404);
        }

        // Générer un nouveau OTP
        $otp = Otp::generateOtp($validated['email'], 10, $validated['type']);
        Log::info("Email:" . $validated['email'] . " otp: " . $otp);

        // Envoyer l'OTP par email

        // Envoyer l'OTP par email
        dispatch(new SendEmailJob(
            $user->email,
            'Validation de votre compte - SUNU Santé',
            'emails.otp_verification',
            [
                'user' => $user,
                'otp' => $otp,
                'expire_at' => now()->addMinutes($otp_expired_at),
            ]
        ));

        return ApiResponse::success([
            'email' => $user->email,
            'message' => 'OTP envoyé avec succès'
        ], 'Code de validation envoyé à votre email.');
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
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        Log::info('Reset password request: ' . json_encode($validated));
        // $resetToken = $validated['token'];
        $newPassword = $validated['new_password'];
        $email = $validated['email'];


        // Récupérer l'utilisateur
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ApiResponse::error('Utilisateur non trouvé.', 404);
        }

        if(Hash::check($validated['new_password'], $user->password)) {
            return ApiResponse::error('Entrez un mot de passe différent du précédent', 400);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($validated['new_password']),
            'mot_de_passe_a_changer' => false,
            'est_actif' => true,
            'email_verifier_a' => now()
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
          // Invalider tous les tokens existants
          try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            Log::warning('Impossible d\'invalider le token: ' . $e->getMessage());
        }

        return ApiResponse::success(null, 'Mot de passe modifié avec succès.');
    }

      /**
     * Changer le mot de passe
     */
    public function changePassword(ChangePasswordFormRequest $request)
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();
        // Vérifier l'ancien mot de passe
        if (!Hash::check($validated['current_password'], $user->password)) {
            return ApiResponse::error('Mot de passe actuel incorrect', 422);
        }
        if(Hash::check($validated['new_password'], $user->password)) {
            return ApiResponse::error('Entrez un mot de passe différent du précédent', 400);
        }
        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($validated['new_password']),
            'mot_de_passe_a_changer' => false,
            'est_actif' => true,
            'email_verifier_a' => now()
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
    public function checkUnique(CheckUniqueFieldRequest $request)
    {
        $validated = $request->validated();

        $exists = User::where($validated['champ'], $validated['valeur'])->exists();


        return ApiResponse::success([
            'exists' => $exists,
            'message' => $exists ? 'Ce ' . $validated['champ'] . ' est déjà utilisé' : 'Ce ' . $validated['champ'] . ' est disponible'
        ]);
    }
}
