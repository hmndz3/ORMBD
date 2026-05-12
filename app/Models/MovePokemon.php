<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MovePokemon extends Pivot
{
    protected $table = 'move_pokemon';

    protected $fillable = ['pokemon_id', 'move_id', 'learn_level'];

    protected $casts = [
        'learn_level' => 'integer',
    ];

    public $timestamps = true;
}