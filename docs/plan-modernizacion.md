# Plan de modernización y seguridad — SiRex

Origen: auditoría técnica 2026-09-30. Rama: `feature/modernizacion-seguridad`, un commit por fase.

Base inicial: Laravel 12.4.1, Livewire 3.6.2, Volt 1.7, Vite 6, Tailwind 4.0, Pest 3. Tests: 25.
Estado final: Laravel 13.34, Livewire 4.4, Volt 1.11, Vite 8, Tailwind 4.3, Pest 4, PHP ^8.3. Tests: 70. Larastan nivel 5 sin errores. `composer audit` limpio; `npm audit` solo 1 low (quill 2.0.3, `getSemanticHTML`, no se usa).

## Fases

- [x] **1. Dependencias** — Livewire 3.8 (CVE-2025-54068), Laravel 12.69, dompdf 3.1.6, `npm audit fix`.
- [x] **2. Autorización** — oficina no editable desde "Editar", `#[Locked]` en ids, permisos en `guardar()`/`Resoluciones`, lista blanca + transacción de permisos, clave provisoria aleatoria, `Audit` en conexión por defecto.
- [x] **3. Archivos y errores** — PDFs en disco privado + `ResolucionArchivoController`, transacción al guardar, mensajes genéricos, `num2letras` corregido, pgsql_mitiv tolerante a caídas.
- [x] **4. Quitar Flux / livewire-alert / dompdf** — trait `NotificaUi`, toasts SweetAlert, tema unificado, Quill 2 por Vite (carga diferida).
- [x] **5. UI menores** — `wire:key`, tablas scrolleables, errores 403/404 sin sesión, código muerto.
- [x] **6. Calidad** — Larastan nivel 5 + baseline, CI con Larastan y audits, tests de autorización/archivos/páginas/num2letras.
- [x] **7. Mayores** — Vite 8 + plugin 3 + Tailwind 4.3, Livewire 4, Laravel 13 + Pest 4.

## Pendientes (requieren decisión o acción manual)

- **Deploy**: Livewire 4 cambia URLs a `/livewire-{hash}/…` (revisar proxy/firewall). Producción necesita PHP ≥ 8.3.
- **PDFs existentes** en `storage/app/public/resoluciones` siguen accesibles por URL pública: moverlos a `storage/app/private/resoluciones` (el controlador busca en ambos discos).
- **Calendario**: `$hasPermission = true` (TEMPORAL). Definir permiso (`calendario_admin`?) y asignarlo antes de activarlo.
- **Resoluciones sin `oficina_id`**: siguen editables por cualquier oficina con `resolucion_editar`; asignarles oficina.
- **`ImportarPases::$mapeoEO`**: claves 98 y 285 duplicadas (la segunda pisa a la primera). Definir el mapeo correcto (en baseline de Larastan).
- **`Expediente::oficina()`** depende de `$this->cod_area` (falla en eager loading); evaluar pasar a `ofi_salida`.
- **`unique('numero_exp')`** en resoluciones: ¿se permite más de una resolución por expediente?
- **Pint**: el código previo no cumple el estilo; correr `vendor/bin/pint` en un commit aparte.
- **Verificación manual en navegador**: no se pudo levantar el servidor desde la sesión; probar editor Quill, toasts, modales y tema.
