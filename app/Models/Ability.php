<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ability extends Model
{
    protected $fillable = ['name', 'description', 'is_hidden'];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    /**
     * Pokémon que poseen esta habilidad.
     */
    public function pokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'ability_pokemon')
            ->withPivot('is_hidden')
            ->withTimestamps();
    }
}