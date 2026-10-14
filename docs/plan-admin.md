# Plan: pestaña Admin

Pantalla `/admin` solo para quien tenga el permiso nuevo `admin_ver` (módulo "Administración" del catálogo). Global, no por oficina.

Pestañas (una sola pantalla Livewire, `?tab=`):
1. **Resumen**: usuarios, expedientes activos, resoluciones del mes, actividad y accesos de hoy, ingresos fallidos 24 h; barras de actividad de 14 días; actividad por tipo; usuarios más activos (7 días).
2. **Auditoría**: tabla `audits` paginada con filtros (evento, usuario, rango de fechas) y detalle de antes/después.
3. **Accesos**: ingresos y ingresos fallidos (nuevos eventos `login` y `login_fallido` registrados por listeners en AppServiceProvider; nunca se guarda la contraseña).
4. **Sistema**: versiones, mailer, cola, caché, conexiones a las bases, espacio en disco, entorno/debug.

Fuera de alcance: exportar CSV, alertas, gráficos con librerías.
