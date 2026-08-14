# Moto

A demo team-management platform for the Formula 1 industry, built to cross-skill a Symfony developer into Laravel. It covers the core Laravel stack end-to-end: Eloquent models and migrations, a JWT-protected REST API, and a Livewire/Volt frontend, all backed by MySQL in Docker.

## Stack

- **Laravel 13** (PHP 8.3+)
- **MySQL 8.4** via Docker Compose (data storage only — the app itself runs on the host)
- **Livewire 3 + Volt**, scaffolded via Laravel Breeze's Livewire stack (auth, layout, navigation)
- **[php-open-source-saver/jwt-auth](https://github.com/PHP-Open-Source-Saver/jwt-auth)** for stateless JWT REST API authentication (the actively-maintained fork of `tymon/jwt-auth`)
- **Pest-compatible PHPUnit** feature tests running against an in-memory SQLite database

## Domain model

- **Team** — an F1 constructor (`name`, `full_name`, `base`, `principal`, `founded_year`, `color`)
- **User** — staff, engineers, drivers, or admins; optionally belongs to a `Team`, and carries a `role` (`App\Enums\UserRole`)
- **Vehicle** — a car entered by a `Team` for a given season, optionally assigned to a `User` as `driver` (`App\Enums\VehicleStatus`: `active`, `testing`, `retired`)
- **Part** — a catalog entry for a physical component (`name`, `part_number`, `category` (`App\Enums\PartCategory`), `manufacturer`, `description`)
- **VehiclePart** — the association between a `Vehicle` and a `Part` it currently requires, carrying its own lifecycle `status` (`App\Enums\VehiclePartStatus`: `required` → `in-transit` → `delivered` → `fitted`)

Relationships: `Team` `hasMany` `User` and `hasMany` `Vehicle`; `Vehicle` `belongsTo` `Team` and optionally `belongsTo` `User` (as `driver`); `Vehicle` `hasMany` `VehiclePart` (also exposed as a `belongsToMany` `Part` through the `VehiclePart` pivot model, via `Vehicle::parts()`).

### Business rule: parts gate testing → active

A vehicle can only be required to fit a part while its status is `testing` (`App\Http\Requests\StoreVehiclePartRequest` rejects the request otherwise). A `testing` vehicle can only move to `active` once **every** `VehiclePart` it has is `fitted` — `Vehicle::canActivate()` / `Vehicle::hasOutstandingParts()` (`app/Models/Vehicle.php`) are the single source of truth for this, checked from three places: the API (`UpdateVehicleRequest::withValidator()`), the Livewire vehicle page (`vehicles.show`'s `updateStatus()`), and implicitly by the seeder's demo data (one seeded vehicle is deliberately left blocked, one deliberately left ready — see `DatabaseSeeder::seedPartRequirements()`).

## Getting started

```bash
# 1. Start MySQL
docker compose up -d

# 2. Install dependencies
composer install
npm install

# 3. Configure environment (.env is already set up for the Docker MySQL instance)
cp .env.example .env   # if starting fresh
php artisan key:generate

# 4. Migrate and seed demo data: 4 real constructors (Ferrari, McLaren, Williams, Mercedes)
#    each with a real driver line-up, ~20 staff/drivers, 8 vehicles, a 28-part catalog,
#    and a few vehicles in testing with parts at different pipeline stages
php artisan migrate --seed

# 5. Build frontend assets
npm run build   # or `npm run dev` for hot-reloading during development

# 6. Serve the app
php artisan serve
```

The seeder creates a login at **test@example.com / password** (admin, no team assigned).

## REST API

All API routes live under `/api` and (aside from login/refresh) require a JWT bearer token issued by `POST /api/tokens`. Tokens are stateless — nothing is persisted server-side, they're just signed and verified against `JWT_SECRET`.

```bash
# Exchange credentials for a token
curl -X POST http://localhost:8000/api/tokens \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
# => { "token": "eyJ0...", "token_type": "bearer", "expires_in": 3600 }

# Use the token
curl http://localhost:8000/api/teams \
  -H "Accept: application/json" -H "Authorization: Bearer <token>"

# Refresh before/shortly after expiry (rotates the token; the old one is blacklisted)
curl -X POST http://localhost:8000/api/tokens/refresh \
  -H "Accept: application/json" -H "Authorization: Bearer <token>"

# Log out (invalidates/blacklists the current token)
curl -X DELETE http://localhost:8000/api/tokens/current \
  -H "Accept: application/json" -H "Authorization: Bearer <token>"

# Require a part on a vehicle currently in testing
curl -X POST http://localhost:8000/api/vehicles/1/parts \
  -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer <token>" \
  -d '{"part_id": 4}'

# Advance that requirement through its lifecycle
curl -X PATCH http://localhost:8000/api/vehicles/1/parts/7 \
  -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer <token>" \
  -d '{"status": "fitted"}'
```

| Resource     | Endpoints                                                                 |
|--------------|------------------------------------------------------------------------------|
| Auth         | `POST /api/tokens` (login), `POST /api/tokens/refresh`, `DELETE /api/tokens/current` (logout), `GET /api/user` (me) |
| Teams        | `GET/POST /api/teams`, `GET/PUT/PATCH/DELETE /api/teams/{team}`           |
| Vehicles     | `GET/POST /api/vehicles`, `GET/PUT/PATCH/DELETE /api/vehicles/{vehicle}`  |
| Users        | `GET/POST /api/users`, `GET/PUT/PATCH/DELETE /api/users/{user}`           |
| Parts        | `GET/POST /api/parts`, `GET/PUT/PATCH/DELETE /api/parts/{part}` (catalog CRUD) |
| Vehicle parts| `POST /api/vehicles/{vehicle}/parts` (require, testing-only), `PATCH /api/vehicles/{vehicle}/parts/{vehiclePart}` (advance status), `DELETE /api/vehicles/{vehicle}/parts/{vehiclePart}` (remove) |

Requests are validated by `App\Http\Requests` FormRequest classes and responses are shaped by `App\Http\Resources` JSON resources, which eager-load and nest related `Team`/`User`/`Vehicle`/`Part` data. The web app's login/session auth (Breeze) is entirely separate from this token auth — they share the same `users` table but nothing else.

## Frontend

The Livewire/Volt pages (`resources/views/livewire/teams`, `.../vehicles`, `.../parts`) provide browser-based CRUD over the same Eloquent models the API uses — team listing/creation, team detail (staff + vehicle roster), vehicle listing with status filtering, a parts catalog, and a vehicle detail page for updating race status and managing part requirements. The vehicle page enforces the same testing → active rule as the API: the "active" option disappears from the status dropdown (and resubmitting it server-side still gets rejected) until every required part is `fitted`. Auth (login/register/password reset/profile) comes from Breeze's Livewire stack unmodified.

## Testing

```bash
php artisan test
```

Feature tests cover both the API (`tests/Feature/Api`) and the Livewire pages (`tests/Feature/*PageTest.php`), including guest-access rejection, validation, and Livewire component interactions via `Livewire\Volt\Volt::test()`. The testing → active business rule is covered at three levels: a model-level unit test (`tests/Unit/VehiclePartsBusinessRuleTest.php`), an API test (`tests/Feature/Api/VehiclePartApiTest.php`), and a Livewire test (in `VehicleManagementPageTest.php`) — deliberately redundant, since the rule is enforced independently in both the API and UI layers and each needs its own proof it's wired up.

## Laravel vs Symfony: how this codebase would differ

A guide for a Symfony developer reading this codebase, mapping each piece to its closest Symfony equivalent and calling out where the philosophies genuinely diverge.

### Routing

Laravel routes are plain PHP closures/arrays in `routes/web.php` and `routes/api.php`, loaded by `bootstrap/app.php`. `Route::apiResource('teams', TeamController::class)` expands to the seven RESTful routes in one line. Symfony would express the same thing with `#[Route]` attributes directly on controller methods (or YAML/PHP route configs), and there's no built-in "resource controller" convention — you'd hand-write each route or reach for API Platform.

### Models and the ORM

Eloquent (`app/Models/Team.php`) is **Active Record**: the model is both the data structure and the query builder (`Team::where(...)->paginate()`), and relationships are methods that return query builders (`hasMany`, `belongsTo`). Doctrine is a **Data Mapper**: entities are plain PHP objects with no knowledge of persistence, and all querying goes through an `EntityManager`/repository. This is the single biggest mental shift — in Laravel, `$team->vehicles` is live and query-driven; in Symfony, you'd typically inject a `VehicleRepository` and call a method on it.

### Migrations & schema

Both frameworks version schema as PHP-defined migrations, but Laravel's `Schema::create('teams', fn (Blueprint $table) => ...)` is written by hand and run with `php artisan migrate`. Doctrine Migrations are usually *generated* from entity annotations/attributes via `doctrine:migrations:diff`, so the entity is the source of truth rather than the migration file.

### Validation

`App\Http\Requests\StoreTeamRequest` centralizes both authorization (`authorize()`) and validation rules (`rules()`) for one request, resolved automatically by type-hinting it in the controller method. Symfony's Validator works differently: constraints are usually attributes on a DTO or entity (`#[Assert\NotBlank]`), and you validate an object explicitly with `$validator->validate($dto)` — validation isn't tied to "the current HTTP request" the way a FormRequest is.

For business rules that aren't simple per-field constraints — e.g. `UpdateVehicleRequest`'s "can't set status to `active` while parts are outstanding" — Laravel uses a `withValidator()` hook that adds an `after()` closure to inspect the whole request (including the route-bound model) once basic rules pass. The nearest Symfony equivalent is a custom `Constraint`/`ConstraintValidator` pair (or a simpler `Callback` constraint), but those are typically attached to the entity/DTO being validated rather than bolted onto the request class itself.

### API responses

`App\Http\Resources\TeamResource` is a small, explicit `toArray()` transformer with `whenLoaded()` guards to avoid N+1s. It's roughly Laravel's answer to Symfony's Serializer normalizers/groups, but far more manual — there's no attribute-driven serialization config; you write the array shape by hand.

### Authentication: JWT vs LexikJWTAuthenticationBundle

The API is authenticated with **`php-open-source-saver/jwt-auth`**, the closest Laravel equivalent to `lexik/jwt-authentication-bundle`. The mechanics map closely once you see past the config style:

| Concept | This app (jwt-auth) | Symfony (LexikJWTAuthenticationBundle) |
|---|---|---|
| Token issuance | `App\Http\Controllers\Api\AuthController::store()` calls `Auth::guard('api')->attempt($credentials)`, which verifies the password and hands back a signed token | Lexik hooks into the Security firewall's `json_login` authenticator — you rarely write the login controller yourself, Lexik's `AuthenticationSuccessHandler` builds the response |
| Guard wiring | `config/auth.php` gets a new `api` guard with `'driver' => 'jwt'`; routes opt in with `auth:api` middleware | `security.yaml` defines a `firewall` with `stateless: true` and `jwt: ~` — config-first rather than middleware-first |
| Claims / subject | `User implements JWTSubject`, with `getJWTIdentifier()` (the `sub` claim) and `getJWTCustomClaims()` (this app adds `role`) | Lexik's `payload_enrichment` / `JWTCreatedEvent` — you listen for an event and mutate the payload, rather than implementing an interface on the entity |
| Signing | HS256 by default (`JWT_SECRET`, HMAC — one shared secret); can be switched to RS256 in `config/jwt.php` | RS256 by default (public/private keypair generated via `lexik:jwt:generate-keypair`) — Lexik nudges you toward asymmetric signing from the start |
| Refresh | Hand-rolled here: `POST /api/tokens/refresh` calls `Auth::guard('api')->refresh()`, which blacklists the old token's `jti` and mints a new one, using whichever Laravel cache store is configured (`CACHE_STORE=database` here, so it's a deny-list entry in the generic `cache` table — not a purpose-built tokens table) | Not part of Lexik itself — refresh is a separate bundle, `gesdinet/jwt-refresh-token-bundle`, which *does* persist refresh tokens to a dedicated database table |
| Logout | `Auth::guard('api')->logout()` blacklists the current token the same way | Stateless by design too — "logout" for a pure JWT API is really just the client discarding the token, unless you're using the refresh-token bundle's revocation |

The practical takeaway: Sanctum (what this API used previously) issues *opaque* tokens that are rows in a `personal_access_tokens` table — checking one means a DB lookup every request. A JWT is *self-contained and signed* — checking one normally means verifying a signature with no DB round-trip at all; the only reason this app still touches storage per-request is the blacklist deny-list check (needed to make logout/refresh actually revoke a token, since a bare JWT can't otherwise be un-issued before it expires). That's the same trade-off Lexik faces: pure JWT is stateless and fast, but real revocation always needs *some* server-side state again, which is exactly the gap `gesdinet/jwt-refresh-token-bundle` and this app's blacklist both exist to fill.

### Frontend: Livewire/Volt vs Symfony UX

This is the biggest architectural difference from a typical Symfony setup. Livewire components (here, Volt single-file components in `resources/views/livewire/`) keep component state on the server and re-render over AJAX on every interaction — there's no client-side state management or build step for the component logic itself, just Blade templates with `wire:model`/`wire:click` bindings. Symfony's closest equivalent is **Symfony UX** (Turbo + Stimulus + `LiveComponent`), which follows a similar "server-rendered, sprinkle in interactivity" philosophy — `ux:live-component` is conceptually very close to a Volt component. Without UX, a Symfony app more commonly ships a separate API + full SPA (React/Vue), which this project deliberately avoids in favor of the API existing primarily for external consumers.

### Console & tooling

`php artisan` (route:list, make:model, tinker, migrate) maps to `bin/console` (debug:router, make:entity, doctrine:migrations:migrate). Laravel's `make:*` generators (used throughout this scaffold — `make:model -f`, `make:controller --api`, `make:resource`, `make:request`) are more aggressively "batteries included" than Symfony Maker Bundle, generating fuller boilerplate per command.

### Testing

Feature tests extend `Tests\TestCase` (itself extending `Illuminate\Foundation\Testing\TestCase`), use the `RefreshDatabase` trait to reset an in-memory SQLite database per test, and lean on model factories (`Team::factory()->create()`) for fixtures. Symfony's nearest equivalents are `KernelTestCase`/`WebTestCase` plus a fixture library like Foundry — the factory pattern (`UserFactory::role(...)`) will feel familiar if you've used Foundry's model factories.

### Dependency injection

Both frameworks have a service container, but Laravel leans on it implicitly — type-hint a class in a controller method or FormRequest and it's resolved automatically, no configuration required for the common case. Symfony's container is more explicit: services are typically autowired too, but you'll more often see them configured or tagged in `services.yaml`, and constructor injection into controllers is the norm rather than method-injection.
