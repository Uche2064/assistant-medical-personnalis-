<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReseauPrestatairee extends Model
{
    protected $fillable = [
        'prestataire_id',
        'client_id',
        'date_creation',
    ];

    protected function casts(): array
    {
        return [
            'date_creation' => 'datetime',
        ];
    }

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
