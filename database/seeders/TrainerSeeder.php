<?php

namespace Database\Seeders;

use App\Models\Pokemon;
use App\Models\Trainer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainerSeeder extends Seeder
{
    /**
     * Cantidad de entrenadores a generar.
     */
    private const TRAINER_COUNT = 500;

    /**
     * Tamaño máximo de equipo (clásico de Pokémon).
     */
    private const MAX_TEAM_SIZE = 6;

    public function run(): void
    {
        $this->command->info('Generando entrenadores con sus equipos...');

        // Pre-cargamos los IDs de pokémon en memoria.
        // Esto evita consultas N+1 al armar los equipos.
        $pokemonIds = Pokemon::pluck('id')->toArray();

        if (empty($pokemonIds)) {
            $this->command->error('No hay pokémon en la BD. Corre primero PokemonSeeder.');
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar(self::TRAINER_COUNT);
        $bar->start();

        for ($i = 0; $i < self::TRAINER_COUNT; $i++) {
            DB::transaction(function () use ($pokemonIds) {
                $trainer = Trainer::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'age' => fake()->numberBetween(10, 60),
                    'badges' => fake()->numberBetween(0, 8),
                ]);

                $this->assignTeam($trainer, $pokemonIds);
            });

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('✓ ' . self::TRAINER_COUNT . ' entrenadores creados con sus equipos.');
    }

    /**
     * Asigna un equipo de 1-6 pokémon al entrenador con nicknames y niveles.
     */
    private function assignTeam(Trainer $trainer, array $pokemonIds): void
    {
        $teamSize = fake()->numberBetween(1, self::MAX_TEAM_SIZE);
        $selectedIds = fake()->randomElements($pokemonIds, $teamSize);

        $attach = [];
        foreach ($selectedIds as $pokemonId) {
            $attach[$pokemonId] = [
                // 40% de probabilidad de tener nickname
                'nickname' => fake()->boolean(40) ? fake()->firstName() : null,
                'level' => fake()->numberBetween(5, 100),
            ];
        }

        $trainer->pokemon()->attach($attach);
    }
}