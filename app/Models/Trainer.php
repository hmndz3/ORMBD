<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Trainer extends Model
{
    protected $fillable = ['name', 'email', 'age', 'badges'];

    protected $casts = [
        'age' => 'integer',
        'badges' => 'integer',
    ];

    /**
     * Pokémon que pertenecen al equipo de este entrenador.
     */
    public function pokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'pokemon_trainer')
            ->withPivot('nickname', 'level')
            ->withTimestamps();
    }
}