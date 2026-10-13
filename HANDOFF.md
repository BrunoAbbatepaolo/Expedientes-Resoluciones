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

## Servidor demo y agente de relojes
Un comando: `wsl -d Ubuntu -- bash /mnt/c/Users/Bruno/Documents/Claude/Expedientes-Resoluciones/scripts/demo-wsl.sh up` (`down|status|sync`). Detalle, variables, lentitud y botón «Sincronizar ahora»: `docs/entorno-demo-wsl.md`.

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

## Seguridad (2026-10-09)
- Cabeceras en todas las respuestas web: `App\Http\Middleware\CabecerasDeSeguridad` (X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, COOP, CSP solo con frame-ancestors/base-uri/form-action/object-src; HSTS solo con HTTPS). Sin `script-src` a propósito: Livewire/Alpine usan inline.
- Tope general de pedidos: RateLimiter `global` (AppServiceProvider), `SIREX_LIMITE_PETICIONES` (300/min por usuario; 1/3 por IP sin sesión). Recuperar clave: 5 por hora por correo+IP.
- Contraseñas: `Password::defaults()` = 10 caracteres, mayúsculas, minúsculas y números (símbolo recomendado, no exigido).
- Auditoría en `audits` (`Audit::registrar`): `permisos` (agregados/quitados), `usuario_creado`, `oficina`, `clave_cambiada`, `clave_inicial_cambiada`. Todavía no hay pantalla para verla.
- `concurrently` pasó a devDependencies; `npm audit fix` aplicado (queda 1 aviso bajo de Quill que no aplica: no se usa su exportación HTML).
- Pendiente en producción: `APP_DEBUG=false`, HTTPS + `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, rotar claves expuestas, usuario PostgreSQL acotado, `storage:link` no debe exponer PDFs viejos. Sin 2FA.

## Próximo paso (acordado, para otro día): pantalla de Auditoría
- Hoy `audits` ya guarda: `permisos`, `usuario_creado`, `oficina`, `clave_cambiada`, `clave_inicial_cambiada` (vía `Audit::registrar`) y, por el trait `Auditable`, created/updated/deleted de Expediente, RrhhHorarioEspecial y RrhhPedidoLicencia. Falta la pantalla para verlos.
- Ideas a validar con Bruno antes de armarla: quién puede verla (¿`lista_usuario_editar` o un permiso nuevo?), filtros (usuario, evento, fechas, texto), detalle legible del antes/después (permisos agregados/quitados con nombres del catálogo), paginación, exportar, y no mostrar nunca claves. Evaluar auditar también inicios de sesión fallidos y cambios de permisos hechos por consola.
- Usar el diseño actual (sirex-glass-card, x-rrhh.estado/chips, x-vacio, filas con `divide-y`) y agregar test de autorización y de consultas (RendimientoTest).
