<?php

namespace App\Http\Controllers\v1\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Assure;
use App\Models\Otp;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Le numero de telephone est requis', 422, $validator->errors());
        }

        $validatedData = $validator->validated();

        // // Vérifier que le numéro appartient à un assuré existant
        // $assureExists = Assure::whereHas('utilisateur', function ($query) use ($validatedData) {
        //     $query->where('contact', $validatedData['phone']);
        // })->exists();

        // if (!$assureExists) {
        //     return ApiResponse::error(
        //         "Ce numéro n'est pas encore enregistré comme assuré.",
        //         403
        //     );
        // }

        // Générer un OTP de 6 chiffres
        $otp = Otp::generateCode();

        // Sauvegarder l'OTP dans la base de données
        try {
            Otp::updateOrCreateOtp($validatedData['phone'], $otp);
        } catch (Exception $e) {
            return ApiResponse::error("Erreur lors de l'enregistrement de l'OTP.", 500);
        }


        return ApiResponse::success([
            'otp' => $otp // À supprimer en production, c'est juste pour le test
        ], 'OTP envoyé avec succès', 200);
    }

    /**
     * Vérifie un OTP envoyé à un téléphone.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric',
            'otp'   => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(
                'Numéro ou OTP manquant ou invalide',
                422,
                $validator->errors()
            );
        }

        $validated = $validator->validated();

        // Récupérer l'OTP valide correspondant au téléphone et au code
        $otp = Otp::where('phone', $validated['phone'])
            ->where('code_otp', $validated['otp'])
            ->whereNull('verifier_a') // non encore utilisé
            ->first();

        // OTP introuvable ou expiré
        if (!$otp || $otp->isExpired()) {
            return ApiResponse::error('OTP invalide ou expiré.', 403);
        }

        // Marquer comme vérifié
        $otp->verifier_a = now();
        $otp->save();

        return ApiResponse::success(
            null,
            'OTP vérifié avec succès.',
            200
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
