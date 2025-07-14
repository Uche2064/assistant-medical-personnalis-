<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientContrat extends Model
{
    /** @use HasFactory<\Database\Factories\ClientContratFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'contrat_id',
        'numero_police',
        'date_debut',
        'date_fin',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }
}
