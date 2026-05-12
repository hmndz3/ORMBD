<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivote Pokémon ↔ Habilidad. Un pokémon puede tener varias habilidades
     * y una habilidad puede pertenecer a varios pokémon.
     */
    public function up(): void
    {
        Schema::create('ability_pokemon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')
                ->constrained('pokemon')
                ->cascadeOnDelete();
            $table->foreignId('ability_id')
                ->constrained('abilities')
                ->cascadeOnDelete();
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->unique(['pokemon_id', 'ability_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ability_pokemon');
    }
};