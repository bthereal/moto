# Moto

A demo management platform for the Formula 1 industry, built to in Laravel 13. It covers the core Laravel stack end-to-end: Eloquent models and migrations, a JWT-protected REST API, and a Livewire/Volt frontend, all backed by MySQL in Docker.

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
- **VehiclePart** — the association between a `Vehicle` and a `Part` it currently requires, carrying its own lifecycle `status` (`App\Enums\VehiclePartStatus`: `required` → `ordered` → `in-transit` → `delivered` → `fitted`)
- **Supplier** — a parts distributor (`name`, `contact_email`)
- **SupplierPart** — a supplier's stock listing for a part: `quantity` on hand, `price`, `location`, and `delivery_cost`. A supplier can list any part in the catalog; the same part can be listed by several suppliers at different prices
- **VehiclePartOrder** — created once, when a `required` `VehiclePart` is fulfilled from a specific `SupplierPart`; snapshots `price`/`delivery_cost` at order time so the order's history doesn't drift if the supplier later changes their price

Relationships: `Team` `hasMany` `User` and `hasMany` `Vehicle`; `Vehicle` `belongsTo` `Team` and optionally `belongsTo` `User` (as `driver`); `Vehicle` `hasMany` `VehiclePart` (also exposed as a `belongsToMany` `Part` through the `VehiclePart` pivot model, via `Vehicle::parts()`); `Supplier` `hasMany` `SupplierPart`; `VehiclePart` `hasOne` `VehiclePartOrder`, which `belongsTo` a `SupplierPart`.

### Business rule: parts gate testing → active

A vehicle can only be required to fit a part while its status is `testing` (`App\Http\Requests\StoreVehiclePartRequest` rejects the request otherwise). A `testing` vehicle can only move to `active` once **every** `VehiclePart` it has is `fitted` — `Vehicle::canActivate()` / `Vehicle::hasOutstandingParts()` (`app/Models/Vehicle.php`) are the single source of truth for this, checked from three places: the API (`UpdateVehicleRequest::withValidator()`), the Livewire vehicle page (`vehicles.show`'s `updateStatus()`), and implicitly by the seeder's demo data (one seeded vehicle is deliberately left blocked, one deliberately left ready — see `DatabaseSeeder::seedPartRequirements()`).

### Business rule: ordering a part

A `required` part can't jump straight to `in-transit` — it has to be **ordered from a specific supplier first**. `VehiclePart::placeOrder(SupplierPart $supplierPart)` (`app/Models/VehiclePart.php`) is the single place this happens: inside a DB transaction it snapshots the listing's `price`/`delivery_cost` onto a new `VehiclePartOrder`, decrements the supplier's stock by one, and flips the requirement to `ordered`. It refuses to run unless the requirement is still `required`, the listing is for the *same* part, and the listing actually has stock — each guard throws a `LogicException`, so the method can never be called into producing an inconsistent state, regardless of caller. The generic `PATCH /api/vehicles/{vehicle}/parts/{vehiclePart}` endpoint explicitly rejects `required` and `ordered` as target statuses (`UpdateVehiclePartRequest`) — those two transitions only happen through the dedicated `store`/`order` actions, which is also why the Livewire vehicle page swaps the "Advance" button for an "Order" button (opening a supplier-selection modal) specifically on `required` rows.

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
#    5 suppliers stocking every part, and a few vehicles in testing with parts at
#    different pipeline stages (including one already-placed order)
php artisan migrate --seed

# 5. Build frontend assets
npm run build   # or `npm run dev` for hot-reloading during development

# 6. Serve the app
php artisan serve
```

The seeder creates a login at **test@example.com / password** (admin, no team assigned). It needs the `admin` role to exercise the app's write paths — see [Authorization](#authorization). These are demo credentials for local use; don't seed them anywhere reachable, and set `APP_DEBUG=false` outside local development.

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

# See which suppliers currently have that part in stock
curl http://localhost:8000/api/vehicles/1/parts/7/suppliers \
  -H "Accept: application/json" -H "Authorization: Bearer <token>"

# Order it from one of them (snapshots price/delivery cost, decrements stock, moves to "ordered")
curl -X POST http://localhost:8000/api/vehicles/1/parts/7/order \
  -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer <token>" \
  -d '{"supplier_part_id": 12}'

# Advance the rest of the lifecycle by hand
curl -X PATCH http://localhost:8000/api/vehicles/1/parts/7 \
  -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer <token>" \
  -d '{"status": "fitted"}'
```

In the table below, **read** endpoints are available to any authenticated user and **write** endpoints are admin-only — see [Authorization](#authorization).

| Resource      | Endpoints                                                                 | Access |
|---------------|------------------------------------------------------------------------------|--------|
| Auth          | `POST /api/tokens` (login), `POST /api/tokens/refresh`, `DELETE /api/tokens/current` (logout), `GET /api/user` (me) | public (login/refresh), authenticated (logout/me) |
| Teams         | `GET /api/teams`, `GET /api/teams/{team}`                                 | authenticated |
|               | `POST /api/teams`, `PUT/PATCH/DELETE /api/teams/{team}`                   | **admin** |
| Vehicles      | `GET /api/vehicles`, `GET /api/vehicles/{vehicle}`                        | authenticated |
|               | `POST /api/vehicles`, `PUT/PATCH/DELETE /api/vehicles/{vehicle}`          | **admin** |
| Users         | `GET /api/users`, `GET /api/users/{user}`                                 | authenticated |
|               | `POST /api/users`, `PUT/PATCH/DELETE /api/users/{user}`                   | **admin** |
| Parts         | `GET /api/parts`, `GET /api/parts/{part}`                                 | authenticated |
|               | `POST /api/parts`, `PUT/PATCH/DELETE /api/parts/{part}` (catalog CRUD)    | **admin** |
| Vehicle parts | `POST /api/vehicles/{vehicle}/parts` (require, testing-only), `PATCH /api/vehicles/{vehicle}/parts/{vehiclePart}` (advance status — `required`/`ordered` excluded), `DELETE /api/vehicles/{vehicle}/parts/{vehiclePart}` (remove) | **admin** |
| Ordering      | `GET /api/vehicles/{vehicle}/parts/{vehiclePart}/suppliers` (in-stock listings for the required part) | authenticated |
|               | `POST /api/vehicles/{vehicle}/parts/{vehiclePart}/order` (place the order) | **admin** |
| Suppliers     | `GET /api/suppliers`, `GET /api/suppliers/{supplier}`                     | authenticated |
|               | `POST /api/suppliers`, `PUT/PATCH/DELETE /api/suppliers/{supplier}`       | **admin** |
| Supplier parts| `GET /api/suppliers/{supplier}/parts` (stock list)                        | authenticated |
|               | `POST /api/suppliers/{supplier}/parts`, `PATCH/DELETE /api/suppliers/{supplier}/parts/{supplierPart}` | **admin** |

Requests are validated by `App\Http\Requests` FormRequest classes and responses are shaped by `App\Http\Resources` JSON resources, which eager-load and nest related `Team`/`User`/`Vehicle`/`Part`/`Supplier` data. The web app's login/session auth (Breeze) is entirely separate from this token auth — they share the same `users` table but nothing else.

### Authorization

Authentication only establishes *who* you are; `App\Policies` decides what you may do. The rule is deliberately simple: **any authenticated user can read anything, only `UserRole::Admin` can write anything.** A non-admin attempting a write gets `403`.

That matters most on `/api/users`, which assigns the `role` column — without a policy, any authenticated user could `PATCH` their own role to `admin`, so every role is effectively admin. `role` defaults to `staff` (see the users migration) and self-registration is open, so the unauthorized path would otherwise be: register → get a token → escalate.

Authorization is enforced at two layers, both reading the same policy:

- **`Gate::authorize()` in every controller action.** This is the authoritative check — it covers `index`/`show`/`destroy`, which have no FormRequest.
- **`authorize()` on each FormRequest.** Redundant for `store`/`update`, but it runs *before* validation, so an unauthorized write returns `403` rather than leaking field-level `422` validation detail.

Abilities follow Laravel's conventions (`viewAny`, `view`, `create`, `update`, `delete`), plus one custom `order` ability on `VehiclePartPolicy` — placing an order commits a purchase and decrements supplier stock, so it is gated separately from a plain status `update`.

Roles live in `App\Enums\UserRole` (`admin`, `principal`, `engineer`, `driver`, `staff`) and `User::isAdmin()` is the single predicate the policies call. Only `admin` is privileged today; the other four are labels with no additional rights, so narrowing a rule means editing one policy method rather than hunting for role checks.

> **Note:** reads are not team-scoped. Any authenticated user can read every team's vehicles, supplier pricing, and the full user roster. That's intentional for a demo, but it is the first thing to change for multi-tenant use.

### Rate limiting

Laravel's `api` middleware group is **not** throttled by default — `Illuminate\Foundation\Configuration\Middleware` only adds `throttle:` when `throttleApi()` is called, which `bootstrap/app.php` now does. Two named limiters are defined in `AppServiceProvider::configureRateLimiting()`:

| Limiter | Applies to | Limit |
|---------|-----------|-------|
| `api` | the whole `/api` group | 60/min, keyed per authenticated user (falling back to IP) |
| `login` | `POST /api/tokens`, `POST /api/tokens/refresh` | 5/min per email+IP, **and** 20/min per IP |

Both limiters must stay defined: a `throttle:<name>` referring to an undefined limiter does not throttle, it falls through to treating the name as a literal attempt count — so an undefined `api` limiter would silently disable throttling rather than fail loudly. `ApiThrottleTest` asserts the limiter is registered for exactly this reason.

The tighter `login` limiter exists because the token endpoints handle credentials and are unauthenticated. The per-email key mirrors the web login throttle in `App\Livewire\Forms\LoginForm`; the per-IP key stops one host spraying many different addresses.

## Frontend

The Livewire/Volt pages (`resources/views/livewire/teams`, `.../vehicles`, `.../parts`, `.../suppliers`) provide browser-based CRUD over the same Eloquent models the API uses — team listing/creation, team detail (staff + vehicle roster), vehicle listing with status filtering, a parts catalog, a suppliers catalog (each with its own stock list of quantity/price/location/delivery cost), and a vehicle detail page for updating race status and managing part requirements. The vehicle page enforces the same testing → active rule as the API: the "active" option disappears from the status dropdown (and resubmitting it server-side still gets rejected) until every required part is `fitted`.

Clicking "Order" on a `required` part (instead of "Advance", which only appears once a part is past `required`) opens a modal — reusing Breeze's `<x-modal>` component, shown/hidden reactively from a Livewire property rather than a client-side event — listing every supplier currently stocking that part, sorted by price, with quantity/location/delivery cost per option. Confirming a selection calls the same `VehiclePart::placeOrder()` the API uses.

The Livewire pages mutate Eloquent models directly rather than calling the API, so they enforce authorization independently — each write action (`createTeam`, `deleteVehicle`, `updateStatus`, `requirePart`, `advanceStatus`, `removePart`, `confirmOrder`, `addSupplierPart`, …) opens with its own `Gate::authorize()` against the same policies the API uses. The matching controls are wrapped in `@can`, so a non-admin doesn't see buttons that would only return `403`. Hiding a control is presentation, not protection: the server-side check in the action is what actually enforces the rule, and `WebAuthorizationTest` calls the actions directly to prove it.

Auth (login/register/password reset/profile) comes from Breeze's Livewire stack unmodified. Note that registration is open and new accounts get the default `staff` role, which grants read access only.

## Testing

```bash
php artisan test
```

Feature tests cover both the API (`tests/Feature/Api`) and the Livewire pages (`tests/Feature/*PageTest.php`), including guest-access rejection, validation, and Livewire component interactions via `Livewire\Volt\Volt::test()`.

Authorization and rate limiting have dedicated suites, since a regression in either is silent — the app keeps working, just for the wrong people:

- `tests/Feature/Api/ApiAuthorizationTest.php` — asserts each of the four non-admin roles is refused every write, that a non-admin cannot escalate their own `role`, and that reads still succeed
- `tests/Feature/WebAuthorizationTest.php` — the same, driven through the Livewire actions, plus `@can` control visibility
- `tests/Feature/Api/ApiThrottleTest.php` — asserts repeated failed logins return `429`, that the throttle is keyed per account, and that the `api` limiter is registered

Because writes are admin-only, tests that exercise one act as `User::factory()->role(UserRole::Admin)->create()`; tests covering reads deliberately keep the factory default (`staff`) so the read-open rule stays under test. Both business rules — testing → active and the parts-ordering workflow — are covered at three levels each (model unit test, API test, Livewire test): `tests/Unit/VehiclePartsBusinessRuleTest.php` / `tests/Unit/VehiclePartOrderingTest.php`, `tests/Feature/Api/VehiclePartApiTest.php` / `VehiclePartOrderApiTest.php`, and `tests/Feature/VehicleManagementPageTest.php` — deliberately redundant, since each rule is enforced independently in both the API and UI layers and each needs its own proof it's wired up.