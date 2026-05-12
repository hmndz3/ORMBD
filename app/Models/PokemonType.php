<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PokemonType extends Pivot
{
    protected $table = 'pokemon_type';

    protected $fillable = ['pokemon_id', 'type_id', 'slot'];

    protected $casts = [
        'slot' => 'integer',
    ];

    public $timestamps = true;
}