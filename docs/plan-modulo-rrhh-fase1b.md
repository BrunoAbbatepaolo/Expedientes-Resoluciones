# Módulo RRHH — Fase 1b (pedido de licencias) — Plan

**Spec:** `docs/spec-modulo-rrhh.md`, sección 7 bis. **Rama:** `feature/modulo-rrhh`.

## Decisiones de implementación
- Todo en tablas de SiRex (`mysql_admin`); nada se escribe en mitivdb.
- Interruptor `SIREX_RRHH_PEDIDOS` (default `false`): apagado → pantallas ocultas y acciones 404.
- Estados: `pendiente_jefe` → `aprobado_jefe` | `rechazado_jefe` → `confirmado` | `rechazado_rrhh`; `cancelado` por el empleado mientras esté pendiente de jefe.
- Aprueba: cualquier jefe cuyo alcance (jefaturas + oficinas dependientes) incluya la oficina del empleado, salvo el propio empleado. Sin otro jefe → pasa directo a RRHH (`aprobado_jefe`, "sin jefe asignado").
- Confirma: permiso `rrhh_confirmar_pedidos`. Con fuente `mitiv`: "Confirmado — cargar en el sistema viejo".
- Tipos pedibles: tabla `rrhh_tipos_pedibles` (tipo de ausencia, permite retroactivo, requiere adjunto), administrada por quien confirma.
- Validaciones: desde ≤ hasta; días hábiles > 0; sin retroactivo salvo tipos marcados (hasta `SIREX_RRHH_PEDIDO_DIAS_RETRO`, 30 días); no superpone licencias del sistema viejo (excepto regímenes "sin fichar" e ignorados) ni pedidos activos; si es de vacaciones, no supera los pendientes del período vigente (descontando pedidos activos de vacaciones).
- Adjunto: disco privado `local`, `rrhh/pedidos/`; descarga con autorización (empleado, sus jefes, RRHH).
- Avisos: contador en el menú (Mi equipo / Licencias), sin consultar mitivdb.

## Tareas
- [x] 1. Migración + modelos (`RrhhPedidoLicencia`, `RrhhTipoPedible`), enum `EstadoPedido`, config.
- [x] 2. Servicio `PedidosLicencia` (crear, cancelar, aprobar/rechazar, confirmar/rechazar, listados, contadores) + tests.
- [x] 3. Mi RRHH: pedir licencia y mis pedidos + tests.
- [x] 4. Mi equipo: pedidos por aprobar + tests.
- [x] 5. Licencias (RRHH): pedidos por confirmar + tipos pedibles + tests.
- [x] 6. Menú con contadores, permiso en Usuarios, descarga de adjunto, rendimiento, docs.
