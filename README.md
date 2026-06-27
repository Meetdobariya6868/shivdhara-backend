# Shivdhara — Backend (Laravel 12 API)

REST API for the Shivdhara application, built on **Laravel 12 / PHP 8.4** with a
**Clean Architecture** layout (Repository Pattern + Service Layer + DDD).

> **Phase 1 — Foundation only.** No business logic, no authentication, no users,
> and no database tables. Laravel Sanctum is installed and CORS is configured,
> but nothing is enforced yet.

## Requirements

- PHP 8.4+
- Composer 2.x
- MySQL 8 (unused until migrations are added in a later phase)

## Setup

```bash
composer install
cp .env.example .env       # present after scaffolding
php artisan key:generate   # only if APP_KEY is empty
php artisan serve          # http://localhost:8000
```

The app runs with **no database tables** — session, cache and queue use the
`file`/`file`/`sync` drivers (see `.env`). Switch them to `database` once their
migrations are run in a later phase.

## Verify

```bash
php artisan route:list
curl http://localhost:8000/api/v1/health
# {"status":"ok","service":"Shivdhara","version":"v1"}
```

## Directory structure (`app/`)

```
app/
├── Domain/
│   └── Contracts/                       Framework-agnostic interfaces
│       └── RepositoryInterface.php
├── Application/
│   ├── Services/BaseService.php         Use-case orchestration layer
│   └── DTOs/BaseDTO.php                 Cross-boundary data objects
├── Infrastructure/
│   └── Persistence/Eloquent/
│       └── BaseRepository.php           Concrete Eloquent CRUD base
├── Http/
│   ├── Controllers/Api/V1/              Versioned API controllers
│   ├── Requests/                        Form-request validation
│   └── Resources/                       JSON response transformers
├── Models/                              Eloquent models (framework)
└── Providers/
    ├── AppServiceProvider.php
    └── RepositoryServiceProvider.php    Binds contracts → implementations
```

See [../docs/ARCHITECTURE.md](../docs/ARCHITECTURE.md) for the full rationale.

## Key configuration

| File                 | What it configures                                             |
| -------------------- | -------------------------------------------------------------- |
| `bootstrap/app.php`  | Registers `routes/api.php`; enables Sanctum `statefulApi()`.   |
| `routes/api.php`     | `/api/v1/*` routes (currently a `health` probe).               |
| `config/cors.php`    | CORS — env-driven origins, `supports_credentials: true`.       |
| `config/sanctum.php` | Sanctum SPA configuration (published, defaults).               |
| `.env`               | MySQL, Sanctum stateful domains, CORS origins, file drivers.   |

## Code style

```bash
./vendor/bin/pint        # apply PSR-12 formatting
./vendor/bin/pint --test # check only
```
