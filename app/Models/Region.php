<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = ['name', 'main_city'];

    /**
     * Pokémon originarios de esta región.
     */
    public function pokemon(): HasMany
    {
        return $this->hasMany(Pokemon::class);
    }
}