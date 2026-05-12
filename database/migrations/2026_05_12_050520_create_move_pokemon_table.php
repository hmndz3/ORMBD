<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivote Pokémon ↔ Movimiento. Cada pokémon aprende muchos movimientos.
     */
    public function up(): void
    {
        Schema::create('move_pokemon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')
                ->constrained('pokemon')
                ->cascadeOnDelete();
            $table->foreignId('move_id')
                ->constrained('moves')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('learn_level')->nullable(); // nivel al que lo aprende
            $table->timestamps();

            $table->unique(['pokemon_id', 'move_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('move_pokemon');
    }
};