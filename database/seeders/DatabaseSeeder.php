<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Orquesta todos los seeders del dominio Pokémon.
     * El orden importa: catálogos primero, luego Pokémon (que usa FKs),
     * y al final entrenadores con sus equipos.
     */
    public function run(): void
    {
        $this->call([
            // Catálogos (sin dependencias)
            TypeSeeder::class,
            GenerationSeeder::class,
            RegionSeeder::class,
            AbilitySeeder::class,
            MoveSeeder::class,
            // Pokémon (usa FKs hacia catálogos + sincroniza pivotes)
            PokemonSeeder::class,
            // Entrenadores y equipos se agregan en el siguiente commit
        ]);
    }
}