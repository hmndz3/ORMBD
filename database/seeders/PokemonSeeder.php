<?php

namespace Database\Seeders;

use App\Models\Ability;
use App\Models\Generation;
use App\Models\Move;
use App\Models\Pokemon;
use App\Models\Region;
use App\Models\Type;
use App\Services\PokeApiService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PokemonSeeder extends Seeder
{
    /**
     * Cachés en memoria para evitar consultas repetidas a la BD.
     * Mapean nombre del recurso → id en nuestra BD.
     */
    private array $typeMap = [];
    private array $abilityMap = [];
    private array $moveMap = [];
    private array $generationMap = [];
    private array $regionMap = [];

    public function run(PokeApiService $api): void
    {
        $this->command->info('Sembrando Pokémon desde PokéAPI (esto tarda ~10-15 min)...');

        $this->buildLookupMaps();

        // Listado completo de pokémon (~1,302 actualmente)
        $pokemonList = $api->getAll('pokemon', 2000);

        $bar = $this->command->getOutput()->createProgressBar(count($pokemonList));
        $bar->start();

        $created = 0;
        $skipped = 0;

        foreach ($pokemonList as $entry) {
            try {
                $detail = $api->get("pokemon/{$entry['name']}");
                $this->createPokemonWithRelations($api, $detail);
                $created++;
            } catch (\Throwable $e) {
                // Algunos pokémon raros tienen data inconsistente; los saltamos
                $skipped++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("✓ {$created} pokémon creados. ({$skipped} saltados)");
    }

    /**
     * Pre-carga los IDs de catálogos en arrays para no consultar la BD
     * miles de veces durante el seeding.
     */
    private function buildLookupMaps(): void
    {
        $this->typeMap = Type::pluck('id', 'name')->toArray();
        $this->abilityMap = Ability::pluck('id', 'name')->toArray();
        $this->moveMap = Move::pluck('id', 'name')->toArray();
        $this->generationMap = Generation::pluck('id', 'number')->toArray();
        $this->regionMap = Region::pluck('id', 'name')->toArray();
    }

    /**
     * Crea un pokémon y sus relaciones (types, abilities, moves).
     * Usa transacción para que si algo falla, no quede a medias.
     */
    private function createPokemonWithRelations(PokeApiService $api, array $detail): void
    {
        DB::transaction(function () use ($api, $detail) {
            // Stats: HP, ataque, defensa, etc.
            $stats = collect($detail['stats'])->keyBy('stat.name');

            // Resolver generación consultando species
            $generationId = null;
            $regionId = null;
            $isLegendary = false;

            if (isset($detail['species']['url'])) {
                $speciesUrl = $detail['species']['url'];
                $speciesPath = parse_url($speciesUrl, PHP_URL_PATH);
                $speciesEndpoint = ltrim(str_replace('/api/v2/', '', $speciesPath), '/');

                try {
                    $species = $api->get($speciesEndpoint);
                    $isLegendary = $species['is_legendary'] ?? false;

                    // Extraer número de generación: "generation-i" → 1
                    if (isset($species['generation']['name'])) {
                        $genNum = $this->romanToInt(str_replace('generation-', '', $species['generation']['name']));
                        $generationId = $this->generationMap[$genNum] ?? null;
                    }
                } catch (\Throwable $e) {
                    // Si falla species, seguimos sin generación
                }
            }

            $pokemon = Pokemon::updateOrCreate(
                ['pokedex_number' => $detail['id']],
                [
                    'name' => $detail['name'],
                    'height' => $detail['height'] ?? null,
                    'weight' => $detail['weight'] ?? null,
                    'base_experience' => $detail['base_experience'] ?? null,
                    'hp' => $stats->get('hp')['base_stat'] ?? 0,
                    'attack' => $stats->get('attack')['base_stat'] ?? 0,
                    'defense' => $stats->get('defense')['base_stat'] ?? 0,
                    'special_attack' => $stats->get('special-attack')['base_stat'] ?? 0,
                    'special_defense' => $stats->get('special-defense')['base_stat'] ?? 0,
                    'speed' => $stats->get('speed')['base_stat'] ?? 0,
                    'is_legendary' => $isLegendary,
                    'sprite_url' => $detail['sprites']['front_default'] ?? null,
                    'generation_id' => $generationId,
                    'region_id' => $regionId,
                ]
            );

            $this->syncTypes($pokemon, $detail);
            $this->syncAbilities($pokemon, $detail);
            $this->syncMoves($pokemon, $detail);
        });
    }

    private function syncTypes(Pokemon $pokemon, array $detail): void
    {
        $sync = [];
        foreach ($detail['types'] ?? [] as $t) {
            $name = $t['type']['name'];
            if (isset($this->typeMap[$name])) {
                $sync[$this->typeMap[$name]] = ['slot' => $t['slot']];
            }
        }
        if (!empty($sync)) {
            $pokemon->types()->sync($sync);
        }
    }

    private function syncAbilities(Pokemon $pokemon, array $detail): void
    {
        $sync = [];
        foreach ($detail['abilities'] ?? [] as $a) {
            $name = $a['ability']['name'];
            if (isset($this->abilityMap[$name])) {
                $sync[$this->abilityMap[$name]] = ['is_hidden' => $a['is_hidden'] ?? false];
            }
        }
        if (!empty($sync)) {
            $pokemon->abilities()->sync($sync);
        }
    }

    private function syncMoves(Pokemon $pokemon, array $detail): void
    {
        $sync = [];
        // Tomamos solo los primeros 30 movimientos por pokémon para mantener el seed manejable
        $moves = array_slice($detail['moves'] ?? [], 0, 30);

        foreach ($moves as $m) {
            $name = $m['move']['name'];
            if (!isset($this->moveMap[$name])) {
                continue;
            }

            // Buscar el nivel de aprendizaje de la versión más reciente
            $learnLevel = null;
            foreach ($m['version_group_details'] ?? [] as $vgd) {
                if (($vgd['move_learn_method']['name'] ?? '') === 'level-up') {
                    $learnLevel = $vgd['level_learned_at'] ?? null;
                    break;
                }
            }

            $sync[$this->moveMap[$name]] = ['learn_level' => $learnLevel];
        }

        if (!empty($sync)) {
            $pokemon->moves()->sync($sync);
        }
    }

    /**
     * Convierte números romanos a int. PokéAPI usa "generation-iv", "generation-v", etc.
     */
    private function romanToInt(string $roman): int
    {
        $map = ['i' => 1, 'v' => 5, 'x' => 10];
        $result = 0;
        $prev = 0;
        $chars = array_reverse(str_split(strtolower($roman)));

        foreach ($chars as $char) {
            $value = $map[$char] ?? 0;
            $result += ($value < $prev) ? -$value : $value;
            $prev = $value;
        }
        return $result;
    }
}