# Agente de relojes dentro de SiRex — especificación

**Fecha:** 2026-10-08 · **Rama:** `feature/modulo-rrhh` · **Contexto:** `docs/analisis-lector-relojes.md`.
**Decisiones del usuario:** el agente vive **dentro de SiRex** (el servidor queda en local y llega a la red de los relojes); durante la convivencia hace **doble escritura** (SiRex + `registros_reloj` del sistema viejo con un usuario de base acotado); se autorizó una prueba real **solo de lectura** contra `.198` y `.199`.

## 1. Validación previa (2026-10-08, solo lectura)

Con la librería de referencia `pyzk` se leyeron los dos relojes de huella (`.198`, `.199`; modelo MB360/ID, firmware 6.60 de 2019, TCP 4370, sin clave de comunicación):

- 21.201 y 20.365 fichadas en ~7 s cada uno; registros de 40 bytes; `user_id` numérico de 2 a 5 dígitos (= `empleados.card_id`); hora del reloj correcta y en hora local.
- Contra `registros_reloj` del sistema viejo coinciden **21.201 de 21.203** y **20.364 de 20.384**. Las diferencias son fichadas que el reloj ya no tiene (borrado del 13/07), correcciones de RRHH (`fecha_hora` ≠ `fecha_hora_inmutable`) y una fichada que el servicio viejo nunca cargó.
- El reloj facial `.197` está caído: **no validado**; se agrega cuando se repare.

## 2. Diseño

- **Cliente del protocolo ZKTeco en PHP** (`App\Services\Rrhh\Relojes\ClienteZk`), solo lectura: `CMD_CONNECT`, tamaños, hora, serie, firmware, lectura de fichadas por buffer y `CMD_EXIT`. **No** deshabilita el reloj, **no** borra memoria, **no** lee usuarios. Transporte intercambiable (`TransporteTcp` real, simulador en los tests). Soporta registros de 40 y de 16 bytes; el formato de 8 bytes (sin `user_id`) se rechaza con un error claro.
- **Tablas de SiRex** (`mysql_admin`): `rrhh_relojes` (ip, puerto, nombre, tipo, activo, serie, modelo, firmware, estado de la última vuelta, fallas seguidas, cantidad de registros del reloj, desvío de hora) y `rrhh_fichadas` (reloj, `card_id`, `fecha_hora` original, `status`, `punch`; único por reloj + tarjeta + fecha/hora).
- **Sincronización** (`SincronizadorRelojes`, comando `relojes:sincronizar`, cada 5 minutos con el planificador de Laravel): por cada reloj activo, con un candado para no solaparse, pide solo el tamaño; **si la cantidad de registros no cambió, no lee nada**. Si cambió, lee todo, descarta lo ya cargado y guarda en lote solo lo nuevo. Un reloj caído no frena a los demás.
- **Doble escritura** (interruptor `SIREX_RELOJES_ESCRIBIR_MITIV`, apagado por defecto), por una conexión **aparte** `pgsql_mitiv_escritura` con usuario acotado (`INSERT` en `registros_reloj` y en `operaciones_reloj`/`log_operaciones_reloj`). La conexión de lectura `pgsql_mitiv` sigue siendo de solo lectura. Escribe en `registros_reloj` (`procesado=false`, `origen='SIREX'`) solo las fichadas que no estén, y deja el **latido** `CHEQUEO_ESTADO_SERVICIO` (cada ≤20 min mientras algún reloj responda) que el sistema viejo exige para calcular faltas y tardanzas. Antes de encender el interruptor hay que **apagar el lector viejo** (los dos controles de duplicado no son atómicos).
- **Estado en SiRex:** con el agente activo, Asistencia muestra una tarjeta por reloj (último contacto, última fichada, errores, desvío de hora) y la alerta usa esos datos. Alerta de un reloj: sin contacto por más de `SIREX_RRHH_RELOJ_ALERTA` minutos en horario laboral, o hora desviada más de 5 minutos.
- **Alta de relojes:** comando `relojes:agregar` y `relojes:probar {ip}` (diagnóstico de solo lectura). Sin pantalla de administración en esta etapa.
- **Credenciales:** solo variables de entorno; nunca en el código ni en la base de SiRex.

## 3. Fuera de alcance (a propósito)

- Borrar la memoria de los relojes (`BORRAR_REGISTROS` del sistema viejo): la memoria (100.000 registros; hoy ~21.000 desde el 13/07) tardaría ~14 meses en llenarse. Se decide aparte.
- Cambiar la hora de los relojes, alta/baja de usuarios o huellas.
- Que las pantallas de asistencia lean de `rrhh_fichadas`: eso es el corte de la fase 2 (`fuente=sirex`).
- Reloj facial `.197` hasta repararlo.

## 4. Pruebas

Simulador de reloj en memoria (conexión, tamaños, datos directos y por bloques fragmentados); vectores de checksum y de hora generados con `pyzk`; carga incremental sin duplicados; omisión de lectura si no cambió la cantidad; un reloj caído no frena al resto; desvío de hora; doble escritura sin duplicar y con latido; interruptores apagados. Verificación real final: lectura con el cliente PHP de `.198` comparada con la de `pyzk`.

## 5. Estado y puesta en marcha (2026-10-08)

**Implementado y probado** (272 tests; prueba real contra `.198` y `.199` en el entorno demo, con la doble escritura apagada): cliente del protocolo, `relojes:probar`, `relojes:agregar`, `relojes:sincronizar`, planificador cada 5 min, tablas `rrhh_relojes` y `rrhh_fichadas`, doble escritura (probada solo contra una base simulada), estado por reloj en Asistencia y alerta. En la prueba real la primera vuelta cargó 21.201 y 20.365 fichadas en SiRex y la segunda no leyó nada (cantidad sin cambios).

**Cómo encenderlo, en este orden** (hasta el paso 4 no se toca el sistema viejo):
1. Dar de alta los relojes: `php artisan relojes:agregar 192.168.7.198 --nombre="…"` (y `.199`; el facial `.197` cuando se repare).
2. Probar a mano: `php artisan relojes:sincronizar --forzar`. Con la doble escritura apagada solo llena las tablas de SiRex.
3. Encender el agente: `SIREX_RELOJES_ACTIVO=true` y dejar corriendo el planificador de Laravel (`php artisan schedule:work` como servicio, o una tarea programada cada minuto con `schedule:run`). Con eso las pantallas de Asistencia muestran el estado de cada reloj.
4. **Doble escritura** (solo cuando se vaya a apagar el lector viejo): crear en PostgreSQL un usuario acotado (`GRANT SELECT, INSERT ON registros_reloj, operaciones_reloj, log_operaciones_reloj` más `USAGE, SELECT` sobre las secuencias de `registros_reloj` y `operaciones_reloj`), cargar `MITIV_WRITE_PG_USERNAME` y `MITIV_WRITE_PG_PASSWORD` en el `.env`, **apagar el lector viejo** y recién ahí poner `SIREX_RELOJES_ESCRIBIR_MITIV=true`. Para recuperar fichadas de un hueco: `php artisan relojes:sincronizar --mitiv-desde=AAAA-MM-DD`.
5. Rotar las claves que estaban en texto plano en el programa viejo.

**Qué hace el agente con el sistema viejo:** inserta en `registros_reloj` (`origen='SIREX'`, `procesado=false`, horas locales) solo lo que no está; renueva el latido `CHEQUEO_ESTADO_SERVICIO` cada ≤20 min mientras algún reloj responda (sin él el proceso de faltas del sistema viejo no corre); y registra `CHEQUEO_RELOJES` + `ERROR_CHECKEO` por cada reloj caído, para que su tablero de errores siga mostrándolos.

**Limitaciones conocidas:** el reloj facial `.197` no se pudo validar (está caído); no se borra la memoria de los relojes (`BORRAR_REGISTROS`); no hay pantalla para administrar relojes (se hace por comando).
