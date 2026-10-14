# Plan: paridad con el sistema viejo (95) y mejoras — 2026-10-14

Aprobado por el usuario: puntos 1, 2 y 3 de la comparación con el 95 + todas las mejoras listadas. Lo grande
(Personas, Soluciones habitacionales, Novedades) queda fuera hasta que el área lo pida.

## Faltantes del 95
1. **Pases emitidos** (`expediente_pases_origen`, vista de mitivdb): informe por oficina de origen y período, con CSV e impresión A4. Carátula/pases del expediente ya salen en `imprimir-ipv`; el hueco era el listado de pases emitidos.
2. **RRHH**: cumpleaños (solo día y mes, respetando el alcance) y reportes que faltan: inasistencias, antigüedad por situación laboral, personal activo/pasivo. Todos por los contratos `Legajos`/`Licencias`/`Asistencias` con `$alcance` y con CSV.
3. **Adjuntos en la ficha del expediente** (`expediente_adjuntos`: cabecera / agregados), solo lectura, recorrido iterativo con control de ciclos.

## Mejoras
- Reporte de resoluciones cuyo `numero_exp` no existe en mitivdb (consulta por lotes).
- Auditar el modelo `Pase` de SiRex (el 95 borra pases sin dejar rastro).
- `admin_ver`: solo quien ya lo tiene puede otorgarlo o quitarlo.
- `expediente_ver` por defecto (oficina propia) para usuarios nuevos.
- Versión imprimible de "Ingresos a oficina".
- Página 404 con el diseño animado.
- Demo en red: clave aleatoria para `demo@sirex.test` y `APP_URL` con la IP de la red.
- Endurecimiento: `Auditable` no guarda ni muestra claves/tokens; CSV neutraliza también tab y retorno de carro y lo hace también el CSV de RRHH.
- Logo: símbolo del IPV en login y sidebar (hecho).

## Cierre
Un commit por tema, suite completa y phpstan antes de subir; `push` aparte; actualizar HANDOFF.md.
