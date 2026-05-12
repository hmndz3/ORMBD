<?php

namespace Database\Seeders;

use App\Models\Generation;
use App\Services\PokeApiService;
use Illuminate\Database\Seeder;

class GenerationSeeder extends Seeder
{
    public function run(PokeApiService $api): void
    {
        $this->command->info('Sembrando generaciones desde PokéAPI...');

        // Año de lanzamiento del primer juego de cada generación
        $years = [
            1 => 1996, 2 => 1999, 3 => 2002, 4 => 2006, 5 => 2010,
            6 => 2013, 7 => 2016, 8 => 2019, 9 => 2022,
        ];

        $generations = $api->getAll('generation');
        $count = 0;

        foreach ($generations as $gen) {
            $detail = $api->get("generation/{$gen['name']}");
            $number = $detail['id'];

            Generation::updateOrCreate(
                ['number' => $number],
                [
                    'name' => 'Generación ' . $number,
                    'release_year' => $years[$number] ?? null,
                ]
            );
            $count++;
        }

        $this->command->info("✓ {$count} generaciones creadas.");
    }
}