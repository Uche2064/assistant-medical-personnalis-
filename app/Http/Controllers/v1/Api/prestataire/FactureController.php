<?php

namespace App\Http\Controllers\v1\Api\prestataire;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Auth;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $query = Facture::query();
        // pour les medecins controleur, techniciens, comptables, on récupère toutes les factures
        if (Auth::user()->hasRole('medecin_controleur') || Auth::user()->hasRole('technicien') || Auth::user()->hasRole('comptable')) {
            $query->where('prestataire_id', Auth::user()->id);
        } else {
            $query->where('prestataire_id', Auth::user()->id);
        }

        // options de filtres

        $factures = $query->get();

        return ApiResponse::success($factures, 'Factures récupérées avec succès', 200);
    }
}
