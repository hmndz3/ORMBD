<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivote Pokémon ↔ Entrenador. Un entrenador tiene varios pokémon
     * y un pokémon puede aparecer en el equipo de varios entrenadores.
     */
    public function up(): void
    {
        Schema::create('pokemon_trainer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')
                ->constrained('pokemon')
                ->cascadeOnDelete();
            $table->foreignId('trainer_id')
                ->constrained('trainers')
                ->cascadeOnDelete();
            $table->string('nickname', 100)->nullable();
            $table->unsignedTinyInteger('level')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon_trainer');
    }
};