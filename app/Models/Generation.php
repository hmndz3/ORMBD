<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Generation extends Model
{
    protected $fillable = ['name', 'number', 'release_year'];

    protected $casts = [
        'number' => 'integer',
        'release_year' => 'integer',
    ];

    /**
     * Pokémon introducidos en esta generación.
     */
    public function pokemon(): HasMany
    {
        return $this->hasMany(Pokemon::class);
    }
}