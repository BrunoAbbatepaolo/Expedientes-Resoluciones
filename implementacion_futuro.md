# Plan de Acción Técnico — Auditoría estática Exp-Res

Generado a partir de análisis estático del código (sin `composer install`/`npm install` ejecutados). Cada hallazgo incluye archivo y línea. Prioridad: 🔴 Alta (bloqueante/seguridad) · 🟡 Media · 🟢 Baja.

---

## Estado de implementación (actualizado 2026-07-17)

Entorno de trabajo: contenedor Docker MySQL aislado (`exp-res-mysql`, puerto 3309) con datos de prueba descartables — no es la base de producción/legacy.

- ✅ **Sección 0** (higiene del repo) — resuelto, commit `8aebf7d` + `8e95850`.
- ✅ **1.1** (`Detalles` fatal error) — resuelto, commit `8e95850`.
- ✅ **2.4** (bug relación `Area::oficinas()`) — resuelto, commit `8e95850`.
- ✅ **2.5** (esquema `users` inconsistente) — resuelto. Detalle:
  - `users` ahora se crea en `mysql_admin` con columnas reales (`nombre`, `apellido`, `email`, `profile_photo_path`, etc.), no en la conexión default con `name`/`email` del starter kit.
  - Se eliminó la migración `2025_04_01_131040_add_profile_photo_to_users_table` (agregaba una columna `profile_photo` no usada por ningún componente, y era la causante del error de migración documentado en `migrate_error.txt`).
  - `UserFactory` y `DatabaseSeeder` corregidos a `nombre`/`apellido`.
  - `resources/views/livewire/auth/register.blade.php` seguía usando el campo `name` del starter kit (nunca se había adaptado) — corregido a `nombre`/`apellido`, igual que `settings/profile.blade.php`.
  - Tests `RegistrationTest` y `ProfileUpdateTest` actualizados a los campos reales.
  - **Bug relacionado encontrado y corregido (era parte de 2.7):** la migración `create_permisos_table` creaba la tabla en la conexión default en vez de `mysql_admin` (donde vive el modelo `Permiso` y donde la migración posterior sí opera). Antes "funcionaba" solo porque `users` estaba, por error, en esa misma conexión default. Corregido: `permisos` ahora se crea en `mysql_admin`.
  - Validado con `migrate:fresh --seed` contra el contenedor de prueba: `admin.users` y `admin.permisos` se crean con el esquema correcto y el usuario semilla queda con `nombre`/`apellido` reales.
  - **Hallazgo nuevo (fuera de alcance de este punto, documentado para más adelante):** no existe ninguna migración `create_expedientes_table` / `create_oficinas_table` / `create_areas_table` en el repo — esas tablas son legacy y se asumen pre-existentes; las migraciones del repo solo las alteran (`add_oficina_id_to_expedientes`, etc.). Un `migrate:fresh` desde cero en una base 100% vacía falla en `2025_09_02_120000_add_oficina_id_to_expedientes` porque `expedientes` no existe. Para reproducir un entorno de desarrollo limpio hoy hace falta importar el esquema legacy manualmente (fuera del flujo de migraciones).
  - **Hallazgo nuevo:** `RefreshDatabase` (usado en los tests) solo gestiona la conexión `default` (sqlite en memoria durante tests). No limpia `mysql_admin`, así que los tests que tocan `User`/`Permiso` requieren que esa conexión ya tenga el esquema correcto y datos limpios de antemano — no hay aislamiento automático entre corridas. Requeriría una estrategia de testing multi-conexión (ej. sqlite en memoria también para `mysql_admin`, o transacciones manuales por conexión) para ser confiable en CI.

---

## 0. Higiene del repositorio (antes que nada)

🔴 Hay **archivos de depuración commiteados en la raíz del repo**, incluyendo un **archivo SQLite real** (`laravel`, formato `SQLite format 3`, 106 KB) y logs con salidas de errores de migración:

- `laravel` — base de datos SQLite binaria, trackeada en git.
- `migrate_error.log`, `migrate_error.txt`, `test_error.txt`, `test_migrate.php` — salidas de sesiones de debug previas.
- Archivos con nombres corruptos, restos de un comando mal escapado: `', 'expedientes.id', '=', 'expediente_pases.expediente_id')`, `elect('id', 'nombre')`, `elect('oficina_id')`, `nombre')`.
- `migrate_error.txt` confirma un **fallo real de migración**: `SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name 'profile_photo'` al correr `2025_04_01_131040_add_profile_photo_to_users_table` — evidencia de que el esquema de migraciones no está en un estado limpio/reproducible.

**Acción:** revisar si `laravel` contiene datos sensibles, eliminar los 7 archivos del control de versiones (`git rm`), agregar `*.sqlite`, `migrate_error.*`, `test_error.*` a `.gitignore`.

---

## 1. Trabajo a medias

| # | Prioridad | Hallazgo | Ubicación |
|---|---|---|---|
| 1.1 | 🔴 | `Livewire\Detalles` no importa `use Livewire\Component;` pero la clase hace `extends Component` → **fatal error al cargar la clase**. Rompe `/expedientes/detalle/{id}` por completo. | `app/Livewire/Detalles.php:1-8` |
| 1.2 | 🔴 | `CrearResolucion::guardar()` y `guardarPersonalizado()` son **stubs**: validan y muestran "guardado exitosamente" pero nunca llaman a `Resolucion::create(...)`. Solo `guardarPlantilla()` persiste de verdad. | `app/Livewire/CrearResolucion.php:380-396, 398-409` (comentario literal `// Ejemplo: ResolucionService::crear(...)`) |
| 1.3 | 🟡 | 6 de 9 tipos de resolución (`Cancelaciones`, `Resciciones`, `Transferencias`, `Transferencia-Cancelacion`, `Rectificacion`, `Otros`) no tienen plantilla Blade en `resources/views/prototipos/` — solo existen 3. Se captura la excepción y se muestra "Error al cargar la plantilla". | `app/Livewire/ElegirResolucion.php:16-26`, `resources/views/prototipos/` |
| 1.4 | 🟡 | La tabla/modelo `Pase` (historial de traslados entre oficinas) está completamente modelado pero **ningún componente Livewire escribe en ella**. El "pase" real hoy es un simple `UPDATE` de `ofi_salida`/`fecha_salida` sobre `Expediente`. Esto es la brecha más importante respecto al objetivo de negocio ("historial completo de pases entre oficinas"). | `app/Models/Pase.php`, `app/Livewire/Forms/ExpedienteForm.php:83-103` |
| 1.5 | 🟡 | `Livewire\Detalles` reconstruye a mano un historial falso de 2 eventos (ingreso/salida) en vez de leer la relación real `Expediente::pases()`, pese a hacer eager-load de `pases.oficina`/`pases.oficinaOrigen` que luego descarta. | `app/Livewire/Detalles.php:18-41` |
| 1.6 | 🟢 | `Oficinas::inputBusqueda()` — método vacío, sin uso real (coincide con el nombre de una propiedad, no es un hook Livewire). | `app/Livewire/Oficinas.php:38` |
| 1.7 | 🟢 | `CrearResolucion::agregarArchivos()` — vacío (marcado como intencional en comentario). | `app/Livewire/CrearResolucion.php:363-366` |
| 1.8 | 🟢 | `Forms/ResolucionForm::store()` y `Forms/ExpedienteForm::delete()` — definidos pero nunca invocados desde ningún componente. | `app/Livewire/Forms/ResolucionForm.php:52-56`, `app/Livewire/Forms/ExpedienteForm.php` |
| 1.9 | 🟢 | `resources/views/welcome.blade.php:13-23` — `@keyframes` con `...` como cuerpo (placeholder sin completar); las animaciones referenciadas no funcionan. | `resources/views/welcome.blade.php:13-35` |
| 1.10 | 🟢 | `AppServiceProvider::register()`/`boot()` vacíos pese a importar `Livewire` y `DashboardPanel` sin usarlos. | `app/Providers/AppServiceProvider.php` |
| 1.11 | 🟢 | `app/Console/Commands/TestConexion.php` — comando de debug con oficina hardcodeada (id 89), sin manejo de errores; parece código de prueba olvidado en el repo. | `app/Console/Commands/TestConexion.php` |

---

## 2. Errores y Seguridad

### 2.1 Autorización entre oficinas (🔴 crítico)

El sistema de permisos (`Permiso` / `User::permiso()`) existe pero se aplica **solo parcialmente**:

- `Expedientes::editar()`, `actualizar()`, `eliminarExpediente()` hacen `Expediente::find($id)` / `findOrFail(...)->delete()` **sin verificar** que el expediente pertenezca a la oficina del usuario ni chequear permiso `expediente_editar`. Un usuario autenticado puede editar/borrar expedientes de **cualquier oficina** manipulando el `id` enviado por `wire:click`. — `app/Livewire/Expedientes.php:295-353`
- `Livewire\Detalles::mount($id)` hace `Expediente::findOrFail($id)` sin chequear pertenencia de oficina — cualquier usuario autenticado puede ver el detalle de cualquier expediente cambiando el `id` en la URL. — `app/Livewire/Detalles.php:15-22`
- `Resoluciones::cargarResolucion()`, `guardarEdicion()`, `borrar()` **no tienen ningún chequeo de permiso ni pertenencia** — cualquier usuario autenticado puede editar o borrar cualquier resolución del sistema. Contrasta con `ListaUsuario`, que sí valida `permiso('lista_usuario_editar')` antes de cada acción. — `app/Livewire/Resoluciones.php:23-65`
- `CrearResolucion` y `ElegirResolucion` no chequean permiso `resolucion_editar` en `mount()`.
- No existen Policies ni Gates de Laravel (`app/Policies` no existe) — toda la autorización depende de chequeos manuales inconsistentes.

**Acción recomendada:** introducir una capa de autorización uniforme (Policies de Laravel o un middleware/trait común) que se aplique en **todos** los métodos de escritura de `Expedientes`, `Resoluciones` y `CrearResolucion`, validando pertenencia a oficina + permiso específico, no solo en la lectura.

### 2.2 Concurrencia (🟡)

No hay `DB::transaction()` (se usa `beginTransaction()/commit/rollBack` manual en `ExpedienteForm::update()`) ni `lockForUpdate()` en las operaciones de pase/actualización de expediente. Dos usuarios pasando el mismo expediente simultáneamente pueden pisarse sin detección. — `app/Livewire/Forms/ExpedienteForm.php`

### 2.3 XSS potencial (🟡)

`{!! $this->plantilla !!}` renderiza sin escapar el HTML producido por el editor Quill en el modo "personalizado", y se guarda tal cual en `guardarPlantilla()` (`'plantilla' => $this->plantilla`). Si ese contenido llega a mostrarse a otros usuarios/oficinas sin sanitización server-side, es un vector de XSS almacenado. No se encontró `strip_tags`/HTML Purifier en el componente. — `resources/views/livewire/crear-resolucion.blade.php:776`, `app/Livewire/CrearResolucion.php:285`

**Acción recomendada:** sanitizar el HTML del editor Quill antes de persistirlo (ej. `HTMLPurifier` o whitelist de tags permitidos).

### 2.4 Bug de relación Eloquent (🔴 — afecta datos, no solo seguridad)

`Area::oficinas()` invierte `foreignKey`/`localKey`: `hasMany(Oficina::class, 'codigo', 'cod_area')` compara `Oficina.codigo = Area.cod_area`, cuando debería ser al revés (`Oficina.cod_area = Area.codigo`), como sí está bien hecho en `Oficina::area()` y `Expediente::area()`. Tal como está, la relación devuelve resultados vacíos o incorrectos. — `app/Models/Area.php:23`

### 2.5 Esquema de `users` inconsistente / migración rota (🔴)

- `User.php` opera sobre la conexión `mysql_admin` con columnas `nombre, apellido, ...`, pero la migración `0001_01_01_000000_create_users_table.php` crea la tabla `users` en la conexión **default** con columnas `name, email, ...` del starter kit — son dos tablas distintas, la migrada nunca la usa la app real.
- `migrate_error.txt` confirma que `2025_04_01_131040_add_profile_photo_to_users_table` falla con `Duplicate column name 'profile_photo'` — el historial de migraciones no es reproducible desde cero en el estado actual del repo.
- `database/factories/UserFactory.php` y `DatabaseSeeder.php` usan `'name'` y la conexión default, incompatible con el schema/conexión real — el seeder actual **no genera usuarios utilizables** por la aplicación.

**Acción recomendada:** consolidar la migración de `users` para que coincida con `mysql_admin` + columnas reales (`nombre`, `apellido`), o documentar explícitamente por qué coexisten dos tablas `users`. Corregir `UserFactory`/`DatabaseSeeder`.

### 2.6 Esquema de `pases` divergente (🟡)

El `$fillable` real de `Pase` (`fecha, hora, observacion, folio, importado, firmado, oficina_origen_id`) no coincide con lo que define la migración `2026_03_17_181039_create_pases_table.php` (`fecha_ingreso, fecha_salida, observaciones`). El esquema real en producción parece haberse creado/alterado fuera del flujo estándar de migraciones (ver también `ImportarPases.php:67-89`, que re-declara columnas ad-hoc).

**Acción recomendada:** auditar el esquema real de `pases` en la base de `mysql_admin` y hacer que las migraciones lo reflejen fielmente (o generar una migración de corrección).

### 2.7 Integridad referencial débil (🟡)

- En `pases`, solo `expediente_id` tiene `->constrained()`; `oficina_id`, `oficina_destino_id`, `oficina_origen_id`, `user_id` son `unsignedBigInteger` sueltos sin FK.
- `oficina_destino_id` no tiene índice (a diferencia de `oficina_origen_id`, que sí).
- `resoluciones.numero_exp` / `numero_resolucion` no tienen índice ni `unique`, pese a usarse para relacionar y (presumiblemente) evitar duplicados.
- `Permiso` se crea en la migración sobre la conexión default, pero el modelo lee/escribe en `mysql_admin` (migración posterior sí corrige el `connection()`, pero deja el estado inicial inconsistente si se migra desde cero).

### 2.8 Detalles menores (🟢)

- `auth()->user()->permiso(...)` sin `?->` en `Expedientes.php:110-111,123,130-131` (otros componentes sí usan `?->`) — riesgo de error si el guard no resolviera usuario en ese contexto.
- `Route::view('dashboard', ...)` es la única ruta con middleware `verified`; como `User` no implementa `MustVerifyEmail`, ese middleware es hoy un no-op — inconsistente pero sin impacto real.
- Formularios de logout con `method="POST"` puro (no `wire:submit`) en `sidebar.blade.php:145,192` y `header.blade.php:84` — confirmar que tienen `@csrf`.

---

## 3. Refactorización y código muerto

| # | Hallazgo | Ubicación |
|---|---|---|
| 3.1 | Lógica de conversión número→letras y formateo de fecha/moneda **duplicada** entre `app/Helpers/fecha.php` (funciones globales autoloaded) y métodos privados de `CrearResolucion` (`convertNumberToLetters`, `formatearFecha`, etc.). Consolidar en el helper. | `app/Helpers/fecha.php`, `app/Livewire/CrearResolucion.php` |
| 3.2 | Las 3 vistas de `resources/views/prototipos/` repiten ~300 líneas de `<style>` idénticas en cada archivo, en vez de extraerse a un partial o al `resolucion.css` existente. | `resources/views/prototipos/*.blade.php` |
| 3.3 | `resources/views/prototipos/resolucion.css` no se importa desde ningún lado — código muerto (o falta conectarlo, ver 3.2). | `resources/views/prototipos/resolucion.css` |
| 3.4 | `resources/views/components/button-blue.blade.php` y `placeholder-pattern.blade.php` — sin referencias en ninguna vista. | `resources/views/components/` |
| 3.5 | `resources/views/components/layouts/auth/card.blade.php` y `auth/split.blade.php` — boilerplate del starter-kit sin usar (solo `simple` está activo). | `resources/views/components/layouts/auth/` |
| 3.6 | Conexiones `pgsql` (genérico), `mariadb`, `sqlsrv` en `config/database.php` sin ninguna referencia en `app/` — limpiar o documentar por qué se mantienen. | `config/database.php` |
| 3.7 | `Permiso::oficina()` mantenida pese a comentario propio "NO usarla". | `app/Models/Permiso.php:30-33` |
| 3.8 | `User::oficinaIdPara()` duplica `oficinaAsignadaId()` ("mantengo por compatibilidad"). | `app/Models/User.php:66-67` |
| 3.9 | Variable `$oficina` calculada y descartada sin uso en `Detalles`. | `app/Livewire/Detalles.php:34` |
| 3.10 | Bloque CSS roto (llave huérfana `font-weight: inherit; color: inherit; }` sin selector) repetido en los 3 prototipos. | `resources/views/prototipos/*.blade.php` |
| 3.11 | Solo existe `UserFactory`; no hay factories para `Expediente`, `Oficina`, `Pase`, `Resolucion`, `Area` — sin forma de sembrar datos de prueba del dominio real para desarrollo/tests. | `database/factories/` |

---

## Orden de trabajo sugerido

1. **Higiene del repo** (sección 0) — rápido, bajo riesgo, mejora la base para todo lo demás.
2. **2.4 y 2.5** (bug de relación `Area`, esquema `users` roto) — corrigen datos incorrectos silenciosos, deberían resolverse antes de escribir nueva lógica sobre esos modelos.
3. **1.1** (`Detalles` fatal error) — bloquea una funcionalidad completa, fix trivial (agregar el `use`).
4. **2.1** (autorización entre oficinas) — el hallazgo de mayor impacto de negocio: sin esto, el sistema no es seguro para uso multi-oficina real.
5. **1.4 + 1.5 + 2.6** (esquema y uso real de `pases`) — es la pieza central para cumplir el objetivo de negocio de "historial completo de pases entre oficinas"; hoy es la brecha más grande entre lo modelado y lo implementado.
6. **1.2 + 1.3** (flujos de resolución incompletos) — completar guardado real en los 2 modos que faltan, y las plantillas faltantes o un mensaje claro de "no disponible".
7. **2.3** (sanitización XSS del editor Quill).
8. Resto de hallazgos 🟡/🟢 y sección 3 (refactor) como limpieza continua.

No se realizaron cambios de código ni se ejecutó ninguna instalación — este documento es solo diagnóstico, a la espera de aprobación para actuar sobre los puntos priorizados.
