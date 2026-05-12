<?php

namespace Database\Seeders;

use App\Models\Move;
use App\Services\PokeApiService;
use Illuminate\Database\Seeder;

class MoveSeeder extends Seeder
{
    public function run(PokeApiService $api): void
    {
        $this->command->info('Sembrando movimientos desde PokéAPI (esto tarda ~3-5 min)...');

        $moves = $api->getAll('move', 1000);
        $bar = $this->command->getOutput()->createProgressBar(count($moves));
        $bar->start();

        foreach ($moves as $mv) {
            $detail = $api->get("move/{$mv['name']}");

            Move::updateOrCreate(
                ['name' => $mv['name']],
                [
                    'power' => $detail['power'] ?? null,
                    'accuracy' => $detail['accuracy'] ?? null,
                    'pp' => $detail['pp'] ?? null,
                    'damage_class' => $detail['damage_class']['name'] ?? 'physical',
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('✓ Movimientos creados.');
    }
}