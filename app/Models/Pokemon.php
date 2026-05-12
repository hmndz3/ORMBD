<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pokemon extends Model
{
    /**
     * Forzamos el nombre de la tabla porque Laravel pluralizaría a "pokemons".
     */
    protected $table = 'pokemon';

    protected $fillable = [
        'pokedex_number',
        'name',
        'height',
        'weight',
        'base_experience',
        'hp',
        'attack',
        'defense',
        'special_attack',
        'special_defense',
        'speed',
        'is_legendary',
        'sprite_url',
        'generation_id',
        'region_id',
    ];

    protected $casts = [
        'pokedex_number' => 'integer',
        'height' => 'integer',
        'weight' => 'integer',
        'base_experience' => 'integer',
        'hp' => 'integer',
        'attack' => 'integer',
        'defense' => 'integer',
        'special_attack' => 'integer',
        'special_defense' => 'integer',
        'speed' => 'integer',
        'is_legendary' => 'boolean',
    ];

    /**
     * Generación a la que pertenece este pokémon.
     */
    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class);
    }

    /**
     * Región de origen del pokémon.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Tipos del pokémon (puede tener 1 o 2).
     */
    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class, 'pokemon_type')
            ->withPivot('slot')
            ->withTimestamps();
    }

    /**
     * Habilidades del pokémon.
     */
    public function abilities(): BelongsToMany
    {
        return $this->belongsToMany(Ability::class, 'ability_pokemon')
            ->withPivot('is_hidden')
            ->withTimestamps();
    }

    /**
     * Movimientos que el pokémon puede aprender.
     */
    public function moves(): BelongsToMany
    {
        return $this->belongsToMany(Move::class, 'move_pokemon')
            ->withPivot('learn_level')
            ->withTimestamps();
    }

    /**
     * Entrenadores que tienen este pokémon en su equipo.
     */
    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(Trainer::class, 'pokemon_trainer')
            ->withPivot('nickname', 'level')
            ->withTimestamps();
    }
}