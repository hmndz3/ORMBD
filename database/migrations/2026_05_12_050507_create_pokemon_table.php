<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla principal de Pokémon. Cada pokémon pertenece a una generación
     * y opcionalmente a una región.
     */
    public function up(): void
    {
        Schema::create('pokemon', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('pokedex_number')->unique();
            $table->string('name', 100);
            $table->unsignedSmallInteger('height')->nullable(); // en decímetros
            $table->unsignedSmallInteger('weight')->nullable(); // en hectogramos
            $table->unsignedSmallInteger('base_experience')->nullable();
            $table->unsignedSmallInteger('hp')->default(0);
            $table->unsignedSmallInteger('attack')->default(0);
            $table->unsignedSmallInteger('defense')->default(0);
            $table->unsignedSmallInteger('special_attack')->default(0);
            $table->unsignedSmallInteger('special_defense')->default(0);
            $table->unsignedSmallInteger('speed')->default(0);
            $table->boolean('is_legendary')->default(false);
            $table->string('sprite_url', 500)->nullable();

            // Foreign keys
            $table->foreignId('generation_id')
                ->nullable()
                ->constrained('generations')
                ->nullOnDelete();
            $table->foreignId('region_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon');
    }
};