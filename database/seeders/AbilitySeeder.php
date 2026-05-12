<?php

namespace Database\Seeders;

use App\Models\Ability;
use App\Services\PokeApiService;
use Illuminate\Database\Seeder;

class AbilitySeeder extends Seeder
{
    public function run(PokeApiService $api): void
    {
        $this->command->info('Sembrando habilidades desde PokéAPI (esto tarda ~1-2 min)...');

        $abilities = $api->getAll('ability', 500);
        $bar = $this->command->getOutput()->createProgressBar(count($abilities));
        $bar->start();

        foreach ($abilities as $ab) {
            $detail = $api->get("ability/{$ab['name']}");

            // Buscar descripción en inglés
            $description = null;
            foreach ($detail['effect_entries'] ?? [] as $entry) {
                if ($entry['language']['name'] === 'en') {
                    $description = $entry['short_effect'] ?? $entry['effect'] ?? null;
                    break;
                }
            }

            Ability::updateOrCreate(
                ['name' => $ab['name']],
                [
                    'description' => $description,
                    'is_hidden' => false, // se setea en el pivote
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('✓ Habilidades creadas.');
    }
}