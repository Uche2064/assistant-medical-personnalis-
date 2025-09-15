<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personne extends Model
{
    use HasFactory;

    protected $table = 'personnes';

    protected $fillable = [
        'nom',
        'prenoms',
        'date_naissance',
        'sexe',
        'profession',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
