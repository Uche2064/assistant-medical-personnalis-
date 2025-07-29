<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonnelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // The base of our resource is the UserResource
        $userResource = new UserResource($this->whenLoaded('user'));

        // We return the user data, and add/override with personnel-specific data
        return array_merge($userResource->toArray($request), [
            'id' => $this->id, // personnel id
            'user_id' => $this->user_id,
            'sexe' => $this->sexe,
            'date_naissance' => $this->date_naissance,
            'code_parainage' => $this->code_parainage,
            'gestionnaire_id' => $this->gestionnaire_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
