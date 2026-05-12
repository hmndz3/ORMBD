<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PokemonTrainer extends Pivot
{
    protected $table = 'pokemon_trainer';

    protected $fillable = ['pokemon_id', 'trainer_id', 'nickname', 'level'];

    protected $casts = [
        'level' => 'integer',
    ];

    public $timestamps = true;
}