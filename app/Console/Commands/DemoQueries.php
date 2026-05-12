<?php

namespace App\Console\Commands;

use App\Models\Generation;
use App\Models\Pokemon;
use App\Models\Trainer;
use App\Models\Type;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Comando que demuestra las consultas Eloquent requeridas por el laboratorio:
 * relaciones, filtros, ordenamiento y eager loading.
 *
 * Uso: sail artisan pokemon:demo
 */
class DemoQueries extends Command
{
    protected $signature = 'pokemon:demo';

    protected $description = 'Ejecuta las 5 consultas Eloquent demo del laboratorio.';

    public function handle(): int
    {
        $this->query1_pokemonLegendariosOrdenados();
        $this->query2_pokemonPorTipo();
        $this->query3_topAtacantesPorGeneracion();
        $this->query4_trainersConSusEquipos();
        $this->query5_pokemonMasUsadosPorTrainers();

        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONSULTA 1: Filtro + ordenamiento
    // ─────────────────────────────────────────────────────────────────────
    private function query1_pokemonLegendariosOrdenados(): void
    {
        $this->info("\n══ CONSULTA 1: Pokémon legendarios ordenados por ataque ══");

        $legendarios = Pokemon::where('is_legendary', true)
            ->orderByDesc('attack')
            ->take(10)
            ->get();

        foreach ($legendarios as $p) {
            $this->line(sprintf(
                "  #%-4d %-20s ATK: %d",
                $p->pokedex_number,
                $p->name,
                $p->attack
            ));
        }

        $this->comment("Total legendarios: " . Pokemon::where('is_legendary', true)->count());
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONSULTA 2: Relación belongsToMany con filtro
    // ─────────────────────────────────────────────────────────────────────
    private function query2_pokemonPorTipo(): void
    {
        $this->info("\n══ CONSULTA 2: Top 10 pokémon tipo 'dragon' por velocidad ══");

        $dragones = Type::where('name', 'dragon')
            ->first()
            ?->pokemon()
            ->orderByDesc('speed')
            ->take(10)
            ->get();

        if (!$dragones || $dragones->isEmpty()) {
            $this->warn("  No hay pokémon tipo dragón en la BD.");
            return;
        }

        foreach ($dragones as $p) {
            $this->line(sprintf("  %-20s SPD: %d", $p->name, $p->speed));
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONSULTA 3: Agrupación + relación belongsTo
    // ─────────────────────────────────────────────────────────────────────
    private function query3_topAtacantesPorGeneracion(): void
    {
        $this->info("\n══ CONSULTA 3: Pokémon con mayor ataque por generación ══");

        $generations = Generation::orderBy('number')->get();

        foreach ($generations as $gen) {
            $top = $gen->pokemon()
                ->orderByDesc('attack')
                ->first();

            if ($top) {
                $this->line(sprintf(
                    "  %-15s → %-20s (ATK: %d)",
                    $gen->name,
                    $top->name,
                    $top->attack
                ));
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONSULTA 4: EAGER LOADING con justificación (Requisito 5 de la rúbrica)
    // ─────────────────────────────────────────────────────────────────────
    private function query4_trainersConSusEquipos(): void
    {
        $this->info("\n══ CONSULTA 4: 5 entrenadores con más medallas y sus equipos ══");

        /*
         * EAGER LOADING — JUSTIFICACIÓN:
         * Sin `with('pokemon.types')` esta consulta sería un caso clásico de N+1:
         *   - 1 query para traer los 5 trainers
         *   - 5 queries adicionales para traer los pokémon de cada trainer
         *   - Por cada pokémon (digamos 6 promedio), 1 query para sus tipos
         *     → 5 trainers × 6 pokémon = 30 queries solo de tipos
         *   Total sin eager: 1 + 5 + 30 = 36 queries.
         *
         * Con eager loading bajamos a SOLO 3 queries:
         *   - 1 para trainers
         *   - 1 para todos los pokémon de esos trainers (WHERE IN)
         *   - 1 para todos los tipos de esos pokémon (WHERE IN)
         *   Mejora del 92% en cantidad de queries.
         */
        $trainers = Trainer::with(['pokemon.types'])
            ->orderByDesc('badges')
            ->take(5)
            ->get();

        foreach ($trainers as $t) {
            $this->line("\n  🏅 {$t->name} — {$t->badges} medallas");
            foreach ($t->pokemon as $p) {
                $nick = $p->pivot->nickname ?: $p->name;
                $types = $p->types->pluck('name')->implode('/');
                $this->line(sprintf(
                    "     - %-15s [%s] nivel %d",
                    $nick,
                    $types ?: 'sin tipo',
                    $p->pivot->level
                ));
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONSULTA 5: Agregación + ordenamiento sobre relación
    // ─────────────────────────────────────────────────────────────────────
    private function query5_pokemonMasUsadosPorTrainers(): void
    {
        $this->info("\n══ CONSULTA 5: 10 pokémon más populares entre entrenadores ══");

        $populares = Pokemon::withCount('trainers')
            ->having('trainers_count', '>', 0)
            ->orderByDesc('trainers_count')
            ->take(10)
            ->get();

        foreach ($populares as $p) {
            $this->line(sprintf(
                "  %-20s usado por %d entrenadores",
                $p->name,
                $p->trainers_count
            ));
        }
    }
}