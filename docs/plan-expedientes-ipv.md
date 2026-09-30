# Plan: Expedientes de todo el IPV + mejoras — SiRex

Fecha: 2026-09-30. Estado: **aprobado** (pases: el nuevo solo consulta; usuarios: empleados activos; extras: todos).

## Qué se relevó (solo lectura)

**Sistema viejo** (`srvipv07`, `/var/www/deploy/mitiv`): Laravel 5.2 como API. Base `mitivdb` (PostgreSQL). **Sigue en uso**: en los últimos 30 días hubo 1.032 expedientes nuevos y 10.037 pases. Por eso el sistema nuevo **solo lee** de mitiv; nunca le escribe.

| Tabla mitiv | Filas | Uso |
|---|---|---|
| `expedientes` | ~450 mil | número (único), fecha/hora, folio, tomo, asunto, `asunto_id`→`tipo_asuntos`, `causante_id`→`causantes`, observación, resolución |
| `expediente_pases` | ~2,2 M | historial: fecha_hora, `oficina_id`→`expediente_oficinas` (destino), `empleado_id`→`empleados` (quién), folios, observación. Promedio 7,4 pases por expediente (máximo 194). Índice por (expediente_id, fecha_hora) |
| `expediente_oficinas` | 514 | destinos de pase (áreas, departamentos, externos) |
| `expediente_adjuntos` / `expediente_relacionados` | 8 mil / 218 | expedientes agregados y relacionados |
| `causantes` + `tipo_causantes` | 105 mil | quién inicia el trámite (empleados, constructoras, bancos, municipios…) |
| `empleados` | 991 (461 activos) | legajo, nombre, oficina, situación laboral, `es_usuario`, email. **También tiene datos sensibles** (claves, CUIL, salud): solo se leerían columnas mínimas |
| `empleado_expediente_oficinas` | 6.639 | qué empleado pertenece a qué oficina de expedientes |

Empleados activos: 346 permanentes, 92 con contrato de locación de servicios y 23 de otras situaciones. Son los ~300 a 460 reales, contra una base de "personas" mucho más grande.

**Base `admin`** (usuarios, permisos y registro de expedientes del sistema nuevo): **no accesible desde esta PC**. `181.13.218.37` rechaza al usuario `computos` desde la IP pública y `181.13.219.37` no responde (timeout). No se pudo confirmar si ahí hay otra tabla de empleados.

## Propuesta

### 1. Expedientes en dos pestañas

**A. "Mi oficina"**: lo que existe hoy, mejorado.
- Registro de ingresos y egresos de la oficina, bandeja de entrantes y pases internos entre oficinas del sistema nuevo.
- Al cargar un expediente se muestra también su último movimiento en el sistema IPV.

**B. "Expedientes IPV"**: consulta general de solo lectura sobre mitiv.
- **Búsqueda unificada** por número, causante, asunto, oficina actual y rango de fechas, con paginación.
- **Ficha del expediente:**
  - Carátula: número, fecha, causante y tipo, asunto, folios y tomo.
  - **Ubicación actual** destacada, con los días que lleva ahí.
  - **Línea de tiempo de pases**: oficina, quién lo movió, fecha y hora, folios y observación. También los días en cada oficina.
  - Expedientes agregados y relacionados, con link entre fichas.
  - Resoluciones del sistema nuevo vinculadas a ese número.
- Acción "Registrar en mi oficina" desde la ficha.
- **Carátula y historial imprimibles** (vista de impresión en el navegador, sin dompdf).

### 2. Usuarios = empleados activos

- El alta de usuario se hace **eligiendo un empleado activo de mitiv** (autocompletar por legajo o apellido), no con datos libres. Se toman nombre, apellido, legajo y email.
- Se sugiere su oficina según mitiv.
- Aviso para los usuarios del sistema nuevo cuyo legajo ya no está activo (bajas y pasivos), para deshabilitarlos.
- El calendario deja de tener nombres fijos en el código y usa los empleados de la oficina.

### 3. Componentes nuevos sugeridos

| Componente | Qué aporta | Esfuerzo |
|---|---|---|
| **Tablero IPV** en el dashboard | expedientes por oficina, los más demorados (días sin movimiento) e ingresos del mes | medio |
| **Alertas de demora** | expedientes de mi oficina con más de N días sin pase | bajo |
| **Seguimiento (favoritos)** | marcar expedientes de otras oficinas y ver cuándo se mueven | bajo |
| **Búsqueda global** (la barra del sidebar, hoy deshabilitada) | número de expediente, causante o resolución desde cualquier pantalla | bajo |
| **Auditoría visible** | historial de cambios por expediente o resolución (la tabla `audits` ya existe) | bajo |
| **Directorio de oficinas** | oficinas con su personal activo (sin datos sensibles) | bajo |

### 4. Rediseño y rendimiento (pedido posterior)

- Rediseño general: micro-interacciones con mouse (hover, foco, transiciones suaves), jerarquía visual y estados vacíos claros.
- Navegación instantánea: `wire:navigate` con precarga al pasar el mouse (`.hover`), `@persist` del sidebar, assets livianos.
- Skeletons en lo que consulta mitiv (listados, ficha, tablero) con carga diferida (`#[Lazy]`) para que la página aparezca al instante.
- Medir: tiempo de cambio de pestaña, tamaño de JS/CSS, consultas por request.

### 5. Técnica

- Modelos de solo lectura para mitiv (`Mitiv\Expediente`, `Pase`, `Oficina`, `Causante`, `Empleado`), cuyo método `save()` lanza una excepción.
- La conexión `pgsql_mitiv` abre cada sesión en `READ ONLY`. Ideal: un usuario de PostgreSQL con solo `SELECT`, que tenés que crear vos.
- Caché corta (1–5 minutos) para los listados y los contadores del tablero.
- Tests con una base SQLite simulada para mitiv. Nunca contra la base real.

## Decisiones pendientes

1. **Pases**: el sistema viejo sigue siendo el oficial. ¿Los pases del sistema nuevo quedan como registro interno (duplicando la carga) o, por ahora, las oficinas siguen pasando en el viejo y el nuevo solo registra y consulta?
2. **Usuarios**: ¿el alta se restringe a empleados activos de mitiv?
3. **Base `admin`**: hace falta acceso desde esta PC (IP 181.81.35.82) o las credenciales correctas para revisar la tabla de empleados que mencionaste.
4. **Seguridad**: cambiar las claves compartidas por chat (SSH `fconrad` y mitiv) y crear un usuario de solo lectura para mitiv.
