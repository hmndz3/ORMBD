<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de movimientos (placaje, lanzallamas, etc.).
     */
    public function up(): void
    {
        Schema::create('moves', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedSmallInteger('power')->nullable();
            $table->unsignedTinyInteger('accuracy')->nullable(); // 0-100
            $table->unsignedTinyInteger('pp')->nullable();
            $table->enum('damage_class', ['physical', 'special', 'status'])->default('physical');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moves');
    }
};