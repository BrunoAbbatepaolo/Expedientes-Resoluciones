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
  - ✅ **Hallazgo resuelto (2026-07-17):** se agregaron las 3 migraciones de creación que faltaban, fechadas en 2020 a propósito para correr antes que el resto (`migrate:fresh` ya no falla desde una base 100% vacía). Detalle:
    - `2020_01_01_000000_create_areas_table` (`mysql_legui`), `2020_01_01_000001_create_oficinas_table` (`mysql_admin`), `2020_01_01_000002_create_expedientes_table` (`mysql_admin`). Columnas inferidas de los modelos (`Area`, `Oficina`, `Expediente`), sus factories y el uso real en `ImportarPases`/`Expedientes`/`ExpedienteForm`.
    - `create_expedientes_table` **no incluye `oficina_id`**: lo sigue agregando `2025_09_02_120000_add_oficina_id_to_expedientes` (ya era idempotente vía `hasColumn`), para no duplicar esa migración.
    - `create_expedientes_table` **sí incluye `deleted_at`**: `Expediente` usa `SoftDeletes` y ninguna migración existente lo agregaba — sin esto, `Expediente::delete()` fallaba con "columna deleted_at no existe" en cualquier base nueva (bug latente que solo no se notaba porque el contenedor de prueba tenía la columna agregada a mano en sesiones anteriores).
    - Se corrigió también el tipo de `ofi_salida`: es un id de oficina (`Oficina::find($expediente->ofi_salida)` en `Expedientes::editar()`), no texto — quedó como `unsignedBigInteger` nullable.
    - Sin `->constrained()` en `oficina_id`/`ofi_salida`/`cod_area`/`cod_oficina`/`codigo`: cruzan conexiones (`mysql_admin` ↔ `mysql_legui`) o son legacy sin garantía de integridad referencial, mismo criterio que `pases`/`permisos`/`resoluciones`.
    - Validado recreando las 3 bases (`laravel`/`admin`/`legui`) completamente vacías en el contenedor Docker y corriendo `php artisan migrate:fresh --seed --force`: las 20 migraciones corren en orden sin error, y una verificación end-to-end con Playwright (login, listado de expedientes) confirmó que la app sigue funcionando sobre el esquema recién creado.
  - **Hallazgo nuevo:** `RefreshDatabase` (usado en los tests) solo gestiona la conexión `default` (sqlite en memoria durante tests). No limpia `mysql_admin`, así que los tests que tocan `User`/`Permiso` requieren que esa conexión ya tenga el esquema correcto y datos limpios de antemano — no hay aislamiento automático entre corridas. Requeriría una estrategia de testing multi-conexión (ej. sqlite en memoria también para `mysql_admin`, o transacciones manuales por conexión) para ser confiable en CI.
- ✅ **2.1** (autorización entre oficinas) — resuelto para `Expediente` y para `Resolucion` (validación de permiso + pertenencia a oficina en ambos). Detalle:
  - Nuevo trait `App\Traits\AuthorizesOficina` con `autorizarPermiso(string $permiso)` (aborta 403 si el usuario no tiene el permiso) y `autorizarExpediente(Expediente $expediente, string $permiso)` (además exige que `expediente->oficina_id` coincida con la oficina asignada al usuario).
  - Aplicado en `Expedientes::editar()`, `actualizar()`, `eliminarExpediente()` y en `Detalles::mount()` — antes cualquier usuario autenticado podía leer/editar/borrar expedientes de otra oficina cambiando el `id` enviado al método Livewire.
  - Aplicado como chequeo de permiso (`resolucion_editar`) en `Resoluciones::cargarResolucion()`, `guardarEdicion()`, `borrar()`, y en `mount()` de `CrearResolucion`/`ElegirResolucion`.
  - ✅ **Limitación resuelta (2026-07-17):** `Resolucion` ahora tiene `oficina_id`. Detalle:
    - Migración `2026_07_17_160000_add_oficina_id_to_resoluciones_table` agrega `resoluciones.oficina_id` (`unsignedBigInteger`, nullable, indexado). **Sin `->constrained()`**: `resoluciones` vive en la conexión default (bd `laravel`) y `oficinas` en `mysql_admin` (bd `admin`) — mismo motivo que `oficina_id`/`oficina_origen_id` en `pases`, un FK real cruzando conexiones no es fiable en este esquema multi-base.
    - `Resolucion::$fillable` incluye `oficina_id` y se agregó la relación `oficina()`.
    - `CrearResolucion::persistirResolucion()` asigna `oficina_id` automáticamente a `auth()->user()->oficinaAsignadaId()` al crear (único punto de creación en el código; `ResolucionForm` solo edita, no crea).
    - Nuevo `AuthorizesOficina::autorizarResolucion(Resolucion $resolucion, string $permiso)`: valida el permiso y, si la resolución **tiene** `oficina_id`, que coincida con la oficina del usuario. Las resoluciones viejas con `oficina_id` null (todo lo cargado antes de este cambio) **no se bloquean** — solo se protegen las nuevas, como pidió el usuario.
    - Aplicado en `Resoluciones::cargarResolucion()`, `guardarEdicion()` (recarga la resolución vinculada al form) y `borrar()` (ahora carga el modelo antes de borrar, en vez de `Resolucion::destroy($id)` a ciegas).
    - `Resoluciones::render()` filtra el listado: solo resoluciones con `oficina_id` null (legacy, visibles a todos) o `oficina_id` igual a la oficina del usuario.
    - Validado con un script standalone (`bootstrap/app.php`) contra el contenedor de prueba: resoluciones de 2 oficinas + 1 legacy sin oficina → cada usuario ve solo la suya + la legacy; editar/borrar la de la oficina ajena → 403; editar la legacy → permitido para cualquiera con el permiso. **No se pudo probar la vista completa `/resoluciones` en navegador** porque el eager-load `Resolucion::with('expediente')` depende de Postgres (`pgsql_mitiv`, sistema MITIV externo), que no está levantado en este entorno de desarrollo — limitación preexistente del entorno, no de este cambio.
- ✅ **1.4 + 1.5 + 2.6** (esquema y uso real de `pases`) — resuelto parcialmente (ver limitación). Detalle:
  - Migración `create_pases_table` reescrita para coincidir con el esquema real ya usado por `ImportarPases` (`fecha`, `hora`, `observacion`, `folio`, `importado`, `firmado`, `oficina_origen_id`) en vez de la que tenía (`fecha_ingreso`/`fecha_salida`/`observaciones`, que además nunca llegó a correr limpia: la migración `add_oficina_destino_to_pases_table` duplicaba una columna que la base ya creaba, y `add_oficina_origen_id_to_pases_table` quedaba suelta). Se eliminaron esas dos migraciones ALTER, ahora todo vive en una sola migración consolidada.
  - `ImportarPases::handle()` ya no recrea el schema de `pases` a mano (Paso 2): ahora exige que la migración ya haya corrido, y falla con un mensaje claro si no. Antes esto era una segunda fuente de verdad del esquema, divergente de la migración real (ver 2.6).
  - `ExpedienteForm::update()`: cuando el usuario elige una oficina de destino distinta de `expediente->oficina_id` (un pase real), ahora se crea un registro en `pases` (`oficina_id`=destino, `oficina_origen_id`=origen, `user_id`=quien lo hizo) además de actualizar los campos de `Expediente` como antes.
  - `Detalles::mount()` ya no reconstruye un historial falso de 2 eventos: ahora lee `expediente->pases` real (con `oficina`/`oficinaOrigen` eager-loaded) y arma el array que la vista `detalles.blade.php` ya esperaba (`origen`, `destino`, `fecha`, `hora`, `observacion`, `importado` — la vista ya estaba escrita para este formato, solo el controller nunca la alimentó bien).
  - **Bug encontrado y corregido en el camino:** `Pase::$fillable` no incluía `oficina_origen_id` (aunque la columna y la relación `oficinaOrigen()` sí existían) — `Pase::create()` lo descartaba en silencio por mass-assignment. Sin este fix, el pase se guardaba con origen vacío.
  - **Hallazgo nuevo y corregido:** no existía ninguna migración para la tabla `audits` (usada por `App\Traits\Auditable`, aplicado en `Expediente`). Cualquier alta/edición/baja de un expediente en una base nueva fallaba con `SQLSTATE[42S02]: Base table ... 'audits' doesn't exist`. A diferencia de `expedientes`/`oficinas` (legacy, fuera del control de esta app), `audits` es una tabla propia de la app — se agregó la migración `2026_05_06_090000_create_audits_table.php`.
  - Validado extremo a extremo contra el contenedor de prueba (tablas `oficinas`/`expedientes` mínimas creadas ad-hoc solo para la prueba, luego eliminadas): pasar un expediente de "Oficina A" a "Oficina B" crea el `Pase` con origen/destino correctos, y `Detalles` lo lee en el formato correcto.
  - ✅ **Limitación resuelta (2026-07-17):** se implementó la bandeja de entrada. Detalle:
    - Migración `2026_07_17_150000_add_estado_to_pases_table` agrega `pases.estado` (`pendiente`/`aceptado`, default `pendiente`, indexado).
    - `ExpedienteForm::update()` ahora también graba `oficina_destino_id` (columna que ya existía, reservada para esto) y `estado='pendiente'` al crear el pase. `Expediente::oficina_id` **ya no se toca** en ese momento — el expediente sigue apareciendo en la oficina de origen hasta que el destino lo acepta (evita el bug de "no se reasigna" sin asumir la reasignación automática, que era la parte de decisión de negocio).
    - `ImportarPases::handle()` marca los pases históricos importados como `estado='aceptado'` (ya están resueltos, no deben aparecer como pendientes).
    - Nueva vista `Expedientes/entrantes` (ruta `expedientes/entrantes`, tab en el sidebar): lista, para la oficina del usuario, los expedientes con un pase `pendiente` cuyo `oficina_destino_id` sea esa oficina (`Expedientes::getEntrantes()`).
    - Nuevo método `Expedientes::aceptarPase($paseId)`: dentro de `DB::transaction()` con `lockForUpdate()`, valida permiso `expediente_editar` y que el pase pertenezca a la oficina del usuario y esté `pendiente`, marca el pase `aceptado` y recién ahí reasigna `Expediente::oficina_id` a `oficina_destino_id`.
    - Validado extremo a extremo con Playwright contra el contenedor de prueba: usuario B ve el expediente en "Entrantes", lo acepta, desaparece de su bandeja y pasa a aparecer en su listado "Todos" (oficina reasignada correctamente).
  - ✅ **Bug encontrado y corregido en uso real (2026-07-17, post-deploy):** al aceptar un pase, el expediente aparecía directo en "Egresados" de la oficina destino en vez de "Ingresados", y desaparecía por completo de la oficina de origen (sin dejar rastro de que había pasado por ahí). Causa raíz: `fecha_salida` es un campo que se carga en el mismo formulario que la oficina de destino (modal de edición), y `Ingresados`/`Egresados` se decidían solo por `whereNull/whereNotNull(fecha_salida)` sobre el `oficina_id` **actual** — al transferir, `fecha_salida` quedaba seteada para siempre y `Egresados` dependía de seguir teniendo `oficina_id` = la mía, cosa que deja de ser cierto apenas el destino acepta. Fix:
    - `Expedientes::aceptarPase()` ahora también limpia `fecha_salida` a `null` al reasignar `oficina_id` — así el expediente entra a la nueva oficina como "Ingresado", no "Egresado".
    - `Expedientes::getExp()`, rama `egresados`: dejó de depender solo de `deOficina() + fecha_salida`. Ahora es un OR: (a) lo tengo yo con `fecha_salida` cargada a mano (cierre sin pase, caso legacy), o (b) existe un `Pase` **aceptado** cuyo `oficina_origen_id` soy yo — esto último no depende de dónde esté el expediente ahora, así que la oficina de origen conserva el registro de "esto salió de acá" para siempre, aunque el destino ya lo haya aceptado.
    - Se reparó el único registro afectado en el contenedor de prueba (expediente que ya había sido aceptado antes de este fix, con `fecha_salida` colgada).
    - Nuevo `User::pasesPendientesCount()` + badge rojo junto a "Entrantes" en el sidebar, mostrando la cantidad de pases pendientes de la oficina del usuario (se recalcula en cada navegación, no requiere WebSockets/polling).
    - Validado extremo a extremo con Playwright: transferencia con `fecha_salida` + oficina destino cargadas a mano (reproduciendo el flujo real del usuario) → badge "1" en destino → aceptar → aparece en "Ingresados" de destino → sigue apareciendo en "Egresados" de origen.
  - ✅ **Seguimiento (2026-07-17, mismo día):** se implementaron las 2 mejoras pendientes que había dejado anotadas más arriba. Detalle:
    - **Badge en vivo:** el cálculo del contador se movió de un `@php` inline en el layout (calculado una sola vez por carga de página) a un componente Livewire propio, `App\Livewire\EntrantesBadge` (`resources/views/livewire/entrantes-badge.blade.php`), embebido en el sidebar como `<livewire:entrantes-badge />` con `wire:poll.30s` en su raíz. Se re-renderiza solo, sin depender de que el usuario navegue.
    - **"Realizar pase" separado de "Editar":** se sacó por completo la lógica de traspaso de `ExpedienteForm::update()` y del modal de edición. Ahora:
      - `modal-EditarExpediente.blade.php` perdió el campo "Oficina de Salida" — solo edita datos propios del expediente (num_exp, folio, causante, asunto, fechas). Ya no puede disparar un pase por accidente.
      - Nuevo botón "Realizar pase" en el menú de acciones (junto a Editar/Eliminar) abre `modal-RealizarPase.blade.php`, que solo pide la oficina de destino.
      - Nuevos métodos en `Expedientes`: `abrirPase($id)`, `confirmarPase()` (crea el `Pase` pendiente + actualiza `ofi_salida`/`cod_area`/`cod_oficina` para la columna "Oficina Salida", dentro de una transacción; bloquea si ya hay un pase pendiente para ese expediente) y `cancelarPase()`.
      - Efecto colateral bueno: como la creación del pase ya no vive dentro de un `update()` genérico, deja de ser posible que una edición cualquiera reabra un pase sin querer por tener `ofi_salida` con un valor viejo cargado.
    - Validado con Playwright: "Realizar pase" crea el `Pase` pendiente sin tocar `oficina_id`/`fecha_salida`; un segundo intento sobre el mismo expediente es bloqueado con un toast de error (sin duplicar el pase); "Editar" con un cambio de causante no crea ningún `Pase` ni toca `oficina_id`/`ofi_salida`.
- ✅ **1.2 + 1.3** (flujos de resolución incompletos) — resuelto para `guardar()` y `guardarPersonalizado()`; **no** se crearon las 6 plantillas Blade faltantes (ver justificación). Detalle:
  - `guardar()` (modo "plantilla con datos") y `guardarPersonalizado()` (modo editor Quill) ya persisten de verdad en `Resolucion` + `ResolucionArchivo`, igual que `guardarPlantilla()`. Se extrajo la lógica común a `persistirResolucion()` para no triplicarla.
  - `guardar()` ahora chequea `View::exists("prototipos.{$tipo}")` antes de intentar nada: si no hay plantilla para ese tipo, muestra un mensaje claro en vez de fallar a medias (este modo si depende 100% de la plantilla Blade, no tiene sentido "guardar" sin ella).
  - `ElegirResolucion` marca con un badge "Sin plantilla completa · usá 'Personalizado'" los 6 tipos sin plantilla — **no bloquea la navegación**, porque el modo "Personalizado" (editor Quill libre) es justamente el fallback pensado para esos casos y no depende de la plantilla Blade.
  - `cargarPlantillaHtml()`/`cargarPlantillaConDatos()`/`cargarPlantillaEditable()`: cuando no hay plantilla, ya no muestran el mensaje de excepción técnico crudo; el modo "Personalizado" ahora arranca con el editor vacío (antes precargaba el texto de error, que además pasaba la validación `min:20` y se podía guardar por accidente).
  - **No se crearon las 6 plantillas Blade faltantes** (`Cancelaciones`, `Resciciones`, `Transferencias`, `Transferencia-Cancelacion`, `Rectificacion`, `Otros`): son documentos legales/administrativos reales del IPV con texto y formato específico — inventar su contenido sería fabricar documentos oficiales incorrectos. Requieren las plantillas reales del organismo.
  - **3 bugs adicionales encontrados y corregidos al validar** (afectaban también a `guardarPlantilla()`, la única ruta que el audit original creía "funcional"):
    1. La tabla `resoluciones` **no tenía columna `plantilla`** — `guardarPlantilla()` ya intentaba guardar ahí pero Eloquent la descartaba en silencio (no estaba en `$fillable`, y la columna ni existía). El contenido real de la resolución **nunca se guardaba**, aunque el registro sí se creaba (cuando se creaba). Se agregó la columna vía migración y a `Resolucion::$fillable`.
    2. `pdf` y `fecha_ingreso` eran `NOT NULL` sin default en la tabla, pero ningún flujo de creación las completa (los PDFs van por `resolucion_archivos`) — cualquier alta fallaba con `SQLSTATE[HY000]: 1366`. Se hicieron `nullable`.
    3. `cod_barrio`/`cod_casa` son columnas `integer`, pero llegan como `''` (string vacío) cuando el tipo de resolución no usa manzana/lote — rompía el insert en modo estricto de MySQL. Se normaliza `''` a `null` en `persistirResolucion()`.
  - Validado extremo a extremo contra el contenedor de prueba: tipo con plantilla → se guarda con el HTML completo; tipo sin plantilla en modo "completo" → bloqueado con mensaje claro; modo "Personalizado" → se guarda igual sin depender de la plantilla.
- ✅ **2.3** (sanitización XSS del editor Quill) — resuelto. Detalle:
  - Se agregó la dependencia `mews/purifier` (wrapper de HTMLPurifier para Laravel) y se publicó `config/purifier.php`.
  - Allowlist ajustada a lo que produce Quill (negrita/cursiva/subrayado/tachado, títulos, listas, citas, enlaces, imágenes, alineación/color vía `style`) sin permitir `<script>`, atributos de evento (`onclick`, `onerror`, etc.) ni esquemas peligrosos (`javascript:`).
  - `Purifier::clean()` se aplica en `persistirResolucion()` — el único punto donde `plantilla` se escribe a la base, así que cubre los 3 flujos de guardado (`guardarPlantilla`, `guardar`, `guardarPersonalizado`) con un solo cambio.
  - Validado con casos concretos: `<script>`, `onclick`, `onerror` y `href="javascript:..."` se eliminan; negrita/cursiva/color se conservan intactos.
  - **Nota:** no se tocó el render `{!! $this->plantilla !!}` en `crear-resolucion.blade.php:776` porque es la vista previa en vivo del propio usuario mientras escribe (no hay riesgo de XSS contra terceros ahí); el vector real era el contenido ya guardado, que ahora sale sanitizado desde el punto de escritura.
- ✅ **Sección 1 (🟢) y sección 3 (refactor/código muerto)** — resuelto lo que era seguro tocar sin datos reales; ver detalle de lo que quedó afuera y por qué.
  - **1.6** `Oficinas::inputBusqueda()` (método vacío que colisionaba de nombre con la propiedad) — eliminado.
  - **1.9** `welcome.blade.php`: los 3 `@keyframes` con `...` como cuerpo ahora tienen animaciones reales (fade/pulse/float).
  - **1.10** `AppServiceProvider`: quitados el `require_once` de `fecha.php` (ya autoloaded vía `composer.json` → `autoload.files`, era doble carga) y los imports de `Livewire`/`DashboardPanel` sin usar.
  - **1.11** `app/Console/Commands/TestConexion.php` — comando de debug olvidado (oficina hardcodeada, sin manejo de errores), eliminado. `ImportarPases` ya cubre el caso real de alta de oficinas nuevas.
  - **1.8** `ResolucionForm::store()` y `ExpedienteForm::delete($expediente)` — confirmados sin ningún caller en toda la app, eliminados. (`ExpedienteForm::store()` sí se usa, no se tocó.)
  - **2.7 (resto)** `resoluciones.numero_exp`/`numero_resolucion` no tenían índice: se agregó `unique` en `numero_exp` (la app ya asume que es único vía el loop de `generarNumeroTramite()`) e índice en `numero_resolucion`.
  - **2.8 (resto)** Verificado: los 3 formularios de logout (`sidebar.blade.php` x2, `header.blade.php`) sí tienen `@csrf`. Sin cambios necesarios.
  - **3.2 + 3.10** Los ~280 líneas de `<style>` duplicadas en los 3 prototipos se extrajeron a `resources/views/prototipos/_estilos.blade.php` (`@include`d desde cada uno); de paso se eliminó el bloque CSS huérfano (`font-weight: inherit; color: inherit; }` sin selector) que estaba repetido en los 3 archivos.
  - **3.3** `resources/views/prototipos/resolucion.css` — eliminado. Al revisar su contenido se confirmó que usa nomenclatura BEM (`.resolucion__header`, etc.) que no coincide con ninguna clase de los prototipos actuales (`.documento-resolucion`, `.sheet`, `.header`...) — es un diseño anterior abandonado, no algo que faltara "conectar".
  - **3.4** `button-blue.blade.php` y `placeholder-pattern.blade.php` — sin referencias en ninguna vista, eliminados.
  - **3.5** `layouts/auth/card.blade.php` y `layouts/auth/split.blade.php` — sin referencias, eliminados (`auth/simple.blade.php` sigue siendo el layout activo).
  - **3.6** Conexiones `pgsql`/`mariadb`/`sqlsrv` en `config/database.php` — son boilerplate del starter kit de Laravel (no algo que el equipo haya agregado a mano), se documentaron con un comentario en vez de eliminarlas, para no perder la plantilla por si se necesita una conexión rápida a futuro.
  - **3.7** `Permiso::oficina()` — eliminada. Al revisar de nuevo se confirmó que el comentario "NO usarla" era **incorrecto**: `Oficina` vive en la misma conexión `mysql_admin` que `Permiso`, no en otra conexión distinta como decía el comentario. Igual se eliminó por estar sin uso en toda la app.
  - **3.8** `User::oficinaIdPara()` — **no se tocó**: al revisar, resultó estar activamente en uso (la propia sección 2.1 de este plan la usa en `AuthorizesOficina` y en 3 lugares de `Expedientes.php` como fallback de `oficinaAsignadaId()`). El hallazgo original quedó desactualizado; no es código muerto.
  - **3.9** Ya resuelto como parte de 1.4/1.5 (la reescritura de `Detalles::mount()` eliminó esa variable junto con el resto de la lógica vieja).
  - **3.11** Se agregaron factories para `Area`, `Oficina`, `Expediente`, `Resolucion` y `Pase` (a este último le faltaba también el trait `HasFactory`). Validadas contra el contenedor de prueba (tablas legacy creadas ad-hoc solo para el test, luego eliminadas).
  - **No se tocó (1.7):** `CrearResolucion::agregarArchivos()` vacío — su propio comentario ya aclara que es intencional (se usa `tempArchivos` con `wire:model` en su lugar).
- ✅ **3.1** (duplicación número→letras/formateo) — resuelto. Los 5 métodos duplicados en `CrearResolucion` (`formatearFecha`, `formatearFechaLarga`, `formatearMoneda`, `num2letras`, `convertNumberToLetters`) se eliminaron; los 3 lugares que pasaban `[$this, 'formatearFecha']` a las vistas ahora pasan el string `'formatearFecha'` (nombre de función global, callable válido en PHP), delegando directamente en `app/Helpers/fecha.php`. De paso se limpiaron los imports `Carbon` y `Cache` que quedaron sin uso. Validado: las plantillas siguen renderizando igual y las funciones globales responden igual que los métodos eliminados.
- ✅ **2.2** (concurrencia) — resuelto en el punto de mayor riesgo (`ExpedienteForm`). Detalle:
  - `store()` y `update()` pasaron de `beginTransaction()/commit()/rollBack()` manual a `DB::transaction()`.
  - `update()` ahora relee el expediente con `lockForUpdate()` **dentro** de la transacción antes de comparar `oficina_id`, en vez de confiar en el modelo ya hidratado (potencialmente desactualizado). Si dos usuarios pasan el mismo expediente a la vez, el segundo espera a que termine el primero y ve la oficina ya actualizada, en vez de generar un `Pase` con un `oficina_origen_id` incorrecto.
  - Validado contra el contenedor de prueba: el pase se sigue creando con los datos correctos tras el cambio.
  - **No se tocó** la creación/edición de `Resolucion` (`CrearResolucion`, `ResolucionForm`): no hay ahí un patrón leer-modificar-escribir sobre un mismo registro compartido entre oficinas como en `Expediente`, así que el riesgo de condición de carrera es mucho menor.

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
