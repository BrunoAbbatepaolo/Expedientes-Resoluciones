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

## Agente de relojes (implementado, apagado por defecto — 2026-10-08)
Spec: `docs/spec-agente-relojes.md` (sección 5 = puesta en marcha paso a paso). Análisis: `docs/analisis-lector-relojes.md`.

**Hecho (tests verdes, 272):** cliente PHP de solo lectura del protocolo ZKTeco validado contra los relojes reales `.198` y `.199`; comandos `relojes:probar`, `relojes:agregar`, `relojes:sincronizar`; planificador cada 5 min (solo con `SIREX_RELOJES_ACTIVO=true`); tablas `rrhh_relojes` y `rrhh_fichadas`; doble escritura al sistema viejo con conexión aparte `pgsql_mitiv_escritura` (apagada: `SIREX_RELOJES_ESCRIBIR_MITIV`); estado por reloj y alerta en Asistencia. Probado de punta a punta con los relojes reales en el entorno demo (la doble escritura solo contra una base simulada: nunca se escribió en la base real).

**Pendiente / decisiones del usuario:**
1. Dar de alta los relojes y encender el agente en el servidor de SiRex (necesita el planificador de Laravel corriendo).
2. Para la doble escritura: crear el usuario de PostgreSQL acotado, apagar el lector viejo y recién entonces encenderla (ver spec, sección 5).
3. Rotar las claves que estaban en texto plano en el programa viejo.
4. Reloj facial `.197`: caído; validar el protocolo con él cuando se repare. `.223` está dado de baja en los hechos.
5. Fase 2: que las pantallas lean `rrhh_fichadas` (`fuente=sirex`) y borrado de memoria de los relojes (`BORRAR_REGISTROS`), hoy fuera de alcance.
