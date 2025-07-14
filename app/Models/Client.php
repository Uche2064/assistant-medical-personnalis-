<?php

namespace App\Models;

use App\Enums\TypeClientEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

        protected $fillable = ['user_id', 'commercial_id', 'profession', 'type_client'];


    protected function casts(): array
    {
        return [
            'type_client' => TypeClientEnum::class,
        ];
    }

    // Relation vers l'user
   public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commercial()
    {
        return $this->belongsTo(Personnel::class, 'commercial_id');
    }

    public function contrats()
    {
        return $this->belongsToMany(Contrat::class, 'client_contrat')
                    ->withPivot(['numero_police', 'date_debut', 'date_fin'])
                    ->withTimestamps();
    }

    public function assures()
    {
        return $this->hasMany(Assure::class);
    }

    protected static function booted()
    {
        static::deleting(function ($client) {
            if ($client->user) {
                $client->user->delete();
            }
        });
    }
}
