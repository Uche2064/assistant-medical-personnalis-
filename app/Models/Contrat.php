<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contrat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'numero_police',
        'technicien_id',
        'date_signature',
        'status',
        'photo_document',
        'prime',
    ];

    protected $casts = [
        'date_signature' => 'date',
        'prime' => 'decimal:2',
        'photo_document' => 'json',
    ];


    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    
    public function technicien()
    {
        return $this->belongsTo(Personnel::class, 'technicien_id');
    }

}
