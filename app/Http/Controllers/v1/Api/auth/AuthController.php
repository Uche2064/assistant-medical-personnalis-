<?php

namespace App\Http\Controllers\v1\Api\auth;

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
        if (isset($validated['photo_url'])) {
            $photoUrl = ImageUploadHelper::uploadImage($validated['photo_url'], 'uploads/users/personnels');
            if (!$photoUrl) {
                return ApiResponse::error('Erreur lors de l\'upload de la photo', 422);
            }
        }

        DB::beginTransaction();

        try {
            // Vérifier si l'email existe déjà
            if (User::where('email', $validated['email'])->exists()) {
                return ApiResponse::error('Cet email est déjà utilisé', 422);
            }

            // Créer l'utilisateur
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'contact' => $validated['contact'],
                'photo_url' => $photoUrl,  
                'adresse' => $validated['adresse'],
                'est_actif' => false, // Inactif jusqu'à validation OTP
                'mot_de_passe_a_changer' => false,
                'email_verified_at' => null, // Sera vérifié après OTP
            ]);

            // Créer l'entité selon le type de demandeur
            switch ($validated['type_demandeur']) {
                case TypeDemandeurEnum::PHYSIQUE->value:
                    $this->createClientPhysique($user, $validated);
                    $user->assignRole(RoleEnum::USER->value);
                    break;

                case TypeDemandeurEnum::AUTRE->value: // Client moral (entreprise)
                    $this->createEntreprise($user, $validated);
                    $user->assignRole(RoleEnum::USER->value);
                    break;

                default: // Prestataires de soins
                    $this->createPrestataire($user, $validated);
                    $user->assignRole(RoleEnum::USER->value);
                    break;
            }

            // Générer et envoyer l'OTP
            $otp = Otp::generateOtp($validated['email']);


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
                'user' => new UserResource($user->fresh()->load('client', 'entreprise', 'prestataire')),
                'email' => $user->email,
                'type_demandeur' => $validated['type_demandeur'],
            ], 'Inscription réussie. Vérifiez votre email pour valider votre compte.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'inscription: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de l\'inscription', 500);
        }
    }

    /**
     * Envoyer un OTP pour validation email
     */
    public function sendOtp(SendOtpFormRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])
            ->where('est_actif', false)
            ->first();

        if (!$user) {
            return ApiResponse::error('Aucun compte en attente de validation trouvé avec cet email.', 404);
        }

        // Générer un nouveau OTP
        $otp = Otp::generateOtp($validated['email']);

        // Envoyer l'OTP par email
        dispatch(new SendEmailJob(
            $user->email,
            'Code de validation - SUNU Santé',
            'emails.otp_verification',
            [
                'user' => $user,
                'otp' => $otp
            ]
        ));

        return ApiResponse::success([
            'email' => $user->email,
            'message' => 'OTP envoyé avec succès'
        ], 'Code de validation envoyé à votre email.');
    }

    /**
     * Vérifier l'OTP et activer le compte
     */
    public function verifyOtp(VerifyOtpFormRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])
            ->where('est_actif', false)
            ->first();

        if (!$user) {
            return ApiResponse::error('Aucun compte en attente de validation trouvé.', 404);
        }

        // Vérifier l'OTP
        $otp = Otp::where('email', $validated['email'])
            ->where('otp', $validated['otp'])
            ->whereNull('verifier_a')
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
            ]);

            // Marquer l'OTP comme vérifié
            $otp->update(['verifier_a' => now()]);

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
                'message' => 'Compte validé avec succès'
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
                    'mot_de_passe_a_changer' => true,
                    'user_id' => $user->id
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
     * Récupérer l'utilisateur connecté
     */
    public function getCurrentUser()
    {
        if (!Auth::check()) {
            return ApiResponse::error('Utilisateur non connecté', 401);
        }

        $user = Auth::user()->load(['roles', 'client', 'entreprise', 'prestataire']);

        return ApiResponse::success(
            new UserResource($user),
            'Utilisateur récupéré avec succès'
        );
    }

    /**
     * Rafraîchir le token JWT
     */
    public function refreshToken()
    {
        try {
            $token = JWTAuth::refresh();
            $user = Auth::user();

            return $this->authService->respondWithToken($token, $user);
        } catch (JWTException $e) {
            return ApiResponse::error('Token invalide', 401);
        }
    }

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
     * Vérifier l'unicité d'un contact
     */
    public function checkContactUnique(Request $request)
    {
        $request->validate([
            'contact' => 'required|string'
        ]);

        $exists = User::where('contact', $request->contact)->exists();

        return ApiResponse::success([
            'available' => !$exists,
            'message' => $exists ? 'Contact déjà utilisé' : 'Contact disponible'
        ]);
    }

    /**
     * Créer un client physique
     */
    private function createClientPhysique(User $user, array $validated): void
    {
        Client::create([
            'user_id' => $user->id,
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'],
            'date_naissance' => $validated['date_naissance'],
            'sexe' => $validated['sexe'],
            'profession' => $validated['profession'] ?? null,
            'type_client' => TypeClientEnum::PHYSIQUE,
            'statut' => StatutClientEnum::PROSPECT,
            'code_parrainage' => $validated['code_parrainage'] ?? null,
        ]);
    }

    /**
     * Créer une entreprise
     */
    private function createEntreprise(User $user, array $validated): void
    {
        Entreprise::create([
            'user_id' => $user->id,
            'raison_sociale' => $validated['raison_sociale'],
            'statut' => StatutClientEnum::PROSPECT,
            'code_parrainage' => $validated['code_parrainage'] ?? null,
        ]);
    }

    /**
     * Créer un prestataire de soins
     */
    private function createPrestataire(User $user, array $validated): void
    {
        $typePrestataire = match ($validated['type_demandeur']) {
            TypeDemandeurEnum::CENTRE_DE_SOINS->value => TypePrestataireEnum::CENTRE_DE_SOINS,
            TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC->value => TypePrestataireEnum::LABORATOIRE_CENTRE_DIAGNOSTIC,
            TypeDemandeurEnum::PHARMACIE->value => TypePrestataireEnum::PHARMACIE,
            TypeDemandeurEnum::OPTIQUE->value => TypePrestataireEnum::OPTIQUE,
            default => TypePrestataireEnum::CENTRE_DE_SOINS, // Fallback
        };

        Prestataire::create([
            'user_id' => $user->id,
            'type_prestataire' => $typePrestataire,
            'documents_requis' => $validated['documents_requis'],
            'code_parrainage' => $validated['code_parrainage'] ?? null,
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
