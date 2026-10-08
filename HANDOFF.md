# HANDOFF — Módulo RRHH (2026-10-07)

## Dónde quedamos
Rama `feature/modulo-rrhh` (sale de `feature/expedientes-ipv`, **sin push**). Spec: `docs/spec-modulo-rrhh.md`. Plan: `docs/plan-modulo-rrhh-fase1.md` (las 12 tareas hechas).

**Fase 1 (consulta) terminada:** Mi RRHH, Mi equipo, Personal + ficha (pestañas por nivel, registro de accesos, archivos), Asistencia (hoy, grilla, fichadas crudas, estado del lector), Licencias (+ adeudadas), Calendario, Organigrama (+ comando `rrhh:cargar-organigrama`), Reportes (imprimible + CSV), búsqueda Ctrl+K de empleados.
Verificado: 219 tests verdes, PHPStan limpio, prueba de humo con datos reales de mitivdb (todas las pantallas 200, ninguna supera ~1 s).

## Antes de usar en producción
1. `php artisan migrate` (tablas `rrhh_oficina_padres`, `rrhh_jefaturas`, `rrhh_accesos`, `rrhh_horarios_especiales`, `rrhh_tipos_pedibles`, `rrhh_pedidos_licencia`).
2. `npm run build` (Tailwind ahora escanea `app/Enums`).
3. `php artisan rrhh:cargar-organigrama --simular` y luego sin `--simular`; completar a mano en Organigrama lo que quede sin superior y asignar jefes.
4. Dar `rrhh_ver` a RRHH y `rrhh_sensibles` + `rrhh_horarios` solo a la jefa de RRHH (Usuarios → permisos).
5. Cargar `legajo` en los usuarios para que vean Mi RRHH.
6. RRHH valida la lista de tipos que "no fichan" (`SIREX_RRHH_TIPOS_SIN_FICHAR`, spec sección 3).
7. Pedir al admin del sistema viejo los índices de la spec (sección 8).

## Próximos pasos
- Fase 1b (hecha, apagada por defecto): pedido de licencias. Para usarla: `SIREX_RRHH_PEDIDOS=true`, dar `rrhh_confirmar_pedidos` (+ `rrhh_ver`) a quien confirma, y habilitar tipos en Licencias → "Tipos que se pueden pedir". Sin tipos, nadie puede pedir. Al confirmar, RRHH carga la licencia a mano en el sistema viejo.
- Fase 2: cargas + importador (spec 7) — plan aparte.
- Fase 3: automatizar el programa lector de relojes (el usuario lo va a pasar).
- Abrir PR draft cuando el usuario lo pida.

## Cómo correr tests
PHP solo en WSL. Copia rápida en `~/sirex-bench`: rsync del repo (excluir .env, vendor, storage, node_modules, .git) y `php artisan test` ahí. Entorno demo con datos reales de mitiv en solo lectura: SQLite en `~/sirex-demo` + variables `DB_*`/`ADMIN_DB_*`/`LEGUI_DB_*` a sqlite, `SESSION_DRIVER=file`, `CACHE_STORE=file`. Usuario `demo@sirex.test` / `demo1234` (tiene `rrhh_ver` y es jefe de Cómputos).

## Decisiones clave
- RRHH usa el catálogo `mitivdb.oficinas` (≠ `expediente_oficinas`). Jefes en `rrhh_jefaturas`, dependencia en `rrhh_oficina_padres`.
- Inasistencia registrada en mitivdb manda sobre lo calculado (82 ausente, 7 tarde).
- "Ausencias" de regímenes sin fichar → Justificado, no Licencia.
- Vacaciones: saldo calculado por SiRex, solo período vigente (año anterior). Horarios especiales en SiRex reemplazan al del viejo.
- Hora local: `App\Services\Rrhh\Hora` (zona `SIREX_RRHH_ZONA`, default Tucumán; tests en UTC).

## Agente de relojes (en curso, 2026-10-08)
Spec: `docs/spec-agente-relojes.md`. Análisis: `docs/analisis-lector-relojes.md`. Decidido: vive dentro de SiRex, doble escritura al sistema viejo, interruptores apagados por defecto.

**Hecho (con commit, tests verdes):** cliente PHP de solo lectura del protocolo ZKTeco (`app/Services/Rrhh/Relojes/ClienteZk.php`), validado contra los relojes reales `.198` y `.199` (idéntico a pyzk, registro por registro) y con 28 tests contra vectores de pyzk; comando `php artisan relojes:probar {ip}`; migración `rrhh_relojes` + `rrhh_fichadas`, modelo `RrhhReloj` y configuración `SIREX_RELOJES_*` en `config/sirex.php`.

**Falta (en orden):**
1. `EscritorMitiv` (doble escritura: `registros_reloj` con `origen='SIREX'`, dedupe por tarjeta + `fecha_hora_inmutable`, latido `CHEQUEO_ESTADO_SERVICIO` cada ≤20 min, y `CHEQUEO_RELOJES` + log `ERROR_CHECKEO` cuando un reloj falla; horas en hora LOCAL con `Hora::ahora()`). Conexión nueva `pgsql_mitiv_escritura` en `config/database.php` con variables `MITIV_WRITE_PG_*` (agregar a `.env.example`); en `MitivFalso` faltan `fecha_hora_inmutable` en `registros_reloj` y la tabla `log_operaciones_reloj`.
2. `SincronizadorRelojes` + comando `relojes:sincronizar` (candado por reloj, omitir lectura si no cambió la cantidad de registros, `insertOrIgnore` en lotes, un reloj caído no frena a los otros, desvío de hora) + `relojes:agregar` + planificador cada 5 min (solo con `SIREX_RELOJES_ACTIVO=true`).
3. `LectorRelojSirex` y tarjeta por reloj en Asistencia; tests de todo lo anterior.
4. Antes de encender la doble escritura: apagar el lector viejo, crear el usuario de PostgreSQL acotado (SELECT/INSERT en `registros_reloj`, `operaciones_reloj`, `log_operaciones_reloj` y uso de sus secuencias) y rotar las claves que aparecieron en texto plano en el programa viejo.
5. Reloj facial `.197`: caído; agregar cuando se repare.
