# HANDOFF — Módulo RRHH, fase 1 (2026-10-07)

## Dónde quedamos
Rama `feature/modulo-rrhh` (sale de `feature/expedientes-ipv`, sin push). Spec: `docs/spec-modulo-rrhh.md`. Plan: `docs/plan-modulo-rrhh-fase1.md`.

Hechas (commits, tests en verde):
- Tarea 1 modelos/tablas/config · 2 Organigrama · 3 Alcance · 4 CalculoAsistencia · 5 servicios (Contracts + Mitiv)
- Tareas 6–7: rutas `/rrhh`, menú RRHH (`MenuRrhh`), permisos `rrhh_ver`/`rrhh_sensibles` en Usuarios, Mi RRHH, Calendario
- Tarea 8: Mi equipo, Ficha del empleado, descarga de archivos

## Próximos pasos (plan, en orden)
- Tarea 9: pantallas Personal, Asistencia (tablero día con `x-rrhh.hoy`, grilla `x-rrhh.grilla`, fichadas crudas, estado del lector), Licencias (+ adeudadas). Agregar rutas `rrhh.personal`, `rrhh.asistencia`, `rrhh.licencias` (el menú las muestra solo si existe la ruta).
- Tarea 10: pantalla Organigrama (editar "Depende de" + jefes; al cambiar jefaturas llamar `MenuRrhh::olvidarJefe`) y comando `rrhh:cargar-organigrama` con `database/data/organigrama_rrhh.php` (mapeo por id armado del PDF; ids y nombres de las 80 oficinas relevados en esta sesión).
- Tarea 11: reportes imprimibles + CSV.
- Tarea 12: búsqueda global de empleados, RendimientoTest de pantallas RRHH, phpstan, cierre.
- Pendiente aparte: migración en producción (`php artisan migrate`), `npm run build` (Tailwind ahora escanea `app/Enums`).

## Cómo correr tests
PHP solo está en WSL. Copia rápida en `~/sirex-bench`: rsync del repo y `php artisan test` ahí (script `t.sh` de la sesión; se puede rehacer con rsync excluyendo .env/vendor/storage). Base: 102 tests; ahora +39 de RRHH.

## Decisiones clave
- RRHH usa el catálogo `mitivdb.oficinas` (≠ `expediente_oficinas`). Jefes en `rrhh_jefaturas`, dependencia en `rrhh_oficina_padres`.
- Inasistencia registrada en mitivdb manda sobre lo calculado (82 ausente, 7 tarde).
- Hora local: `App\Services\Rrhh\Hora` (zona `SIREX_RRHH_ZONA`, default Tucumán; tests en UTC).
