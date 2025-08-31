<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $contratAssocie = $this->getContratAssocie();

        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenoms' => $this->prenoms,
            'date_naissance' => $this->date_naissance ? $this->date_naissance->format('Y-m-d') : null,
            'email' => $this->email,
            'contact' => $this->contact,
            'sexe' => $this->sexe,
            'profession' => $this->profession,
            'est_principal' => $this->est_principal,
            'entreprise' => $this->entreprise ? [
                'id' => $this->entreprise->user->id,
                'raison_sociale' => $this->entreprise->raison_sociale,
                'email' => $this->entreprise->user->email,
                'contact' => $this->entreprise->user->contact,  
                'adresse' => $this->entreprise->adresse,
                // autres infos si besoin
            ] : null,
            'assure_principal' => $this->assurePrincipal ? [
                'id' => $this->assurePrincipal->id,
                'nom' => $this->assurePrincipal->nom,
                'prenoms' => $this->assurePrincipal->prenoms,
                // autres infos si besoin
            ] : null,
            'photo' => $this->photo,
            'lien_parente' => $this->lien_parente,

            'contrat' => $contratAssocie ? [
                'id' => $contratAssocie->contrat->id,
                'numero_police' => $contratAssocie->numero_police,
                'date_debut' => $contratAssocie->date_debut ? $contratAssocie->date_debut->format('Y-m-d') : null,
                'date_fin' => $contratAssocie->date_fin ? $contratAssocie->date_fin->format('Y-m-d') : null,
                'statut' => $contratAssocie->statut,

                // Garanties aplaties, pas groupées par catégorie
                'garanties' => $contratAssocie->contrat->garanties->map(function ($garantie) {
                    return [
                        'id' => $garantie->id,
                        'libelle' => $garantie->libelle,
                        'prix_standard' => $garantie->prix_standard,
                        'taux_couverture' => $garantie->taux_couverture,
                    ];
                }),
            ] : null,
        ];
    }
}
