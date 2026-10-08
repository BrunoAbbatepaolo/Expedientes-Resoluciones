# Módulo RRHH — especificación de diseño

**Fecha:** 2026-10-07 · **Rama:** `feature/modulo-rrhh` · **Estado:** fase 1 (consulta) implementada; fases 1b y 2 pendientes.

## 1. Objetivo y contexto

Llevar a SiRex el RRHH del sistema viejo (MITIV, Laravel 5.2 + AngularJS en `srvipv07:/var/www/deploy/mitiv`) mejorado, como paso hacia que SiRex reemplace al sistema viejo.

- **Alcance:** legajo del empleado, licencias/ausencias, inasistencias, relojes/fichadas, horarios, feriados y días no laborables, reportes.
- **Fuera de alcance:** sueldos (otro sistema), novedades/asignaciones/retenciones del viejo, automatización del programa lector de relojes (subproyecto aparte, fase 3).
- **Mientras conviven:** SiRex solo **consulta** mitivdb (solo lectura). Las cargas se construyen pero quedan apagadas hasta el corte.

### Fases

| Fase | Contenido | Estado al terminar |
|---|---|---|
| 1. Consulta | Secciones 2–6 y 8 | Activa en producción |
| 1b. Pedido de licencias | Sección 7 bis | Implementada; se enciende con `SIREX_RRHH_PEDIDOS=true` |
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

- **Dos catálogos de oficinas en mitivdb (relevado):** `oficinas` (80, catálogo de RRHH: `empleados.oficina_id` apunta acá) y `expediente_oficinas` (514, catálogo de expedientes: `App\Models\Oficina.mitiv_oficina_id` de SiRex apunta acá). Los ids coinciden por casualidad. **Todo RRHH usa el catálogo `oficinas`.**
- **Modelos** `App\Models\Mitiv\*` (extienden `MitivModel`, que bloquea escritura; sesión `READ ONLY`): `OficinaRrhh` (`oficinas`), `Categoria`, `Ausencia`, `TipoAusencia`, `Inasistencia`, `TipoInasistencia`, `RegistroReloj`, `Horario`, `Feriado`, `SaldoVacaciones` (`vista_saldo_vacaciones`), `Familiar` (`grupo_familiares`), `FojaServicio`, `EmpleadoMovimiento`, `EmpleadoArchivo` (sin la columna `contenido` salvo al descargar), `OperacionReloj`. `Empleado` se amplía.
- **Columnas por nivel** en `Empleado`: enum `NivelDatoRrhh` (`Basico`, `Personal`, `Sensible`) con la lista explícita de columnas de cada nivel. El scope global sigue seleccionando solo las públicas; los niveles superiores se piden explícitamente y solo desde el servicio. `password`, `clave_fiscal`, `clave_seguridad_social`, `roles` no figuran en ninguna lista → nunca se leen.
- **Servicios por área** en `App\Services\Rrhh` (interfaces en `Contracts\`, implementación mitiv en `Mitiv\`): `Legajos`, `Asistencias`, `Licencias`, `Calendario`, `LectorReloj`. Cada método recibe el `Alcance` del usuario (sección 5) y lo aplica; ninguna pantalla consulta mitivdb directo. `RrhhServiceProvider` enlaza la implementación según `config('sirex.rrhh_fuente')`; la implementación `sirex` llega en fase 2.
- **`App\Services\Rrhh\CalculoAsistencia`** — puro, sin I/O: recibe fecha, horario del día, fichadas, feriado, licencia, inasistencia registrada y "ahora", y devuelve un `DiaAsistencia` (estado `presente | tarde | incompleto | falta | justificado | licencia | feriado | franco | pendiente | futuro`, marcas, minutos de tarde, motivo legible). **Una inasistencia ya registrada en mitivdb manda** (es el registro oficial: id 82 = AUSENTE → falta, id 7 = LLEGADA TARDE → tarde, otro tipo → justificado); lo demás se calcula para mostrar. Evalúa ambos turnos.
- **Datos propios de SiRex** (`mysql_admin`): `rrhh_oficina_padres` (dependencia entre oficinas RRHH), `rrhh_jefaturas` (jefe ↔ oficina RRHH), `rrhh_accesos` (registro de accesos), permisos nuevos `rrhh_ver` y `rrhh_sensibles`.
- **Config** (`config/sirex.php`): `rrhh_fuente` (`SIREX_RRHH_FUENTE`, default `mitiv`), `rrhh_tolerancia_minutos` (default 30), `rrhh_reloj_alerta_minutos` (default 30), `rrhh_pedidos` (`SIREX_RRHH_PEDIDOS`, default `false`), `rrhh_pedido_dias_atras` (default 0), `rrhh_tipo_ausente_id` (default 82), `rrhh_tipo_tarde_id` (default 7).

### Reglas descubiertas al implementar (2026-10-07)

- **"Ausencias" que no son licencias:** el sistema viejo usa `ausencias` también para regímenes que eximen de fichar por meses o años (planta justificada de obra, jerárquicos, gabinete, funcionarios, ley sindical, retiro voluntario, etc.). Esos tipos (`sirex.rrhh_tipos_sin_fichar`, env `SIREX_RRHH_TIPOS_SIN_FICHAR`) se muestran como **Justificado (no ficha)** y no como Licencia. **RRHH debe validar la lista.**
- **Contratados (CONT, id 131):** la "ausencia" CONT solo marca la vigencia del contrato; **los contratados fichan todos los días**. Se ignora (`sirex.rrhh_tipos_ignorados`) y el día se calcula por fichadas: sin marca es falta (confirmado por el usuario).
- **Regla de entrada (confirmada por el usuario):** horarios de 7 a 12 o de 7 a 14. Hasta 10 min después de la entrada = presente; de 10 a 30 min = llegada tarde; más de 30 min = falta. Se usa la tolerancia del horario del empleado (el 82 % tiene 10) o `SIREX_RRHH_TOLERANCIA` (10); el límite de falta es `SIREX_RRHH_LIMITE_FALTA` (30). Sin fichada hoy, el día queda "pendiente" hasta el límite de falta. Se compara por minuto (07:10:45 cuenta como 07:10).
- **Vacaciones (confirmado por el usuario):** la vista de saldo del sistema viejo dejó de calcular en 2022 y desde 2023 se cargan como "VAC ANUALES" (que no descuenta). SiRex calcula el saldo con las mismas reglas del viejo (antigüedad con años reconocidos → 20/25/30/35 días; días hábiles sin feriados) y muestra **solo el período vigente**: el del año anterior (en 2026 se gozan las del período 2025, que vencen al terminar 2026). Configurable: `SIREX_RRHH_VACACIONES_DESFASE`, `SIREX_RRHH_TIPOS_VACACIONES`, `SIREX_RRHH_TIPOS_ANTICIPO`.
- **Horarios especiales:** tabla SiRex `rrhh_horarios_especiales` (días, entrada/salida, turno tarde y tolerancia opcionales, vigencia, motivo, auditada). Mientras está vigente reemplaza al horario del sistema viejo en el cálculo de asistencia. Solo la modifica quien tiene el permiso `rrhh_horarios` (jefatura de RRHH); la ve quien ve al empleado (pestaña Horario de la ficha). No se superponen especiales en los mismos días.
- **Hora local:** la base vieja guarda fechas sin zona (hora de Tucumán). `App\Services\Rrhh\Hora` arma "ahora" en hora local (`SIREX_RRHH_ZONA`, default `America/Argentina/Tucuman`).
- `card_id` no es columna pública de `Empleado`: se pide con `conNivel(Basico)`.

## 4. Pantallas (fase 1)

Grupo **RRHH** en el sidebar; cada ítem se muestra según permiso. Mobile-first: grillas → tarjetas en pantallas chicas, filtros colapsables.

1. **Mi RRHH** (todo usuario cuyo `legajo` exista en mitiv)
   - Resumen: saldo de vacaciones por año, faltas y tardanzas del mes, próxima licencia, próximo feriado.
   - Mis fichadas: calendario mensual coloreado por estado; detalle del día (marcas, horario, motivo).
   - Mis licencias y faltas: listado por año.
   - Mis datos: básicos + personales propios (los sensibles propios también, sección 5).
   - Sin legajo o legajo inexistente: aviso explicativo, no error.
2. **Mi equipo** (jefes: fila en `rrhh_jefaturas`)
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
7. **Reportes** (`rrhh_ver`): ficha personal, presentismo mensual por oficina, vacaciones adeudadas, licencias por tipo. PDF = vista lista para imprimir / "Guardar como PDF" desde el navegador (mismo enfoque que la impresión de expedientes IPV); Excel = CSV con BOM y `;`. Sin dependencias nuevas.
8. **Organigrama** (`rrhh_ver` ve; `lista_usuario_editar` edita): árbol de las oficinas RRHH, campo "Depende de" por oficina y jefes asignados a cada oficina.
9. **Integraciones:** búsqueda global (Ctrl+K) incluye empleados (según alcance).

## 5. Permisos y visibilidad

| Rol | Origen | Alcance |
|---|---|---|
| Empleado | Automático por `users.legajo` = `empleados.legajo` | Solo él |
| Jefe | Fila en `rrhh_jefaturas` (user + oficina RRHH; uno o varios), asignada en Organigrama | Esa oficina y todas sus descendientes por `rrhh_oficina_padres` (recursivo, con corte de ciclos) |
| RRHH | `rrhh_ver` | Todo el personal |
| RRHH sensible | `rrhh_sensibles` (hoy: solo la jefa de RRHH) | + datos sensibles |
| Carga | `rrhh_carga` + fuente `sirex` | Pantallas de la sección 7 |
| Confirma pedidos | `rrhh_confirmar_pedidos` + `SIREX_RRHH_PEDIDOS=true` | Pedidos por confirmar (sección 7 bis) |

La jefatura se guarda sobre el catálogo RRHH (`mitivdb.oficinas`), no sobre las oficinas de SiRex: no depende del vínculo con IPV.

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

- Sobre el catálogo RRHH (`mitivdb.oficinas`, 80 filas). Tabla SiRex `rrhh_oficina_padres` (`mitiv_oficina_id` único, `padre_mitiv_oficina_id` nullable).
- Comando `rrhh:cargar-organigrama`: precarga desde el organigrama funcional (Res. 0373/2003, versión 06/06/2022) con un mapeo **por id** (`database/data/organigrama_rrhh.php`: id → [nombre esperado, id padre]); solo aplica si el nombre actual en mitivdb coincide (normalizado), no pisa valores ya cargados e imprime aplicadas / salteadas. Los nombres de jefes del organigrama **no** se usan.
- Oficinas sin equivalente en el organigrama (Inspección Médica, PROCREAR, PROMEBA, UEMS, UECELT, Comisión de Servicios, Adscriptos, Retiro Voluntario, Lic. sin goce, etc.) quedan sin superior hasta que se carguen a mano.
- Árbol de referencia: Intervención → Subintervención → Áreas (Social, Económico Financiera, Legal, Recupero y Reg. Dominial, Planeamiento, Técnica, Secretaría General) → Subdirección → Dptos → Secciones; con dependencia directa de Intervención: Cómputos, C.O.A., Prensa y Difusión, Relaciones Institucionales, U.G.R.L., Control de Gestión, Secretaría Privada.
- Edición en la pantalla **Organigrama** ("Depende de"), validando que no se formen ciclos.

## 7. Cargas e importador (fase 2, apagado)

- **Selector de fuente** `SIREX_RRHH_FUENTE=mitiv|sirex`. Con `mitiv`, todas las acciones de carga hacen `abort(404)` y no se muestran. Con `sirex`, `RrhhSirex` lee/escribe tablas propias `rrhh_*`.
- **Tablas SiRex:** `rrhh_empleados`, `rrhh_familiares`, `rrhh_fojas`, `rrhh_movimientos`, `rrhh_historial`, `rrhh_archivos`, `rrhh_horarios`, `rrhh_plantillas_horario`, `rrhh_feriados`, `rrhh_tipos_ausencia`, `rrhh_ausencias`, `rrhh_tipos_inasistencia`, `rrhh_inasistencias`. Con `mitiv_id` para trazabilidad. Auditadas con el trait `Auditable` existente.
- **`php artisan rrhh:importar [--dry-run]`:** copia mitiv → SiRex, idempotente (upsert por `mitiv_id`), reporta cantidades e inconsistencias. Excluye columnas del nivel "Nunca".
- **Pantallas de carga:** alta/edición de legajo, familia, foja, archivos y foto; pase a pasivo/activo; licencia individual y masiva (validación de saldo de vacaciones y superposición); justificar/anular inasistencia (motivo obligatorio); horarios y plantillas (aplicación masiva); feriados; tipos de ausencia e inasistencia; tolerancias.
- **Proceso `rrhh:procesar-asistencia`** (programado cada hora): genera faltas/tardanzas usando `CalculoAsistencia`. No corre con fuente `mitiv` ni si el lector de relojes está caído. Las fichadas se siguen leyendo de `registros_reloj` hasta la fase 3.

## 7 bis. Pedido de licencias desde Mi RRHH

No escribe en mitivdb ni choca con el viejo (un pedido no es una licencia hasta que RRHH lo confirma), así que funciona con cualquier fuente. Tiene su propio interruptor `SIREX_RRHH_PEDIDOS` (default `false`); apagado → pantallas ocultas y acciones `abort(404)`.

- **Tabla SiRex `rrhh_pedidos_licencia`:** user_id, empleado (id mitiv), tipo de ausencia (id de la fuente activa), desde, hasta, días hábiles, observación, adjunto opcional (certificado, mismo almacenamiento privado que archivos de resoluciones), estado, y por cada paso: quién, cuándo, motivo de rechazo. Auditada con `Auditable`.
- **Estados:** `pendiente_jefe` → `aprobado_jefe` | `rechazado_jefe` → `confirmado` | `rechazado_rrhh`. El empleado puede `cancelado` mientras esté pendiente de jefe.
- **Quién aprueba:** cualquier usuario cuyo alcance de jefe (sección 5) incluya la oficina del empleado. Sin jefe asignado → pasa directo a RRHH. Nadie aprueba su propio pedido: el de un jefe lo aprueba un jefe de una oficina superior o, si no hay, RRHH.
- **Confirmación (permiso nuevo `rrhh_confirmar_pedidos`):**
  - Fuente `mitiv`: RRHH carga la licencia en el sistema viejo y marca el pedido "Confirmado — cargado en sistema viejo".
  - Fuente `sirex`: confirmar crea la `rrhh_ausencias` automáticamente.
- **Validaciones al pedir (implementado):** hasta ≥ desde; al menos 1 día hábil (sin fines de semana ni feriados; mismo cálculo que las vacaciones); no superpone licencias cargadas en el sistema viejo ni pedidos en trámite — **los regímenes que duran meses o años (`rrhh_tipos_sin_fichar` y `rrhh_tipos_ignorados`, ej. CONT) no cuentan como superposición**, si no los contratados nunca podrían pedir; sin fecha pasada salvo en los tipos marcados "permite fecha pasada" (hasta `SIREX_RRHH_PEDIDO_DIAS_RETRO`, 30 días); si es de vacaciones, no supera los días pendientes del período vigente **descontando los pedidos de vacaciones en trámite**; si el tipo exige certificado, adjunto obligatorio (PDF/JPG/PNG, 5 MB, validado por contenido).
- **Tipos pedibles:** tabla SiRex `rrhh_tipos_pedibles` (id de tipo + permite fecha pasada + exige certificado), administrada desde Licencias → "Tipos que se pueden pedir" por quien tiene `rrhh_confirmar_pedidos`. Sin tipos habilitados, nadie puede pedir. Los tipos del viejo no se tocan.
- **Permisos:** confirmar requiere `rrhh_confirmar_pedidos` **y** `rrhh_ver` (la pantalla Licencias es de RRHH). El adjunto lo ven el empleado, sus jefes y RRHH.
- **Concurrencia:** cada cambio de estado exige que el pedido siga en el estado esperado (dos personas no pueden procesarlo a la vez).
- **Pantallas:** Mi RRHH → "Pedir licencia" (muestra días hábiles y saldo resultante antes de enviar) y "Mis pedidos". Mi equipo → "Pedidos por aprobar". Licencias (RRHH) → "Pedidos por confirmar".
- **Avisos:** contador en el sidebar (como pases pendientes) para jefe y RRHH; el empleado ve el estado en Mis pedidos. Email queda para después.
- **Pruebas:** circuito completo de estados; jefe fuera de alcance → 403; auto-aprobación imposible; validaciones de superposición, saldo y feriados; con interruptor apagado → 404.

## 8. Errores, rendimiento y pruebas

- mitivdb no disponible: aviso "datos del sistema viejo no disponibles" (patrón `rescue` de Expedientes IPV); la página no se rompe.
- Consultas a `registros_reloj` e `inasistencias` siempre filtradas por empleado(s) y rango de fechas. Índices relevados: `registros_reloj` tiene `(card_id, fecha_hora)`; **`inasistencias`, `ausencias` y `horarios` solo tienen la PK**. Pedido para el administrador del sistema viejo (SiRex no los crea): `CREATE INDEX ON inasistencias (empleado_id, fecha); CREATE INDEX ON ausencias (empleado_id, desde); CREATE INDEX ON horarios (empleado_id);`. Mientras tanto, tableros del día cacheados 60 s.
- Pruebas (Pest/PHPUnit, conexión mitiv en SQLite como en los tests actuales):
  - Alcance: empleado, jefe con descendientes, RRHH, sensibles; acceso a ficha ajena → 403.
  - Ninguna respuesta contiene columnas del nivel "Nunca".
  - `CalculoAsistencia`: feriado, licencia, tarde, falta, turno doble, franco, día futuro.
  - Fuente `mitiv`: acciones de carga → 404.
  - `rrhh:importar` dos veces → sin duplicados.
  - `RendimientoTest` cubre las pantallas nuevas (sin N+1).
  - Registro de accesos se inserta al ver datos personales/sensibles ajenos.
