<?php

namespace Database\Seeders;

use App\Models\Type;
use App\Services\PokeApiService;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    public function run(PokeApiService $api): void
    {
        $this->command->info('Sembrando tipos desde PokéAPI...');

        // Colores hex aproximados por tipo (mejora visual, opcional)
        $colors = [
            'normal' => '#A8A77A', 'fire' => '#EE8130', 'water' => '#6390F0',
            'electric' => '#F7D02C', 'grass' => '#7AC74C', 'ice' => '#96D9D6',
            'fighting' => '#C22E28', 'poison' => '#A33EA1', 'ground' => '#E2BF65',
            'flying' => '#A98FF3', 'psychic' => '#F95587', 'bug' => '#A6B91A',
            'rock' => '#B6A136', 'ghost' => '#735797', 'dragon' => '#6F35FC',
            'dark' => '#705746', 'steel' => '#B7B7CE', 'fairy' => '#D685AD',
        ];

        $types = $api->getAll('type');
        $count = 0;

        foreach ($types as $type) {
            // PokéAPI tiene "unknown" y "shadow" que no usamos
            if (in_array($type['name'], ['unknown', 'shadow', 'stellar'])) {
                continue;
            }

            Type::updateOrCreate(
                ['name' => $type['name']],
                ['color' => $colors[$type['name']] ?? null]
            );
            $count++;
        }

        $this->command->info("✓ {$count} tipos creados.");
    }
}