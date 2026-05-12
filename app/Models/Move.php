<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Move extends Model
{
    protected $fillable = ['name', 'power', 'accuracy', 'pp', 'damage_class'];

    protected $casts = [
        'power' => 'integer',
        'accuracy' => 'integer',
        'pp' => 'integer',
    ];

    /**
     * Pokémon que pueden aprender este movimiento.
     */
    public function pokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'move_pokemon')
            ->withPivot('learn_level')
            ->withTimestamps();
    }
}