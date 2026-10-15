# Rediseño de Usuarios y permisos

## Problema
La ventana de permisos tenía 11 interruptores con descripción, buscador, filtro y módulos plegables dentro de un modal con scroll: abrumaba. La tabla de usuarios mostraba cinco columnas apretadas y no decía qué acceso tenía cada uno sin abrir la ventana.

## Decisiones
- **Una fila por módulo** (5 filas, sin scroll en escritorio). Cada fila: ícono, título, una línea con lo que permite el nivel actual y un **selector segmentado** `Sin acceso / Ver / Editar`. Los permisos "ver" y "editar" siguen siendo permisos separados en la base; el selector solo los fija juntos (elegir Editar activa Ver; elegir Sin acceso los quita).
- **Administración**: selector `Sin acceso / Sí`. Se bloquea con candado si quien edita no tiene `admin_ver` (el servidor ya ignoraba ese cambio en silencio; ahora la pantalla no promete algo que no se guarda).
- **RRHH**: `Sin acceso / Ver` y, con Ver activo, tres chips para datos sensibles, horarios especiales y confirmar pedidos.
- **Perfil rápido** (antes "Plantillas de rol") arriba: un clic arma varios módulos. Se quitó buscador y "Solo activos" (con 5 filas no hacen falta).
- **Lista de usuarios**: avatar con iniciales, nombre y email juntos, oficina como botón editable, **íconos de acceso por módulo** (apagado = sin acceso, contorno = ver, relleno = editar; tooltip con el detalle) y botones Permisos / Cuenta. En pantallas chicas pasa a tarjetas apiladas. Los accesos solo los ve quien puede editar usuarios (como antes la columna Permisos).
- La ventana se abre desde el servidor (`seleccionarUsuario` despacha `open-modal`), así nunca muestra por un instante los permisos del usuario anterior.

## Dónde está
- Catálogo: `app/Support/CatalogoPermisos.php` (`niveles`, `extras` y `resumen()` por módulo). `claves()`, `dependencias()`, `normalizar()` y `roles()` no cambiaron: el guardado y la auditoría son los mismos.
- Ventana: `resources/views/livewire/modal-permisos.blade.php`, `resources/js/modal-permisos.js`, estilos `.sirex-modulo` / `.sirex-segmento` en `resources/css/app.css`.
- Lista: `resources/views/livewire/lista-usuario.blade.php`, `app/Livewire/ListaUsuario.php` (una sola consulta de permisos por página).
- Tests: `tests/Feature/PermisosModalTest.php`.
