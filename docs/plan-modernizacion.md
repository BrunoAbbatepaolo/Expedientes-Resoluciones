# Plan de modernización y seguridad — SiRex

Origen: auditoría técnica 2026-09-30 (solo lectura). Rama de trabajo: `feature/modernizacion-seguridad`, un commit por fase.

Base: Laravel 12.4.1, Livewire 3.6.2, Volt 1.7, PHP 8.3 (WSL), Vite 6, Tailwind 4. Tests: 25/25 OK.

## Fases

- [ ] **1. Dependencias (sin saltar mayores):** Livewire 3.8.x (CVE-2025-54068 RCE), Laravel 12.69.x, dompdf 3.1.6, `composer update` general, `npm audit fix`, quitar `@rollup/rollup-linux-x64-gnu` fijado en 4.9.5.
- [ ] **2. Autorización:**
  - `ExpedienteForm::update()` no debe guardar `oficina_id`/`cod_area`/`cod_oficina`/`fecha_salida` enviados por el cliente; `$campos` no público.
  - `#[Locked]` en IDs públicos (`DashboardPanel::$oficinaId`, `Detalles::$id`, `Resoluciones::$resolucionIdParaBorrar`, `Expedientes::$expedienteId/$expedientePaseId`, `ListaUsuario::$editingUserId`, `CrearResolucion::$tipo`).
  - Permisos en `Resoluciones::mount()`, `Expedientes::guardar()`, `Oficinas`, calendario (`$hasPermission = true` temporal).
  - Resoluciones con `oficina_id` null: impedir creación sin oficina.
  - `ListaUsuario`: clave inicial aleatoria, quitar `togglePermiso()`, lista blanca + transacción en `guardarPermisos`.
- [ ] **3. Archivos y errores:** PDFs a disco privado + ruta autorizada, limpieza de `temp/` y sesión, transacción al guardar resolución, `manzana`/`lote` integer, mensajes de error genéricos (log del detalle), revisar `$resultado` en store/update, try/catch en `pgsql_mitiv`, fix `num2letras`.
- [ ] **4. Quitar Flux y livewire-alert:** reemplazar `$this->modal()`/`Flux::modal/toast`, unificar modo oscuro (clave `theme`, script en `<head>`), página Apariencia, Quill desde npm, SweetAlert único, quitar `livewire/flux`, `livewire-alert`, `laravel-dompdf` (sin uso).
- [ ] **5. UI menores:** `wire:key` en loops, tablas `overflow-x-auto`, modales responsive, `routeIs('listausuarios')`, páginas de error para invitados, código muerto.
- [ ] **6. Calidad:** Larastan, tests de flujos principales (expedientes, pases, permisos, `num2letras`), CI GitHub Actions.
- [ ] **7. Mayores:** Laravel 13, Livewire 4, Vite 8 + laravel-vite-plugin 3, Pest 4 — solo con tests de fase 6 en verde.

## Decisiones pendientes del usuario
- `unique('numero_exp')` en resoluciones: ¿se permite más de una resolución por expediente?
- Probar migraciones pendientes en copia de la base, nunca en producción.
