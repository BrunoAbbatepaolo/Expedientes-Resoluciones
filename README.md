# Expedientes-Resoluciones (SiRex)

Cuaderno digital centralizado para el seguimiento de expedientes administrativos. Reemplaza el cuaderno manual en el que cada oficina anotaba a mano el ingreso y egreso de expedientes, y lo transforma en un historial digital de **pases entre oficinas**, con un módulo adicional para gestionar el **traspaso y la redacción de resoluciones**.

> Documento generado a partir de un análisis estático del código (sin `composer install` / `npm install` ejecutados). Los hallazgos de auditoría, bugs y trabajo pendiente están en [`implementacion_futuro.md`](./implementacion_futuro.md).

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 (PHP ^8.2) |
| Componentes reactivos | Livewire 3 + **Volt 1.7** (SFC de Livewire, sin Controllers tradicionales) |
| UI Kit | **Flux UI 2** (`livewire/flux`) |
| Estilos | Tailwind CSS **v4** (sin `tailwind.config.js`, configuración vía `@theme` en CSS) |
| JS de interacción | **Alpine.js** (incluido con Livewire/Flux) — *no hay Vue.js en el proyecto real, pese a mencionarse en la propuesta original* |
| Editor de texto enriquecido | Quill.js (redacción de resoluciones) |
| PDF | `barryvdh/laravel-dompdf` |
| Notificaciones UI | SweetAlert2 vía `jantinnerezo/livewire-alert` |
| Build | Vite 6 |
| Tests | Pest 3 (SQLite en memoria) |
| Locale | `es` / `es_AR`, timezone `America/Argentina/Buenos_Aires` |

> ⚠️ El stack real usa **Alpine.js**, no Vue.js. Si el equipo espera componentes Vue, no existen en este repo (`resources/js/app.js` solo inicializa SweetAlert2).

## Arquitectura

### Sin capa de Controllers tradicional
La lógica de negocio vive casi por completo en **componentes Livewire** (`app/Livewire/`), montados directamente por rutas mediante `Livewire\Volt\Volt::route(...)`. `app/Http/Controllers/` solo contiene la clase base vacía y `Auth/VerifyEmailController`.

### Multi-conexión de base de datos
El sistema centraliza datos que viven en **bases de datos distintas** (probablemente heredadas de sistemas preexistentes de cada dependencia). Configuradas en `config/database.php` / `.env`:

| Conexión | Motor | Uso real |
|---|---|---|
| `mysql` (default) | MySQL | `resoluciones`, `resolucion_archivos`, `audits`, `tasks` |
| `mysql_admin` | MySQL | `users`, `expedientes`, `pases`, `oficinas`, `permisos` (núcleo del negocio) |
| `mysql_legui` | MySQL | `areas` (tabla `Area`, sistema legado "LEGUI") |
| `pgsql_mitiv` | PostgreSQL | `vista_expedientes` (vista de solo lectura de un sistema externo "MITIV") |
| `pgsql`, `mariadb`, `sqlsrv` | — | Declaradas en config, **sin uso real detectado** |

No hay integridad referencial entre conexiones (no es posible con FKs de motores distintos); el cruce entre `resoluciones.numero_exp` y `vista_expedientes.numero` se hace por coincidencia de string, no por clave foránea real.

### Sistema de permisos propio
No usa Policies/Gates de Laravel. Es un sistema propio basado en el modelo `Permiso` (`user_id`, `nombre`, `oficina_id`) y el método `User::permiso($nombre)`. Se aplica de forma **inconsistente** entre componentes (ver auditoría).

### Auditoría
`App\Traits\Auditable`, aplicado en el modelo `Expediente`, registra automáticamente altas/bajas/cambios en la tabla `audits` (relación polimórfica `morphTo`).

### Volt
`app/Providers/VoltServiceProvider.php` monta como vistas Volt tanto `resources/views/livewire/` como `resources/views/pages/`.

## Estructura de carpetas

```
app/
  Console/Commands/     Comandos Artisan (incluye ImportarPases, ETL desde pgsql_mitiv)
  Helpers/               Funciones globales autoloaded (fecha.php: formatearFecha, num2letras, etc.)
  Http/Controllers/      Casi vacío; Auth/VerifyEmailController es el único real
  Livewire/               Componentes de negocio (Expedientes, Resoluciones, Oficinas, etc.)
    Actions/              Acciones invocables (Logout)
    Forms/                 Livewire Form Objects (ExpedienteForm, ResolucionForm)
  Models/                 Eloquent models, uno por tabla/conexión
  Providers/              AppServiceProvider, VoltServiceProvider
  Traits/                 Auditable

resources/
  views/
    components/layouts/   Layouts de app autenticada y de auth (login/register)
    livewire/               Vistas Blade/Volt de cada componente (Expedientes/, auth/, settings/)
    prototipos/             Plantillas HTML de documentos de resolución (renderizadas dinámicamente)
    partials/, flux/         Partials compartidos y overrides de iconos Flux
  js/app.js               Bootstrap de Axios + SweetAlert2 (sin Vue)
  css/app.css              Tailwind v4 (@theme, sin config file)

database/
  migrations/              Historial de esquema (con inconsistencias, ver auditoría)
  factories/                Solo UserFactory
  seeders/                  DatabaseSeeder básico

routes/
  web.php                  Rutas de negocio, casi todas vía Volt::route()
  auth.php                  Rutas de autenticación (starter kit Livewire)
  console.php               Solo el comando `inspire` de ejemplo
```

## Modelo de datos (resumen)

- **User** (`mysql_admin`) — `nombre, apellido, email`. `hasMany(Permiso)`.
- **Oficina** (`mysql_admin`) — pertenece a un `Area` (`mysql_legui`); `hasMany(Expediente)`.
- **Area** (`mysql_legui`) — agrupa oficinas.
- **Expediente** (`mysql_admin`) — entidad central. `belongsTo(Oficina)`, `hasMany(Pase)`. Usa `SoftDeletes` y `Auditable`.
- **Pase** (`mysql_admin`) — registro de traslado de un expediente entre `oficina_origen` y `oficina_destino`. Modelo y tabla existen pero **actualmente no se escriben desde ningún componente Livewire** (ver auditoría: el "pase" real hoy es solo un UPDATE de campos en `Expediente`).
- **Permiso** (`mysql_admin`) — permisos por usuario y (opcionalmente) por oficina.
- **Resolucion** (`mysql` default) — se vincula a `VistaExpedientes` (`pgsql_mitiv`) por número de expediente (sin FK real). `hasMany(ResolucionArchivo)`. Usa `SoftDeletes`.
- **ResolucionArchivo** — PDFs adjuntos a una resolución.
- **VistaExpedientes** (`pgsql_mitiv`) — vista externa de solo lectura, fuente de expedientes del sistema "MITIV".
- **Audit** — bitácora polimórfica de cambios.
- **Task** — módulo interno de to-do, sin relación con el dominio de expedientes.

## Flujo funcional

### Pases de expedientes entre oficinas
1. Un usuario autenticado ve el listado de expedientes de **su oficina** (`Livewire\Expedientes`, filtra por `oficinaAsignadaId()` y permiso `expediente_ver`).
2. Al "pasar" un expediente, hoy el sistema **actualiza directamente** los campos `ofi_salida`, `fecha_salida`, `cod_area`, `cod_oficina` del propio registro `Expediente` (vía `ExpedienteForm`), en vez de crear un registro histórico en la tabla `pases`.
3. `Livewire\Detalles` muestra el "historial de pases" de un expediente, pero lo **reconstruye a mano** con 2 eventos fijos (ingreso/salida) leídos del propio `Expediente`, sin consultar la tabla `pases` real.
4. El comando `app/Console/Commands/ImportarPases.php` importa expedientes desde la base externa `pgsql_mitiv` (sistema "MITIV") hacia `mysql_admin`.

> El modelo de datos para un historial multi-oficina completo (tabla `pases`) **ya existe**, pero la capa de aplicación todavía no lo usa como fuente de verdad. Ver detalle en la auditoría.

### Traspaso de resoluciones
1. El usuario elige un tipo de resolución en `Livewire\ElegirResolucion` (9 tipos definidos).
2. Redirige a `Livewire\CrearResolucion`, que ofrece 3 modos: **plantilla completa**, **plantilla con datos** y **personalizado** (editor Quill).
3. Solo el flujo de "plantilla" (`guardarPlantilla()`) persiste realmente el registro `Resolucion` + sus PDFs (`ResolucionArchivo`). Los otros dos modos de guardado (`guardar()`, `guardarPersonalizado()`) están **incompletos**: validan y muestran mensaje de éxito, pero no escriben en base de datos.
4. Solo 3 de los 9 tipos de resolución tienen plantilla Blade implementada en `resources/views/prototipos/`.
5. `Livewire\Resoluciones` permite listar, editar y borrar resoluciones ya creadas.

## Rutas principales

| Ruta | Componente | Middleware |
|---|---|---|
| `/` | vista `welcome` | — |
| `/dashboard` | `DashboardPanel` (vía Volt, `dashboard.blade.php`) | `auth`, `verified`* |
| `/expedientes`, `/expedientes/ingresados`, `/expedientes/egresados` | `Expedientes` | `auth` |
| `/expedientes/detalle/{id}` | `Detalles` | `auth` |
| `/oficinas` | `Oficinas` | `auth` |
| `/usuarios` | `ListaUsuario` | `auth` |
| `/resoluciones` | `Resoluciones` | `auth` |
| `/resoluciones/elegir` | `ElegirResolucion` | `auth` |
| `/resoluciones/crear/{tipo}` | `CrearResolucion` | `auth` |
| `/resoluciones/descargar-pdf/{index}` | closure (sirve PDF desde `storage`) | `auth` |
| `/settings/*` | Volt (perfil, password, apariencia) | `auth` |

\* `verified` es actualmente un no-op: `User` no implementa `MustVerifyEmail`.

## Requisitos e instalación

- PHP **8.2+** con extensión `pdo`
- Node **22+**
- Composer, npm
- MySQL (conexiones `mysql`/default y `mysql_admin`, `mysql_legui`) y PostgreSQL (`pgsql_mitiv`) accesibles
- Credenciales privadas de **Flux UI Pro** (`composer config http-basic.composer.fluxui.dev <usuario> <license-key>`) — **requerido antes de `composer install`**

```bash
# 1. Credenciales de Flux (una sola vez)
composer config http-basic.composer.fluxui.dev <usuario> <license-key>

# 2. Dependencias
composer install
npm install

# 3. Entorno
cp .env.example .env
php artisan key:generate
# Completar credenciales reales de cada conexión (mysql, mysql_admin, mysql_legui, pgsql_mitiv, redis)

# 4. Base de datos
php artisan migrate
# Nota: el equipo debería revisar el estado real del esquema antes de migrar en un
# entorno existente — ver "Errores y Seguridad" en implementacion_futuro.md
# (hay evidencia en el repo de un fallo de migración por columna duplicada).

# 5. Desarrollo
composer dev          # server + queue + vite en paralelo
# o por separado:
php artisan serve
npm run dev
```

### Tests

```bash
vendor/bin/pest                  # corre contra SQLite en memoria (RefreshDatabase)
vendor/bin/pest --filter=<name>
```

### CI

`.github/workflows/lint.yml` y `tests.yml` corren en push/PR a `develop` y `main` (Pint + Pest).

## Estado del proyecto

El proyecto es funcional en su núcleo (alta/edición de expedientes, listado por oficina, redacción de resoluciones vía plantilla), pero tiene módulos a medio terminar y puntos de seguridad pendientes de revisar antes de un uso multi-oficina real. El detalle completo — con archivo y línea de cada hallazgo — está en **[`implementacion_futuro.md`](./implementacion_futuro.md)**.
