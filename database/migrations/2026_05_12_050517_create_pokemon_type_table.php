<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivote Pokémon ↔ Tipo. Un pokémon tiene 1 o 2 tipos.
     */
    public function up(): void
    {
        Schema::create('pokemon_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pokemon_id')
                ->constrained('pokemon')
                ->cascadeOnDelete();
            $table->foreignId('type_id')
                ->constrained('types')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('slot')->default(1); // 1 = primario, 2 = secundario
            $table->timestamps();

            $table->unique(['pokemon_id', 'type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon_type');
    }
};