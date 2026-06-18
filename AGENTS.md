# Expedientes Repo (SiRex)

Laravel 12 + Livewire 3 + Flux UI 2 + Volt 1.7.  
Spanish locale (`es`), Argentina timezone (`America/Argentina/Buenos_Aires`).  
App name: "Servicio de Exp-Res" / branded "SiRex" in the sidebar.

## Commands

```bash
composer dev                     # server + queue + vite (Ctrl+C to stop)
vendor/bin/pest                  # run all tests (SQLite in-memory)
vendor/bin/pest --filter=<name>  # run tests matching name
vendor/bin/pint                  # format code (auto-fixes on save)
npm run build                    # build production assets
php artisan make:livewire        # create Livewire component
php artisan make:model -m        # model + migration
php artisan migrate              # run migrations
php artisan tinker               # interactive REPL
```

## Setup

1. `composer config http-basic.composer.fluxui.dev <username> <license-key>` (CI secrets)
2. `composer install`
3. `npm install`
4. `cp .env.example .env && php artisan key:generate`
5. `npm run dev`

## Architecture

- **App routes** mounted under `/expedientes/public/` — Livewire update/script routes customized in `routes/web.php:7-14`
- **Auth**: Volt-based login/register/password-reset (Livewire starter kit), custom permission system via `Permiso` model & `User::permiso()`
- **DB connections**: `mysql` (default), `mysql_admin` (Expediente, User), `mysql_legui` (Oficina queries), `pgsql_mitiv` (VistaExpedientes), `pgsql` (generic)
- **Custom audit**: `App\Traits\Auditable` trait on Expediente — auto-records created/updated/deleted to `audits` table
- **SoftDeletes** on Expediente and Resolucion
- **UI stack**: Tailwind CSS v4 (`@import 'tailwindcss'`), Flux components, Quill editor (resolución documents), SweetAlert2 via `jantinnerezo/livewire-alert`
- **PDF**: `barryvdh/laravel-dompdf` for resoluciones generation
- **Volt** mounts `resources/views/livewire/` and `resources/views/pages/` (`app/Providers/VoltServiceProvider.php`)
- **Helpers** (autoloaded in `composer.json`): `formatearFecha()`, `formatearFechaLarga()`, `formatearMoneda()`, `num2letras()` in `app/Helpers/fecha.php`

## Gotchas

- **Flux credentials required** before `composer install`
- `composer dev` runs 3 processes (serve + queue + vite) — stop with `Ctrl+C`
- Tests use **SQLite in-memory** via `phpunit.xml` — migrations not needed locally
- **Branches**: `develop` (PRs), `main` (releases)
- PHP 8.2+ / Node 22+ required
- Pint auto-formats; CI auto-fixes style

## Test notes

- Feature tests auto-use `RefreshDatabase` (configured in `tests/Pest.php`)
- All tests run against ephemeral SQLite — external DB connections not available in tests
