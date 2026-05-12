# Pokédex ORM — Laboratorio Laravel + Eloquent

Modelado de un dominio Pokémon usando Laravel 13 y Eloquent ORM. Incluye 11 tablas con relaciones (`hasMany`, `belongsTo`, `belongsToMany`), seeders que pueblan la base con datos reales de [PokéAPI](https://pokeapi.co/), y consultas demo que ilustran el uso de eager loading para evitar el problema N+1.

## Dominio modelado

- **Pokémon** con sus stats, generación y región de origen
- **Tipos** (fuego, agua, dragón…) — relación muchos a muchos
- **Habilidades** que pueden ser normales o ocultas — muchos a muchos
- **Movimientos** que cada pokémon puede aprender — muchos a muchos con `learn_level`
- **Entrenadores** con sus equipos de hasta 6 pokémon — muchos a muchos con `nickname` y `level`

## Requisitos

- Docker Desktop
- WSL2 (si estás en Windows)
- Git


## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/hmndz3/ORMBD.git
cd ORMBD

# 2. Instalar dependencias de PHP usando un contenedor temporal
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php85-composer:latest \
    composer install --ignore-platform-reqs

# 3. Copiar el archivo de entorno y generar la APP_KEY
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
```

## Correr migraciones y seeders

```bash
# Levantar los contenedores 
./vendor/bin/sail up -d

# Correr las 11 migraciones
./vendor/bin/sail artisan migrate

# Poblar la base de datos
# IMPORTANTE: este paso descarga datos de PokéAPI y tarda ~15-25 minutos
# la primera vez. Las respuestas se cachean en storage/app/pokeapi-cache/
# para futuras ejecuciones.
./vendor/bin/sail php -d memory_limit=1G artisan db:seed
```

> Si el seed se interrumpe, puedes volver a correrlo. La caché en disco y `updateOrCreate` permiten retomar sin duplicar datos.

## Verificar los datos

```bash
./vendor/bin/sail artisan tinker
```

Dentro de Tinker:

```php
App\Models\Pokemon::count();   // ~1300
App\Models\Trainer::count();   // 500
App\Models\Move::count();      // ~900
```

## Ejecutar las consultas demo

Las 5 consultas Eloquent del laboratorio se ejecutan con un solo comando:

```bash
./vendor/bin/sail artisan pokemon:demo
```

Imprime en consola:

1. Pokémon legendarios ordenados por ataque
2. Top 10 pokémon tipo dragón por velocidad
3. Pokémon con mayor ataque por generación
4. Top 5 entrenadores con más medallas y sus equipos *(usa eager loading)*
5. 10 pokémon más populares entre entrenadores


## Estructura del proyecto

```
app/
├── Models/              # 11 modelos Eloquent (Pokemon, Type, Trainer, etc.)
├── Services/
│   └── PokeApiService   # Cliente HTTP con caché para PokéAPI
└── Console/Commands/
    └── DemoQueries      # Comando con las 5 consultas demo

database/
├── migrations/          # 11 migraciones (catálogo + pokemon + pivotes)
└── seeders/             # Un seeder por tabla principal + DatabaseSeeder
```

## Comandos útiles

```bash
# Reiniciar todo desde cero (¡borra datos!)
./vendor/bin/sail artisan migrate:fresh --seed

# Detener los contenedores
./vendor/bin/sail down

# Ver logs
./vendor/bin/sail logs
```


