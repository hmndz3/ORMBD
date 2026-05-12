<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Services\PokeApiService;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(PokeApiService $api): void
    {
        $this->command->info('Sembrando regiones desde PokéAPI...');

        $mainCities = [
            'kanto' => 'Pueblo Paleta', 'johto' => 'Pueblo Primavera',
            'hoenn' => 'Villa Raíz', 'sinnoh' => 'Pueblo Hojaverde',
            'unova' => 'Pueblo Arcilla', 'kalos' => 'Pueblo Boceto',
            'alola' => 'Pueblo Iki', 'galar' => 'Pueblo Norte',
            'hisui' => 'Aldea Jubileo', 'paldea' => 'Cabo Poco',
        ];

        $regions = $api->getAll('region');
        $count = 0;

        foreach ($regions as $region) {
            Region::updateOrCreate(
                ['name' => ucfirst($region['name'])],
                ['main_city' => $mainCities[$region['name']] ?? null]
            );
            $count++;
        }

        $this->command->info("✓ {$count} regiones creadas.");
    }
}