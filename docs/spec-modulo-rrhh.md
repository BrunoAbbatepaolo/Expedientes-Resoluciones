# Módulo RRHH — especificación de diseño

**Fecha:** 2026-10-07 · **Rama:** `feature/modulo-rrhh` · **Estado:** diseño aprobado en chat, pendiente revisión de este documento.

## 1. Objetivo y contexto

Llevar a SiRex el RRHH del sistema viejo (MITIV, Laravel 5.2 + AngularJS en `srvipv07:/var/www/deploy/mitiv`) mejorado, como paso hacia que SiRex reemplace al sistema viejo.

- **Alcance:** legajo del empleado, licencias/ausencias, inasistencias, relojes/fichadas, horarios, feriados y días no laborables, reportes.
- **Fuera de alcance:** sueldos (otro sistema), novedades/asignaciones/retenciones del viejo, automatización del programa lector de relojes (subproyecto aparte, fase 3).
- **Mientras conviven:** SiRex solo **consulta** mitivdb (solo lectura). Las cargas se construyen pero quedan apagadas hasta el corte.

### Fases

| Fase | Contenido | Estado al terminar |
|---|---|---|
| 1. Consulta | Secciones 2–6 y 8 | Activa en producción |
| 2. Cargas + importador | Sección 7 | Construida, apagada (`SIREX_RRHH_FUENTE=mitiv`) |
| 3. Lector de relojes | Automatizar el programa que llena `registros_reloj` | Fuera de esta spec |

## 2. Datos del sistema viejo (relevado 2026-10-07)

| Tabla mitivdb | Filas | Uso |
|---|---|---|
| `empleados` | 460 activos | Legajo (~90 columnas, incluye datos sensibles) |
| `registros_reloj` | 1,49 M | Fichadas (`card_id`, `fecha_hora`, `nombre_reloj`, `origen`) — se cruza con `empleados.card_id` |
| `inasistencias` / `tipo_inasistencias` | 351 k | Faltas y llegadas tarde (`afecta_presentismo`) |
| `ausencias` / `tipo_ausencias` | 89 k | Licencias (`desde`, `hasta`, `afecta_vacaciones`) |
| `horarios` / `plantillas_horarios` | 3,7 k | Por empleado y día (1–7): turno mañana/tarde + tolerancias |
| `feriados` / `tipo_feriados` | 297 | Rango `desde`–`hasta` |
| `vista_saldo_vacaciones` | — | Saldo de vacaciones por empleado/año |
| `grupo_familiar`, `fojas_servicio`, `empleado_movimientos`, `empleado_historial`, `empleado_archivos` | — | Pestañas de la ficha |
| `operaciones_reloj` | — | `CHEQUEO_ESTADO_SERVICIO`: latido del lector de relojes |
| `jerarquias` / `oficinas` | 88 / 80 | **No usable como árbol**: las 80 oficinas apuntan a `jerarquia_id = 1` |

Lógica del viejo a reemplazar: `RegistroRelojController::sincronizar_relojes()` (cron `reloj:procesar_eventos` cada hora). Defectos: códigos fijos (82 = falta, 7 = llegada tarde), tolerancia mínima fija de 30 min, solo evalúa turno mañana, `cumplioHorario()` y tarde incompletos.

## 3. Arquitectura

**Lectura en vivo** de mitivdb (opción A), mismo patrón que Expedientes IPV:

- **Modelos** `App\Models\Mitiv\*` (extienden `MitivModel`, que bloquea escritura; sesión `READ ONLY`): `Ausencia`, `TipoAusencia`, `Inasistencia`, `TipoInasistencia`, `RegistroReloj`, `Horario`, `Feriado`, `SaldoVacaciones`, `GrupoFamiliar`, `FojaServicio`, `EmpleadoMovimiento`, `EmpleadoHistorial`, `EmpleadoArchivo`, `OperacionReloj`. `Empleado` se amplía.
- **Columnas por nivel** en `Empleado`: listas explícitas `COLUMNAS_BASICAS`, `COLUMNAS_PERSONALES`, `COLUMNAS_SENSIBLES`. El scope global sigue seleccionando solo básicas; los niveles superiores se piden explícitamente y solo desde el servicio. `password`, `clave_fiscal`, `clave_seguridad_social`, `roles` no figuran en ninguna lista → nunca se leen.
- **`App\Services\Rrhh\Rrhh`** (interfaz) — única puerta a los datos. Todo método recibe el `User` que consulta y aplica el alcance (sección 5). Implementaciones: `RrhhMitiv` (fase 1) y `RrhhSirex` (fase 2). Se elige por `config('sirex.rrhh_fuente')`.
- **`App\Services\Rrhh\CalculoAsistencia`** — puro, sin I/O: recibe horario del día, fichadas, feriado, ausencia e inasistencias registradas y devuelve un `DiaAsistencia` (estado: `presente | tarde | falta | licencia | feriado | franco | sin_horario | futuro`, marcas, minutos de tarde por turno, motivo legible). Evalúa ambos turnos. Tolerancias desde config.
- **Datos propios de SiRex:** `oficinas.oficina_padre_id` (nullable, FK a `oficinas`), permisos nuevos, tabla `rrhh_accesos` (registro de accesos, sección 5).
- **Config** (`config/sirex.php`): `rrhh_fuente` (`SIREX_RRHH_FUENTE`, default `mitiv`), `rrhh_tolerancia_minutos` (default 30), `rrhh_reloj_alerta_minutos` (default 30).

## 4. Pantallas (fase 1)

Grupo **RRHH** en el sidebar; cada ítem se muestra según permiso. Mobile-first: grillas → tarjetas en pantallas chicas, filtros colapsables.

1. **Mi RRHH** (todo usuario cuyo `legajo` exista en mitiv)
   - Resumen: saldo de vacaciones por año, faltas y tardanzas del mes, próxima licencia, próximo feriado.
   - Mis fichadas: calendario mensual coloreado por estado; detalle del día (marcas, horario, motivo).
   - Mis licencias y faltas: listado por año.
   - Mis datos: básicos + personales propios (los sensibles propios también, sección 5).
   - Sin legajo o legajo inexistente: aviso explicativo, no error.
2. **Mi equipo** (`rrhh_equipo`)
   - Hoy: presentes / tarde / ausentes / licencia por oficina (refresco con `wire:poll`, 60 s).
   - Mes: grilla empleados × días con colores.
   - Mi gente: listado → ficha en nivel básico + asistencia + licencias.
3. **Personal** (`rrhh_ver`)
   - Listado: búsqueda por apellido/nombre, legajo, DNI; filtros oficina, situación laboral, categoría, activos/pasivos; paginado.
   - Ficha con pestañas: Datos · Familia · Trayectoria (foja, movimientos, historial) · Asistencia · Licencias y saldo · Inasistencias · Archivos.
4. **Asistencia** (`rrhh_ver`)
   - Tablero del día del instituto con totales por área.
   - Grilla mensual por oficina/área.
   - Consulta de fichadas crudas (empleado, reloj, rango de fechas; rango máximo 31 días).
   - Estado del lector: última fichada y último `CHEQUEO_ESTADO_SERVICIO`; alerta si supera `rrhh_reloj_alerta_minutos` en horario laboral (lun–vie 7–15 h, no feriado).
5. **Licencias** (`rrhh_ver`): listado filtrable (tipo, fechas, oficina, empleado) y vacaciones adeudadas por empleado/año.
6. **Calendario** (todos): feriados y días no laborables del año.
7. **Reportes** (`rrhh_ver`): ficha personal (PDF), presentismo mensual por oficina, vacaciones adeudadas, licencias por tipo. Exportación Excel y PDF.
8. **Integraciones:** búsqueda global (Ctrl+K) incluye empleados (según alcance); **Oficinas** suma el campo "Depende de" y vista en árbol.

## 5. Permisos y visibilidad

| Rol | Origen | Alcance |
|---|---|---|
| Empleado | Automático por `users.legajo` = `empleados.legajo` | Solo él |
| Jefe | Permiso `rrhh_equipo` + oficina (uno o varios) | Esa oficina y todas sus descendientes por `oficina_padre_id` (recursivo, con corte de ciclos) |
| RRHH | `rrhh_ver` | Todo el personal |
| RRHH sensible | `rrhh_sensibles` (hoy: solo la jefa de RRHH) | + datos sensibles |
| Carga | `rrhh_carga` + fuente `sirex` | Pantallas de la sección 7 |

Alcance de jefe: las oficinas SiRex se traducen a oficinas mitiv por `oficinas.mitiv_oficina_id`; oficinas sin vínculo no aportan empleados (se avisa en Mi equipo).

| Nivel de datos | Campos | Ven |
|---|---|---|
| Básico | apellido, nombre, legajo, oficina, función, categorías, situación laboral, fecha de ingreso, horario, email | Él mismo, jefe, RRHH |
| Personal | DNI, CUIL, domicilio, teléfonos, nacimiento, estado civil, nacionalidad, familia, obra social, seguro, contacto de emergencia | Él mismo, RRHH |
| Sensible | discapacidad, enfermedad, tratamiento, datos judiciales del grupo familiar, cuenta bancaria | Él mismo, `rrhh_sensibles` |
| Nunca | password, clave_fiscal, clave_seguridad_social, roles | Nadie |

- Licencias para el jefe: ve el **tipo**, no la **observación**.
- **Registro de accesos:** abrir datos personales o sensibles de *otra* persona inserta en `rrhh_accesos` (user_id, empleado_id mitiv, nivel, fecha). Consultable por `rrhh_sensibles`.
- **Hallazgo:** mitivdb guarda `clave_fiscal` y `clave_seguridad_social` en `empleados`. Recomendación al área de sistemas: verificar contenido y vaciar si son claves reales. SiRex no las lee ni importa.

## 6. Dependencia de oficinas

- Migración: `oficinas.oficina_padre_id`.
- Seeder/comando `rrhh:cargar-organigrama`: precarga desde el organigrama funcional (Res. 0373/2003, versión 06/06/2022) emparejando por nombre normalizado. Imprime emparejadas / no emparejadas; no pisa valores ya cargados. Los nombres de jefes del organigrama **no** se usan.
- Árbol de referencia: Intervención → Subintervención → Áreas (Social, Económico Financiera, Legal, Recupero y Reg. Dominial, Planeamiento, Técnica, Secretaría General) → Subdirección → Dptos → Secciones; con dependencia directa de Intervención: Cómputos, C.O.A., Prensa y Difusión, Relaciones Institucionales, U.G.R.L., Control de Gestión, Secretaría Privada.
- Edición en **Oficinas** ("Depende de"), validando que no se formen ciclos.

## 7. Cargas e importador (fase 2, apagado)

- **Selector de fuente** `SIREX_RRHH_FUENTE=mitiv|sirex`. Con `mitiv`, todas las acciones de carga hacen `abort(404)` y no se muestran. Con `sirex`, `RrhhSirex` lee/escribe tablas propias `rrhh_*`.
- **Tablas SiRex:** `rrhh_empleados`, `rrhh_familiares`, `rrhh_fojas`, `rrhh_movimientos`, `rrhh_historial`, `rrhh_archivos`, `rrhh_horarios`, `rrhh_plantillas_horario`, `rrhh_feriados`, `rrhh_tipos_ausencia`, `rrhh_ausencias`, `rrhh_tipos_inasistencia`, `rrhh_inasistencias`. Con `mitiv_id` para trazabilidad. Auditadas con el trait `Auditable` existente.
- **`php artisan rrhh:importar [--dry-run]`:** copia mitiv → SiRex, idempotente (upsert por `mitiv_id`), reporta cantidades e inconsistencias. Excluye columnas del nivel "Nunca".
- **Pantallas de carga:** alta/edición de legajo, familia, foja, archivos y foto; pase a pasivo/activo; licencia individual y masiva (validación de saldo de vacaciones y superposición); justificar/anular inasistencia (motivo obligatorio); horarios y plantillas (aplicación masiva); feriados; tipos de ausencia e inasistencia; tolerancias.
- **Proceso `rrhh:procesar-asistencia`** (programado cada hora): genera faltas/tardanzas usando `CalculoAsistencia`. No corre con fuente `mitiv` ni si el lector de relojes está caído. Las fichadas se siguen leyendo de `registros_reloj` hasta la fase 3.
- **Anotado para después (no en esta spec):** pedido de licencia por el empleado con aprobación de jefe y RRHH.

## 8. Errores, rendimiento y pruebas

- mitivdb no disponible: aviso "datos del sistema viejo no disponibles" (patrón `rescue` de Expedientes IPV); la página no se rompe.
- Consultas a `registros_reloj` e `inasistencias` siempre filtradas por empleado(s) y rango de fechas. Antes de implementar: relevar índices existentes en esas tablas (solo lectura); si faltan, documentar el pedido de índice para el viejo en lugar de crearlo.
- Pruebas (Pest/PHPUnit, conexión mitiv en SQLite como en los tests actuales):
  - Alcance: empleado, jefe con descendientes, RRHH, sensibles; acceso a ficha ajena → 403.
  - Ninguna respuesta contiene columnas del nivel "Nunca".
  - `CalculoAsistencia`: feriado, licencia, tarde, falta, turno doble, franco, día futuro.
  - Fuente `mitiv`: acciones de carga → 404.
  - `rrhh:importar` dos veces → sin duplicados.
  - `RendimientoTest` cubre las pantallas nuevas (sin N+1).
  - Registro de accesos se inserta al ver datos personales/sensibles ajenos.
