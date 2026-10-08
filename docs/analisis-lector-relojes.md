# Lector de relojes — análisis y propuesta de incorporación a SiRex

**Fecha:** 2026-10-08 · **Fuente analizada:** `C:\Users\Bruno\Documents\servicio-sincronizador-relojes\servicio-sincronizador-relojes` (proyecto `IPVRelojService`) · **Estado:** análisis; nada se modificó en el programa ni en los relojes.

## 1. Qué es y qué hace

- Programa .NET Framework (2017) con el SDK COM de ZKTeco (`zkemkeeper`, 32 bits, solo Windows). Corre como servicio de Windows (`ipv_reloj`) o, si se abre a mano, como consola.
- Un planificador (Quartz) lo ejecuta cada `scheduler_check_interval` (600 s). En cada vuelta, para cada reloj (`relojes_ip`: `.197` facial, `.198` y `.199` huellas, puerto 4370):
  1. se conecta, **deshabilita el reloj** (`EnableDevice(false)`), lee **toda** la memoria de fichadas (unos 43.000–53.000 eventos por reloj) y lo vuelve a habilitar;
  2. por cada evento hace un `SELECT COUNT(*)` y, si no existe, un `INSERT` en `mitivdb.registros_reloj` (card_id, fecha_hora, fecha_hora_inmutable, procesado=false, nombre_reloj), **abriendo una conexión nueva a PostgreSQL por cada consulta**;
  3. mira `operaciones_reloj` por tareas `PENDIENTE`; la única que entiende es `BORRAR_REGISTROS` (`ClearGLog`: **borra la memoria del reloj**) y deja constancia en `log_operaciones_reloj`.
- Si algo falla, manda un mail por Gmail a una persona fija.
- `check_reloj_service.cmd` es un "vigilante": si el proceso no está, lo abre en consola desde `C:\servicio-sincronizador-relojes`.

## 2. Lo que encontré (datos reales de mitivdb, solo lectura)

1. **El código de esa carpeta no es exactamente el que corre.** La versión en producción además registra el latido `CHEQUEO_ESTADO_SERVICIO` / `CHEQUEO_RELOJES` en `operaciones_reloj`, completa la columna `origen` y vigila un cuarto reloj (`.223`). Nada de eso está en el código fuente de la carpeta. Hace falta la versión real y saber dónde corre.
2. **Hoy solo funciona de mañana.** Desde mediados de septiembre el latido aparece de lunes a viernes entre las ~06:55 y las ~11:30 y no hay latidos de noche ni fines de semana (antes era las 24 h). Coincide con "hay que abrirlo a mano". Consecuencia: las fichadas de salida (12, 13 y 14 h) **no se pierden** (el reloj las guarda) pero **llegan recién a la mañana siguiente**.
3. **Reloj facial `.197` caído.** "Reloj inaccesible" 109 veces en 7 días; desde el 02/10 entraron 5 fichadas y desde el 05/10 ninguna. Desde esta PC el puerto 4370 de `.197` y de `.223` no responde; el de `.198` y `.199` sí. El `.223` figura en la configuración pero está apagado (107 errores en 7 días). Impacto medido hoy: solo 2–3 de las faltas calculadas corresponden a gente que ficha únicamente en el facial (no es la causa principal de las faltas).
4. **El reloj se bloquea mientras se lee.** `EnableDevice(false)` durante ~45–60 s por reloj, cada 10 minutos: quien intente fichar en ese momento no puede (según el código de la carpeta).
5. **Ineficiente y frágil:** relee toda la memoria cada vuelta (el log de 33 MB dice "Eventos extraídos 43.853, nuevos 0"), ~150.000 consultas por vuelta, una conexión por consulta; las 3 lecturas son secuenciales.
6. **Seguridad (importante):**
   - El programa se conecta a mitivdb como el **superusuario de PostgreSQL** con la clave **en texto plano** en su configuración, y la base se alcanza por una **IP pública**.
   - La **clave de aplicación de Gmail** está escrita en el código fuente (`Mailer.cs`), junto con el mail de una persona.
   - Todas las consultas se arman concatenando texto (riesgo de inyección SQL; los datos vienen de los relojes).
   - Ambas claves quedaron visibles en el análisis hecho en esta sesión: **conviene rotarlas** (clave del usuario de la base y clave de aplicación de Gmail) y crear un usuario de base solo con permiso de `INSERT` en `registros_reloj` y de lectura/escritura en `operaciones_reloj`.
7. **Mi alerta de SiRex tenía un error que corregí:** los latidos llegan cada 30 min y hubo cortes normales de hasta 60 min, así que el límite de 30 min saltaba en falso; además avisaba fuera de horario. Ahora el límite es de 75 min (`SIREX_RRHH_RELOJ_ALERTA`), solo avisa en horario laboral y, al empezar la jornada, mide desde las 7:00.

## 3. Opciones para incorporarlo

| Opción | Qué es | A favor | En contra |
|---|---|---|---|
| **A. Dejarlo como está, pero bien instalado** | Instalar el `.exe` actual como **servicio de Windows** con inicio automático y reinicio ante fallas, en un equipo siempre encendido con acceso a los relojes y a la base | Sin programar; resuelve "hay que abrirlo a mano" | Sigue dependiendo de Windows/COM y de código viejo y con claves expuestas; no queda integrado a SiRex |
| **B. Agente nuevo que informa a SiRex (recomendada)** | Programa chico y moderno que lee **solo los eventos nuevos**, sin bloquear el reloj, y los envía a SiRex por HTTPS con un token; manda un latido propio. SiRex guarda las fichadas en su propia tabla, vigila la salud y avisa | Cierra el camino al corte con el sistema viejo; alerta desde SiRex; claves y SQL saneados; eficiente | Hay que desarrollarlo y probarlo con los relojes reales; mientras convivan los dos sistemas, el agente tiene que seguir escribiendo en `registros_reloj` (SiRex no escribe en mitivdb) |
| **C. SiRex lee los relojes directamente** | Un comando programado de SiRex consulta los relojes (protocolo UDP/TCP 4370, sin COM) | Un solo sistema, sin Windows | Exige que el servidor de SiRex llegue a la red `192.168.7.x` y validar que el protocolo funcione con el reloj facial; más riesgo para empezar |

## 4. Recomendación y orden

1. **Esta semana, sin programar (opción A):** instalar el servicio actual como servicio de Windows en un equipo fijo y rotar las dos claves. Es lo que más rápido evita que el lector se caiga.
2. **Reparar o dar de baja el reloj `.197`** (y sacar el `.223` de la configuración): hoy genera ruido y fichadas que no llegan.
3. **Construir el agente (opción B)** en este orden: lectura incremental con prueba de solo lectura contra un reloj; envío a SiRex con token; tabla propia `rrhh_fichadas` y escritura temporal en `registros_reloj` con un usuario acotado; latido y alertas en SiRex; apagar el servicio viejo al terminar. Se evaluaría la opción C si el servidor de SiRex llega a la red de los relojes.

## 5. Preguntas abiertas para el usuario

- ¿En qué equipo corre hoy el lector, quién lo abre y a qué hora se cierra? ¿Existe una versión más nueva del código?
- ¿Dónde va a vivir SiRex en producción y desde ahí se alcanza la red `192.168.7.x`?
- ¿Se puede reparar el reloj facial `.197`? ¿El `.223` está dado de baja?
- ¿Se puede crear en PostgreSQL un usuario acotado para el agente?
