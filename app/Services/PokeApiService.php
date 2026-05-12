<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Cliente para PokéAPI con caché en disco.
 * Evita golpear la API más de una vez por endpoint.
 */
class PokeApiService
{
    private const BASE_URL = 'https://pokeapi.co/api/v2';
    private const CACHE_DIR = 'pokeapi-cache';

    /**
     * Hace GET a un endpoint y guarda el JSON en caché de disco.
     * Si ya está cacheado, lo lee del disco.
     */
    public function get(string $endpoint): array
    {
        $cacheKey = $this->cacheKey($endpoint);

        if (Storage::disk('local')->exists($cacheKey)) {
            return json_decode(Storage::disk('local')->get($cacheKey), true);
        }

        $url = self::BASE_URL . '/' . ltrim($endpoint, '/');
        $response = Http::timeout(30)->retry(3, 500)->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("PokeAPI falló para {$endpoint}: HTTP {$response->status()}");
        }

        $data = $response->json();
        Storage::disk('local')->put($cacheKey, json_encode($data));

        return $data;
    }

    /**
     * Itera todos los recursos de un endpoint paginado.
     * Devuelve un array plano con todos los items "name" y "url".
     */
    public function getAll(string $endpoint, int $limit = 100000): array
    {
        $data = $this->get("{$endpoint}?limit={$limit}");
        return $data['results'] ?? [];
    }

    private function cacheKey(string $endpoint): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_\-\/]/', '_', $endpoint);
        return self::CACHE_DIR . '/' . trim($safe, '/') . '.json';
    }
}