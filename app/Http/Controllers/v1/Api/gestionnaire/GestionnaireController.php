<?php

namespace App\Http\Controllers\v1\Api\gestionnaire;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\gestionnaire\StorePersonnelRequest;
use App\Jobs\SendCredentialsJob;
use App\Models\Personnel;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class GestionnaireController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }


    public function indexPersonnels(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Personnel::with('user', 'user.roles')->where('gestionnaire_id', '!=', null);


        $query->whereHas('user', function ($q) use ($request) {
            if ($request->filled('sexe')) {
                $q->where('sexe', $request->sexe);
            }

            if ($request->filled('est_actif')) {
                $q->where('est_actif', filter_var($request->est_actif, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $q->where(function ($q2) use ($search) {
                    $q2->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenoms', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }
        });

        if ($request->filled('role')) {
            $query->whereHas('user.roles', function ($qr) use ($request) {
                $qr->where('name', $request->role);
            });
        }

        $personnels = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ApiResponse::success($personnels, 'Liste des personnels récupérée avec succès');
    }


    public function storePersonnel(StorePersonnelRequest $request)
    {
        try {
            $validated = $request->validated();
            $password = User::genererMotDePasse();
            $photoUrl = null;
            if (isset($validated['photo_url'])) {
                $photo = $validated['photo_url'];
                $photoUrl = ImageUploadHelper::uploadImage($photo, 'uploads/users');
            } else {
                $photo = null;
            }

            // créer l'user
            $user = User::create([
                'nom' => $validated['nom'],
                'prenoms' => $validated['prenoms'] ?? null,
                'email' => $validated['email'],
                'contact' => $validated['contact'] ?? null,
                'adresse' => $validated['adresse'],
                'date_naissance' => $validated['date_naissance'] ?? null,
                'sexe' => $validated['sexe'] ?? null,
                'photo_url' => $photoUrl,
                'mot_de_passe_a_changer' => true,
                'est_actif' => false,
                'password' => Hash::make($password),
            ]);

            $user->assignRole($validated['role']);
            $user->save();

            // Création du personnel
            if ($validated['role'] == RoleEnum::COMMERCIAL->value) {
                 Personnel::create([
                    'user_id' => $user->id,
                    'code_parainage' => Personnel::genererCodeParainage(),
                    'gestionnaire_id' => Auth::user()->personnel->id,
                ]);
            } else {
                Personnel::create([
                    'user_id' => $user->id,
                    'gestionnaire_id' => Auth::user()->personnel->id,
                ]);
            }

            SendCredentialsJob::dispatch($user, $password);

            $reponseData = [
                "personnel" => $user->load('roles', 'personnel'),
                "password" => $password
            ];
            return ApiResponse::success($reponseData, 'Un email de confirmation a été envoyé à l\'adresse email fournie.', 201);
        } catch (Throwable $th) {
            return ApiResponse::error('Une erreur est survenue lors de la création du personnel', 500, $th->getMessage());
        }
    }



    public function showPersonnel(int $id)
    {
        try {
            $personnel = Personnel::with('user')->where('gestionnaire_id', '!=', null)->find($id);
            if (!$personnel) {
                return ApiResponse::error('Gestionnaire non trouvé', 404);
            }

            
            return ApiResponse::success($personnel, 'Gestionnaire récupéré avec succès');
        } catch (\Throwable $th) {
            return ApiResponse::error('Une erreur est survenue lors de la récupération du personnel', 500, $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyPersonnel(int $id)
    {
        $personnel = Personnel::with('user')->find($id);
        if (!$personnel) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }
        $personnel->delete();
        $personnel->user->delete();
        return ApiResponse::success(null, 'Gestionnaire supprimé avec succès', 204);
    }
}
