# Módulo RRHH — Fase 1 (consulta) — Plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Pantallas de consulta de RRHH (Mi RRHH, Mi equipo, Personal, Asistencia, Licencias, Calendario, Organigrama, Reportes) leyendo mitivdb en solo lectura, con alcance por empleado / jefe / RRHH / sensibles.

**Architecture:** Modelos `App\Models\Mitiv\*` de solo lectura + servicios por área (`Contracts\*` con implementación `Mitiv\*`) que reciben un `Alcance` y lo aplican. Cálculo de asistencia puro (`CalculoAsistencia`). Datos propios de SiRex (dependencia de oficinas, jefaturas, registro de accesos) en `mysql_admin`. Pantallas Livewire 4 de página completa bajo `/rrhh`.

**Tech Stack:** Laravel 13, Livewire 4 (+Volt), Pest 4, Tailwind; tests con mitivdb falso en SQLite (`tests/Support/MitivFalso.php`).

**Spec:** `docs/spec-modulo-rrhh.md` (secciones 1–6 y 8). Fases 1b (pedido de licencias) y 2 (cargas) tienen plan propio.

## Global Constraints

- Nada escribe en mitivdb: todo modelo nuevo extiende `MitivModel`.
- `password`, `clave_fiscal`, `clave_seguridad_social`, `roles` nunca se seleccionan (no figuran en ninguna lista de columnas).
- Todo RRHH usa el catálogo `mitivdb.oficinas` (no `expediente_oficinas`, no `App\Models\Oficina`).
- Inasistencia registrada en mitivdb manda sobre lo calculado: id `rrhh_tipo_ausente_id` (82) → falta, `rrhh_tipo_tarde_id` (7) → tarde, otro → justificado.
- Consultas a `registros_reloj`, `inasistencias`, `ausencias` siempre acotadas por empleado(s) y rango de fechas. Fichadas crudas: rango máximo 31 días.
- Jefe ve tipo de licencia, nunca la observación.
- Ver datos personales/sensibles de otra persona → fila en `rrhh_accesos`.
- Base vieja caída → aviso en pantalla, nunca excepción visible.
- Mobile-first; textos de UI en español; commits `feat:`/`test:` en español sin referencias a Claude.
- `./vendor/bin/pint` sobre cada archivo PHP tocado; `php artisan test` en verde al cerrar cada tarea.

## Review Focus

1. Usuario con `legajo` vacío, no numérico o inexistente en mitivdb → Mi RRHH muestra aviso, no 500 (test en Tarea 3 y 7).
2. Ciclo en la dependencia de oficinas (A→B→A cargado a mano) → `descendientes()` termina y no se cuelga (test en Tarea 2).
3. Empleado sin `card_id` o sin horario → días "franco"/"falta" sin error, grilla sigue funcionando (test en Tarea 4 y 5).
4. Jefe pidiendo por URL la ficha de alguien fuera de su alcance → 403 (test en Tarea 8).
5. mitivdb caída (excepción de conexión) en cualquier pantalla RRHH → aviso "no disponible" (test en Tarea 7).

---

### Task 1: Base de datos, config y modelos

**Files:**
- Modify: `config/sirex.php` (claves `rrhh_*`)
- Create: `app/Enums/NivelDatoRrhh.php`, `app/Enums/EstadoDia.php`
- Create: `app/Models/Mitiv/{OficinaRrhh,Categoria,Parentesco,Ausencia,TipoAusencia,Inasistencia,TipoInasistencia,RegistroReloj,Horario,Feriado,SaldoVacaciones,Familiar,FojaServicio,EmpleadoMovimiento,EmpleadoArchivo,OperacionReloj}.php`
- Modify: `app/Models/Mitiv/Empleado.php` (scope `conNivel`, relaciones `oficinaRrhh`, `categoriaTitular`)
- Create: `database/migrations/2026_10_07_090000_create_rrhh_tablas.php` (`rrhh_oficina_padres`, `rrhh_jefaturas`, `rrhh_accesos`)
- Create: `app/Models/{RrhhOficinaPadre,RrhhJefatura,RrhhAcceso}.php`
- Modify: `tests/Support/MitivFalso.php` (tablas RRHH + helpers `oficinaRrhh`, `empleadoRrhh`, `horarioSemana`, `fichada`, `ausencia`, `inasistencia`, `feriado`)
- Test: `tests/Feature/Rrhh/ModelosTest.php`

**Interfaces (Produces):**
- `NivelDatoRrhh::{Basico,Personal,Sensible}`; `->columnas(): list<string>` acumulativas; `->columnasFamiliar(): list<string>`; `->incluye(self $otro): bool`.
- `EstadoDia::{Presente,Tarde,Incompleto,Falta,Justificado,Licencia,Feriado,Franco,Pendiente,Futuro}`; `->etiqueta(): string`; `->clases(): string` (Tailwind).
- `Empleado::conNivel(NivelDatoRrhh $n)` scope.
- `MitivFalso::oficinaRrhh(string $nombre): int`, `empleadoRrhh(array $datos = []): int` (card_id y oficina por defecto), `horarioSemana(int $empleadoId, string $desde='08:00', string $hasta='14:00', int $tol=0, array $dias=[1,2,3,4,5])`, `fichada(int $cardId, string $fechaHora)`, `ausencia(int $empleadoId, string $desde, string $hasta, string $tipo='LIC. ANUAL', bool $afectaVac=true, ?string $obs=null)`, `inasistencia(int $empleadoId, string $fecha, int $tipoId)`, `feriado(string $fecha, string $tipo='Feriado nacional')`.

- [ ] Test: `conNivel(Basico)` nunca trae `clave_fiscal`/`password`/`enfermedad`; `conNivel(Sensible)` trae `enfermedad` pero no `clave_fiscal`; guardar un `Ausencia` lanza `LogicException`.
- [ ] Implementar; migrar; tests verdes; commit `feat: modelos y tablas base del módulo RRHH`.

### Task 2: Organigrama (dependencia entre oficinas RRHH)

**Files:** Create `app/Services/Rrhh/Organigrama.php`; Test `tests/Feature/Rrhh/OrganigramaTest.php`.

**Interfaces:** `padres(): array<int,int|null>`; `descendientes(array $ids): list<int>` (incluye los ids, BFS con visitados); `asignarPadre(int $oficina, ?int $padre): void` (lanza `InvalidArgumentException` si es ella misma o crea ciclo); `oficinas(): Collection<OficinaRrhh>` (habilitadas, cache 1 h); `arbol(): list<array{oficina: OficinaRrhh, nivel: int}>` (DFS, raíces = sin padre, huérfanas al final).

- [ ] Tests: descendientes recursivos; ciclo insertado a mano no cuelga; `asignarPadre` rechaza ciclo y auto-referencia; `arbol` ordena por nivel.
- [ ] Implementar; commit `feat: organigrama de oficinas RRHH`.

### Task 3: Alcance

**Files:** Create `app/Services/Rrhh/Alcance.php`, `app/Services/Rrhh/AlcanceFactory.php`; Test `tests/Feature/Rrhh/AlcanceTest.php`.

**Interfaces:** `Alcance` (readonly): `userId`, `?empleadoId`, `rrhh`, `sensibles`, `oficinasEquipo: list<int>`; `esJefe()`, `puedeEntrar()`, `oficinasVisibles(): ?list<int>` (null = todas), `nivelPara(int $empleadoId, int $oficinaId): ?NivelDatoRrhh`, `veObservacion(int $empleadoId): bool`. `AlcanceFactory::para(User $u): Alcance` (memo por user; legajo inválido o mitiv caída → `empleadoId` null).

- [ ] Tests: propio → Sensible; RRHH → Personal, con sensibles → Sensible; jefe de padre ve hija (Basico) y no la hermana (null); legajo vacío/"abc"/inexistente → null sin excepción.
- [ ] Implementar; commit `feat: alcance de RRHH por empleado, jefe y RRHH`.

### Task 4: Cálculo de asistencia

**Files:** Create `app/Services/Rrhh/CalculoAsistencia.php`, `app/Services/Rrhh/DiaAsistencia.php`; Test `tests/Unit/CalculoAsistenciaTest.php`.

**Interface:** `dia(CarbonImmutable $fecha, ?object $horario, list<CarbonImmutable> $marcas, bool $feriado, ?string $licencia, ?object $inasistencia, CarbonImmutable $ahora): DiaAsistencia`. Orden: futuro → inasistencia registrada → licencia → feriado → sin horario (franco) → sin marcas (pendiente si hoy antes del límite, si no falta) → primera marca > entrada+tolerancia (tarde, minutos) → turno tarde partido sin marca (incompleto) → presente. Tolerancia del horario si > 0, si no `rrhh_tolerancia_minutos`.

- [ ] Tests: cada estado + horario solo tarde + tolerancia 0 usa default.
- [ ] Implementar; commit `feat: cálculo de asistencia diaria`.

### Task 5: Servicios de datos (contratos + mitiv)

**Files:** Create `app/Services/Rrhh/Contracts/{Legajos,Asistencias,Licencias,Calendario,LectorReloj}.php`, `app/Services/Rrhh/Mitiv/{LegajosMitiv,AsistenciasMitiv,LicenciasMitiv,CalendarioMitiv,LectorRelojMitiv}.php`, `app/Providers/RrhhServiceProvider.php` (+ `bootstrap/providers.php`); Tests `tests/Feature/Rrhh/{Legajos,Asistencias,Licencias,LectorReloj}ServicioTest.php`.

**Interfaces:**
- `Legajos`: `propio(Alcance): ?Empleado`; `buscar(Alcance, array $f, int $porPagina = 20): LengthAwarePaginator`; `ficha(Alcance, int $empleadoId): array` (403 si fuera de alcance; registra acceso); `archivo(Alcance, int $empleadoId, int $archivoId): ?object`; `buscarRapido(Alcance, string $q, int $limite = 5): Collection`.
- `Asistencias`: `mes(Alcance, int $empleadoId, int $anio, int $mes): list<DiaAsistencia>`; `dia(Alcance, CarbonImmutable $fecha, ?int $oficinaId = null): Collection<array{empleado, oficina, dia}>`; `grilla(Alcance, int $anio, int $mes, int $oficinaId): array{dias, filas}`; `fichadas(Alcance, array $f): LengthAwarePaginator`.
- `Licencias`: `deEmpleado(Alcance, int $empleadoId, ?int $anio = null): Collection`; `inasistenciasDe(Alcance, int $empleadoId, int $anio): Collection`; `saldo(Alcance, int $empleadoId): Collection`; `buscar(Alcance, array $f): LengthAwarePaginator`; `adeudadas(Alcance, ?int $oficinaId = null): Collection`; `tipos(): Collection`.
- `Calendario`: `feriados(int $anio): Collection`; `proximo(CarbonImmutable $desde): ?Feriado`.
- `LectorReloj`: `estado(CarbonImmutable $ahora): array{ultima_fichada: ?Carbon, ultimo_chequeo: ?Carbon, alerta: bool, mensaje: string}`.

- [ ] Tests: alcance aplicado en buscar/ficha/mes (403 fuera de alcance); ficha de jefe sin DNI; observación oculta al jefe; registro de acceso; mes arma 28–31 días con licencia/feriado/falta registrada; fichadas clamp 31 días; lector en alerta.
- [ ] Implementar; commit `feat: servicios de consulta RRHH sobre el sistema viejo`.

### Task 6: Rutas, menú y permisos

**Files:** Modify `routes/web.php` (grupo `rrhh`), `resources/views/components/layouts/app/sidebar.blade.php` (grupo RRHH), `app/Livewire/ListaUsuario.php` + `resources/views/livewire/modal-permisos.blade.php` (`rrhh_ver`, `rrhh_sensibles`; el preset "solo lectura" no los otorga).

- [ ] Commit junto con la Tarea 7.

### Task 7: Mi RRHH + Calendario

**Files:** Create `app/Livewire/Rrhh/{MiRrhh,Calendario}.php`, vistas `resources/views/livewire/rrhh/{mi-rrhh,calendario}.blade.php`, componentes `resources/views/components/rrhh/{calendario-mes,estado,aviso-error,encabezado}.blade.php`; Test `tests/Feature/Rrhh/MiRrhhTest.php`.

- [ ] Tests: sin legajo → aviso; con legajo → saldo, faltas del mes, calendario; mitiv caída → aviso; calendario lista feriados.
- [ ] Commit `feat: Mi RRHH y calendario de feriados`.

### Task 8: Mi equipo + Ficha del empleado + archivos

**Files:** Create `app/Livewire/Rrhh/{MiEquipo,FichaEmpleado}.php`, `app/Http/Controllers/Rrhh/ArchivoEmpleadoController.php`, vistas; Test `tests/Feature/Rrhh/EquipoYFichaTest.php`.

- [ ] Tests: jefe ve hoy/mes de su oficina e hijas; ficha fuera de alcance → 403; jefe no ve DNI ni observación; RRHH ve personales y no sensibles; acceso registrado.
- [ ] Commit `feat: Mi equipo y ficha del empleado`.

### Task 9: Personal, Asistencia, Licencias (RRHH)

**Files:** Create `app/Livewire/Rrhh/{Personal,Asistencia,Licencias}.php`, vistas; Test `tests/Feature/Rrhh/PantallasRrhhTest.php`.

- [ ] Tests: sin `rrhh_ver` → 403; filtros del listado; tablero del día con totales; fichadas crudas; licencias filtradas; adeudadas.
- [ ] Commit `feat: pantallas de RRHH (personal, asistencia, licencias)`.

### Task 10: Organigrama (pantalla + comando)

**Files:** Create `app/Livewire/Rrhh/Organigrama.php`, vista, `app/Console/Commands/CargarOrganigramaRrhh.php`, `database/data/organigrama_rrhh.php`; Test `tests/Feature/Rrhh/OrganigramaPantallaTest.php`.

- [ ] Tests: solo `lista_usuario_editar` edita; ciclo → error visible; asignar/quitar jefe; comando aplica por id con nombre coincidente y saltea el resto sin pisar.
- [ ] Commit `feat: pantalla de organigrama y carga inicial`.

### Task 11: Reportes

**Files:** Create `app/Livewire/Rrhh/Reportes.php`, `app/Http/Controllers/Rrhh/ReporteController.php`, vistas `resources/views/rrhh/reportes/{ficha,presentismo,adeudadas,licencias}.blade.php` + layout de impresión; Test `tests/Feature/Rrhh/ReportesTest.php`.

- [ ] Tests: cada reporte HTML 200 con `rrhh_ver`, 403 sin él; CSV con BOM y `;`.
- [ ] Commit `feat: reportes de RRHH imprimibles y CSV`.

### Task 12: Búsqueda global + rendimiento + cierre

**Files:** Modify `app/Livewire/BusquedaGlobal.php`, `tests/Feature/BusquedaGlobalTest.php`, `tests/Feature/RendimientoTest.php`; Modify `docs/spec-modulo-rrhh.md` (estado), `HANDOFF.md`.

- [ ] Tests: búsqueda devuelve empleados solo en alcance; presupuesto de consultas en pantallas RRHH.
- [ ] `php artisan test` completo, `pint`, `phpstan`; commit `feat: búsqueda de empleados y cierre de fase 1 RRHH`.
