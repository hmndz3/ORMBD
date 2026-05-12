<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AbilityPokemon extends Pivot
{
    protected $table = 'ability_pokemon';

    protected $fillable = ['pokemon_id', 'ability_id', 'is_hidden'];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public $timestamps = true;
}