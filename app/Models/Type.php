<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Type extends Model
{
    protected $fillable = ['name', 'color'];

    /**
     * Pokémon que tienen este tipo.
     */
    public function pokemon(): BelongsToMany
    {
        return $this->belongsToMany(Pokemon::class, 'pokemon_type')
            ->withPivot('slot')
            ->withTimestamps();
    }
}