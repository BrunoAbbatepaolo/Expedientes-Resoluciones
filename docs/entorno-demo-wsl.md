# Entorno demo en WSL (servidor + agente de relojes)

Actualizado 2026-10-13. PHP no está instalado en Windows: todo corre en WSL (Ubuntu, usuario `brunella`, PHP 8.3).

## Atajo: un solo comando

```powershell
wsl -d Ubuntu -- bash /mnt/c/Users/Bruno/Documents/Claude/Expedientes-Resoluciones/scripts/demo-wsl.sh up
```

`up` (copia el repo a `~/sirex-run`, exporta las variables, levanta servidor y planificador desacoplados con `setsid`, y espera a que responda), `down`, `status`, `sync` (solo copiar código). Logs en `~/sirex-cache/logs/`. Sigue corriendo aunque se cierre la terminal; se apaga con `down` o al reiniciar WSL. El resto de este documento explica qué hace el script.

## Carpetas

| Carpeta (WSL) | Para qué |
|---|---|
| `~/sirex-run` | Copia del repo desde donde corre el servidor y el planificador. |
| `~/sirex-bench` | Copia para correr tests (`php artisan test`). Puede estar desactualizada. |
| `~/sirex-demo` | Bases SQLite de la demo: `main.sqlite`, `admin.sqlite`, `legui.sqlite`. |
| `~/sirex-cache/views` | Vistas Blade compiladas (fuera de `/mnt/c`). |

**No correr desde `/mnt/c/...`** (el repo de Windows): leer `vendor` por ese disco hace que cada pedido tarde ~10 s. Desde `~/sirex-run` tarda ~15 ms.

`~/sirex-run` es una foto del repo. Tras cambiar código en Windows hay que copiar los archivos (o `rsync -a --exclude=.git --exclude=node_modules <repo>/ ~/sirex-run/`). El `.env` de esa copia no se pisa si se excluye.

## Variables de entorno de la demo

Se exportan antes de arrancar (no están en el `.env`):

```bash
cd ~/sirex-run
export DB_CONNECTION=sqlite            DB_DATABASE=$HOME/sirex-demo/main.sqlite
export ADMIN_DB_CONNECTION=sqlite      ADMIN_DB_DATABASE=$HOME/sirex-demo/admin.sqlite
export LEGUI_DB_CONNECTION=sqlite      LEGUI_DB_DATABASE=$HOME/sirex-demo/legui.sqlite
export SESSION_DRIVER=file CACHE_STORE=file
export SIREX_RELOJES_ESCRIBIR_MITIV=false     # nunca escribir en el sistema viejo en la demo
export SIREX_RELOJES_ACTIVO=true              # enciende el agente de relojes
export VIEW_COMPILED_PATH=$HOME/sirex-cache/views PHP_CLI_SERVER_WORKERS=4
mkdir -p $VIEW_COMPILED_PATH
```

**Importante:** NO definir `MITIV_DB_DRIVER=sqlite`. El sistema viejo (`pgsql_mitiv`) se consulta en PostgreSQL real (`MITIV_PG_*` del `.env`, `192.168.1.95:5432`) en solo lectura. Con SQLite apunta a un archivo inexistente y todas las pantallas de RRHH muestran «No se pudieron consultar los datos de RRHH del sistema viejo».

Usuario demo: `demo@sirex.test` / `demo1234` (tiene `rrhh_ver`, jefe de Cómputos).

## Encender

Dos procesos, en terminales separadas (con las variables de arriba exportadas):

```bash
php -d opcache.enable_cli=1 artisan serve --host=0.0.0.0 --port=8012 --no-reload   # servidor
php artisan schedule:work                                                           # agente de relojes (cada 5 min)
```

- `--no-reload` es necesario para que `PHP_CLI_SERVER_WORKERS=4` surta efecto; sin él crea un solo worker.
- Abrir **http://127.0.0.1:8012**. Con `localhost` Windows pierde ~2 s probando IPv6 primero.
- Se apagan al cerrar la sesión/terminal que los lanzó.

## Agente de relojes

- Relojes dados de alta en la demo: `.198` y `.199` (huella, funcionan) y `.197` (facial, caído: «No route to host»).
- Pasos de puesta en marcha y doble escritura: `docs/spec-agente-relojes.md`, sección 5.
- Con la doble escritura apagada solo se llenan las tablas de SiRex (`rrhh_relojes`, `rrhh_fichadas`).

### Botón «Sincronizar ahora»

En Asistencia (`/rrhh/asistencia`), dentro de la barra de estado de los relojes. Ejecuta `SincronizadorRelojes::sincronizar()` en el momento, sin esperar al planificador, y muestra un resumen («N fichadas nuevas de M relojes. K sin respuesta.»).

- Acción `sincronizarAhora` en `app/Livewire/Rrhh/Asistencia.php`; exige el permiso de RRHH (`alcance()->rrhh`).
- Funciona aunque `SIREX_RELOJES_ACTIVO` esté apagado (equivale a `relojes:sincronizar --forzar`).
- Si el planificador o un doble clic ya están leyendo un reloj, el candado por reloj (`rrhh.reloj.{id}`) evita lecturas duplicadas: ese reloj se informa como «ocupado».
- Test: `tests/Feature/Rrhh/LectorRelojSirexTest.php` (usa `FabricaRelojesSimulada`; no toca relojes reales).

## Problemas conocidos / aprendizajes

- **Lentitud**: correr desde `/mnt/c` (~10 s por pedido) y usar `localhost` (+2 s). Ver arriba.
- **Fotos de perfil rotas**: faltaba el enlace `public/storage` en `~/sirex-run` y `APP_URL` del `.env` apuntaba a otro puerto (`localhost:8010`). El script ahora corre `storage:link` y exporta `APP_URL=http://127.0.0.1:8012`.
- **Tests de relojes**: para simular relojes hay que bindear `FabricaClienteReloj` (con su `use` importado). Un binding con la clase mal importada no falla: el test lee los relojes reales de la red.
- **Quién escribe en el sistema viejo**: nada en la demo. `SIREX_RELOJES_ESCRIBIR_MITIV` queda en `false`.
- `rsync` hacia `~/sirex-bench` fue bloqueado por el control de permisos de Claude Code (sobrescribe archivos); `~/sirex-run` se creó nueva para evitarlo.
