# HANDOFF — Modernización visual SiRex (glassmorphism, sin Flux UI)

**Fecha:** 2026-07-18
**Rama:** `security-review-2026-07-17` (pusheada a `origin`, working tree limpio)
**Último commit:** `d19458e` "feat(ui): eliminar Flux de Resoluciones (listado + elegir tipo)"

> El próximo agente debería poder retomar el trabajo leyendo **solo este archivo**. Si necesita el detalle línea por línea de algún commit, están todos en `git log` con mensajes largos y descriptivos.

## Qué se pidió

1. Aplicar un rediseño visual (glassmorphism + paleta institucional IPV) al sidebar y dashboard, a partir de un handoff de diseño en `C:\Users\bruno\Downloads\SiRex Modernización Visual\`.
2. Tras feedback ("no quedó como quería", "no me gusta el degradé amarillo en oscuro"), corregir y simplificar.
3. **Regla nueva y definitiva, dicha explícitamente por el usuario: "de ahora en más nada con Flux".** Cero etiquetas `<flux:...>` en todo el proyecto, ni Flux free ni Pro. Todo HTML plano + Tailwind v4 + Alpine.js.
4. Extender ese mismo patrón (sin Flux, glassmorphism, paleta IPV) al resto de las pantallas del proyecto.

## Qué se hizo (todo commiteado y pusheado)

Orden cronológico de commits en esta rama (los últimos 9, todos de esta tarea):

| Commit | Contenido |
|---|---|
| `94cea15` → `6c91e3c` | Primer intento de sidebar con Flux (glass), luego fix de un bug de binding Alpine. **Superado por commits posteriores**, lo dejo solo como referencia histórica. |
| `7c02403` | Sidebar rehecho siguiendo el 2º handoff de diseño + dashboard con más glassmorphism (aún con Flux). |
| `b5e4a56` | **Sidebar y dashboard reescritos 100% sin Flux** (HTML + Tailwind + Alpine puro), a partir del 3er handoff de diseño. |
| `c406d05` | Fix: `nav` sin `min-h-0` empujaba el perfil/switch fuera de la vista en pantallas bajas. Fix: sacar el degradé dorado del fondo en modo oscuro (pedido explícito). |
| `373ce77` | Extendido el patrón sin Flux a: shell de auth, las 6 vistas de auth, Settings completo, Oficinas. |
| `2a5ab4b` | Usuarios (lista-usuario + modal-permisos + permiso-card), 3 modales. |
| `67b0e89` | Expedientes (listado + detalle + 5 modales). |
| `d19458e` | Resoluciones (listado + elegir tipo), 2 modales. |

### Archivos modificados/creados por esta tarea (resumen)

- `resources/css/app.css` — tokens de color IPV (`ipv-blue`, `ipv-gold`, `ipv-magenta`, etc.), `--color-background` (claro/oscuro), clases `.sirex-shell` (fondo con radiales), `.sirex-glass` (sidebar), `.sirex-glass-card` (tarjetas/paneles), animación `sirex-badge-pop`.
- `resources/views/components/layouts/app/sidebar.blade.php` — sidebar completo en HTML/Alpine puro.
- `resources/views/components/layouts/app.blade.php` — quitado el wrapper `<flux:main>` redundante (el `<main>` real ya vive en sidebar.blade.php).
- `resources/views/livewire/dashboard-panel.blade.php`, `dashboard-calendar.blade.php` — sin Flux, glass intensificado.
- `resources/views/components/layouts/auth/simple.blade.php`, `auth-header.blade.php` — shell de auth con glass card + logo IPV.
- `resources/views/livewire/auth/*.blade.php` (login, forgot-password, reset-password, confirm-password, verify-email, cambiar-clave-inicial) — sin Flux.
- `resources/views/partials/settings-heading.blade.php`, `components/settings/layout.blade.php`, `livewire/settings/*.blade.php` (profile, password, appearance, delete-user-form) — sin Flux.
- `resources/views/livewire/oficinas.blade.php` — sin Flux.
- `resources/views/livewire/lista-usuario.blade.php`, `modal-permisos.blade.php`, `components/permiso-card.blade.php` — sin Flux, 3 modales reconstruidos con Alpine.
- `resources/views/livewire/Expedientes/expedientes.blade.php` + `modal-NuevoExpediente`, `modal-EditarExpediente`, `modal-RealizarPase`, `modal-ConfirmarBorrado` (compartido), `modal-filtros` (compartido), `detalles.blade.php` — sin Flux, 5 modales.
- `resources/views/livewire/resoluciones.blade.php`, `elegir-resolucion.blade.php` — sin Flux, 2 modales.
- Borrados por ser código muerto (sin ninguna referencia en el proyecto): `components/layouts/app/header.blade.php`, `components/modal.blade.php`, `components/dialog-modal.blade.php`, `resources/views/flux/navlist/group.blade.php`.

**Cero cambios de lógica de backend en todo este trabajo.** Ningún método PHP, ninguna query, ningún `wire:model`/`wire:click` fue tocado — solo el markup/CSS de las vistas.

## Descubrimientos técnicos importantes (leer antes de seguir)

### 1. `<x-input>`, `<x-button>` y `<x-badge>` eran Flux disfrazado
Estos "componentes propios" del proyecto en realidad resolvían en secreto al componente real de Flux vía un alias interno de Blade (namespace `e60dd9d2c3a62d619c9acb38f20d5aa5::input.index`, etc. — verificable inspeccionando `storage/framework/views/*.php` compilados). Un grep de `<flux:` **no los detecta**. Si en el futuro aparece algún `<x-input>`/`<x-button>`/`<x-badge>` en un archivo no migrado, es Flux aunque no lo parezca.

Comando para verificar rápido si un componente `<x-algo>` es en verdad Flux:
```bash
# renderizar la página, luego:
grep -n "AnonymousComponent::resolve.*e60dd9d2" storage/framework/views/<hash>.php
```

### 2. Hay DOS mecanismos distintos de abrir/cerrar modales en este proyecto — no asumir que son iguales
- **`ListaUsuario.php`** (Usuarios): dispatch manual `$this->dispatch('open-modal', 'modal-x')` / `close-modal`. Como es posicional, `event.detail` llega como **array**: `$event.detail[0] === 'modal-x'`.
- **`Expedientes.php` / `Resoluciones.php`**: usan el helper `$this->modal('x')->close()` (o `Flux::modal('x')->close()`, variante estática — son equivalentes). Internamente esto llama `dispatch('modal-close', name: 'x', scope: ...)`. Como son argumentos con nombre, `event.detail` llega como **objeto**: `$event.detail.name === 'modal-x'`.

Si se reusa el bridge equivocado, el modal **no se nota roto a simple vista** (el `wire:click` de la acción sí se ejecuta en el servidor), pero el modal se queda abierto para siempre después de Guardar/Eliminar/Confirmar. Antes de tocar cualquier modal nuevo, revisar el código PHP del componente (`grep -n "modal(\|dispatch(" app/Livewire/X.php`) para saber cuál de los dos patrones usa.

Patrón de apertura: en ambos casos la apertura es 100% client-side (ningún componente PHP llama a `->show()`), así que cada botón dispara `@click="show = true"` directo.

Plantilla de bridge (ejemplo real, tomado de `expedientes.blade.php`):
```html
<div x-data="{ showNuevo: false, showEditar: false }"
    x-on:modal-close.window="
        if ($event.detail.name === 'modal-exp') showNuevo = false;
        if ($event.detail.name === 'modal-editarExpediente') showEditar = false;
    ">
  <button @click="showNuevo = true">Nuevo</button>
  <div x-show="showNuevo" x-cloak x-on:keydown.escape.window="showNuevo = false"
       class="fixed inset-0 z-[100] ..." x-on:click.self="showNuevo = false">
    ...
  </div>
</div>
```

### 3. Bug preexistente encontrado (no corregido, es lógica): botón "Filtrar" en Usuarios
En `lista-usuario.blade.php` el botón "Filtrar" nunca funcionó — abre un modal `modal-filtro` cuyo template (`modal-filtros.blade.php`) nunca se incluía en esa pantalla, y además ese componente depende de `filtro.fechaDesde`/`aplicarFiltros` que `ListaUsuario` no tiene. Se dejó igual (botón inerte), documentado, no corregido por ser lógica de negocio.

### 4. Layout bug encontrado y corregido en la migración del sidebar
El handoff de diseño (los 3 que se fueron entregando) nunca ponía `display:flex` en el `<body>`, así que el `<aside>` y el `<main>` quedaban apilados verticalmente en vez de lado a lado. Se agregó `lg:flex` al `<body>` en `sidebar.blade.php`. Ninguno de los 3 handoffs había sido probado contra el proyecto real (lo decían ellos mismos en su propio README).

### 5. `nav` sin `min-h-0`
En pantallas bajas, el `<nav>` del sidebar (flex-1 dentro de un flex-col) no se achicaba por debajo de su contenido (comportamiento por defecto de flexbox: `min-height: auto`), empujando el switch de modo oscuro y el perfil fuera de la vista. Se agregó `min-h-0` para que el nav scrollee internamente y esos dos elementos queden siempre visibles.

## Tokens de diseño (ya en `resources/css/app.css`)

```
--color-ipv-blue: #00519e
--color-ipv-blue-dark: #003d7a
--color-ipv-blue-light: #8fc1ee       (para texto/iconos sobre fondo oscuro)
--color-ipv-gold: #ffb81a
--color-ipv-gold-light: #ffce63
--color-ipv-gold-hover: #ffc44d
--color-ipv-gold-ink: #7a5200         (texto sobre fondo dorado, modo claro)
--color-ipv-gold-ink-dark: #3d2600    (texto sobre fondo dorado, modo oscuro)
--color-ipv-magenta: #cf2972
--color-ipv-magenta-light: #ff9dc0
--color-ipv-ink: #1f2937              (texto principal, claro)
--color-ipv-ink-dark: #e5e9ee         (texto principal, oscuro)
--color-background: #ffffff / #0f1720 (fondo neutro sin glass, para auth/settings)
```

Clases de utilidad (`@layer components`):
- `.sirex-shell` — fondo de página. Claro: 3 radiales sutiles azul/dorado. Oscuro: **plano, sin radiales** (a pedido explícito, no le gustaba el degradé amarillo).
- `.sirex-glass` — glass del sidebar (blur 20px).
- `.sirex-glass-card` — glass de tarjetas/paneles/modales (blur 24px, más transparente, con inset highlight). Usar `rounded-[14px]` junto con esta clase para las tarjetas grandes.

Paleta de colores de categoría de tareas del calendario (blue/yellow/red/green/purple guardados en BD, remapeados visualmente a la paleta IPV): blue→ipv-blue, yellow→ipv-gold, red→ipv-magenta, green/purple→gris neutro. Los valores en base de datos **no se tocaron**, solo el mapeo visual y las etiquetas del `<select>`.

## Qué falta (para la próxima sesión)

### `resources/views/livewire/crear-resolucion.blade.php`
**782 líneas, ~197 referencias a Flux/x-input/x-button.** Es el editor de resoluciones con Quill, con 3 modalidades (modelo completo / plantilla completa / personalizado). Sustancialmente más grande que cualquier otro archivo migrado en esta tarea — no se tocó, amerita su propia sesión dedicada con la cabeza fresca en vez de apurarlo al final de esta.

Antes de empezar con este archivo, la próxima sesión debería:
1. Leer este HANDOFF completo (ya lo estás haciendo).
2. Leer `app/Livewire/CrearResolucion.php` para ver qué mecanismo de modal usa (`dispatch('open-modal'/'close-modal', ...)` estilo array, o `$this->modal()->close()` estilo objeto) — no asumir, verificar con grep como se explica arriba.
3. Revisar cómo interactúa con Quill (`public/js/quill-init.js`, `resources/css/app.css` sección `.documento-ql-editor` y `.ql-*` dark mode) — esas partes no son Flux, no tocarlas.
4. Aplicar el mismo criterio de todo este trabajo: cero cambios de lógica, solo vista/CSS.
5. Validar en vivo con Playwright las 3 modalidades (completo/plantilla/personalizado) antes de dar por terminado.
6. Correr `php artisan test` (debe seguir en 25/25) y `./vendor/bin/pint` sobre los archivos tocados.

### Limpieza menor opcional (no urgente)
`resources/views/flux/icon/*.blade.php` (book-open-text, chevrons-up-down, folder-git-2, layout-grid) — overrides de íconos custom de Flux. Puede que ya no se usen desde que el sidebar usa SVG inline directo, pero no se verificó a fondo. Revisar con `grep -rn "flux:icon\." resources/views/` antes de borrar.

## Cómo probar el entorno

- Servidor: `php artisan serve --host=127.0.0.1 --port=8888` (a veces falla el primer intento en este entorno por un tema de sandboxing de red — si falla, reintentar una vez con `dangerouslyDisableSandbox`).
- Usuario demo: `demo@sirex.local` / `demo1234` (tiene permisos de admin, oficina "Departamento Cómputos").
- `php artisan view:clear` después de cada cambio de Blade (el cache de vistas no es hot-reload).
- `./vendor/bin/pint <archivo>` antes de dar cualquier archivo por terminado.
- `php artisan test` debe dar 25/25 en todo momento — si baja de eso, algo se rompió (no debería, ya que no se tocó lógica).

## Estado de git

- Rama `security-review-2026-07-17`, ya pusheada a `origin`, working tree limpio al momento de escribir esto.
- Esta rama también contiene trabajo previo de la misma sesión larga: auditoría de seguridad, cierre de registro público + alta de usuarios por admin, fixes de test suite. Ver `implementacion_futuro.md` para el historial completo de *ese* trabajo (no relacionado a este rediseño visual).
- No se abrió Pull Request todavía. El usuario no lo pidió explícitamente.
