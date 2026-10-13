#!/usr/bin/env bash
# Servidor demo + agente de relojes en WSL. Ver docs/entorno-demo-wsl.md
# Uso (desde Windows):  wsl -d Ubuntu -- bash /mnt/c/Users/Bruno/Documents/Claude/Expedientes-Resoluciones/scripts/demo-wsl.sh up|down|status|sync
set -u

REPO=/mnt/c/Users/Bruno/Documents/Claude/Expedientes-Resoluciones
RUN=$HOME/sirex-run
DEMO=$HOME/sirex-demo
LOGS=$HOME/sirex-cache/logs
mkdir -p "$LOGS" "$HOME/sirex-cache/views"

# Copia del repo en el disco de WSL (desde /mnt/c cada pedido tarda ~10 s). No pisa .env ni storage.
sincronizar() {
  mkdir -p "$RUN"
  rsync -a --exclude=.git --exclude=node_modules --exclude=.env --exclude=storage/logs "$REPO"/ "$RUN"/
  [ -f "$RUN/.env" ] || cp "$REPO/.env" "$RUN/.env"
}

entorno() {
  cd "$RUN" || exit 1
  export DB_CONNECTION=sqlite DB_DATABASE=$DEMO/main.sqlite
  export ADMIN_DB_CONNECTION=sqlite ADMIN_DB_DATABASE=$DEMO/admin.sqlite
  export LEGUI_DB_CONNECTION=sqlite LEGUI_DB_DATABASE=$DEMO/legui.sqlite
  export SESSION_DRIVER=file CACHE_STORE=file
  export SIREX_RELOJES_ESCRIBIR_MITIV=false SIREX_RELOJES_ACTIVO=true
  export APP_URL=http://127.0.0.1:8012   # Storage::url() arma las URLs de las fotos con esto
  [ -e public/storage ] || php artisan storage:link >/dev/null 2>&1   # sirve las fotos subidas
  export VIEW_COMPILED_PATH=$HOME/sirex-cache/views PHP_CLI_SERVER_WORKERS=4
  # NO definir MITIV_DB_DRIVER: el sistema viejo se lee del PostgreSQL real (solo lectura).
}

parar() {
  pkill -f "artisan serve" 2>/dev/null
  pkill -f "artisan schedule:work" 2>/dev/null
  pkill -f "php.*-S 0.0.0.0:8012" 2>/dev/null
  sleep 1
}

case "${1:-up}" in
  sync) sincronizar; echo "Copia actualizada en $RUN" ;;
  down) parar; echo "Apagado." ;;
  status)
    pgrep -af "artisan (serve|schedule:work)" || echo "Nada corriendo."
    curl -s -o /dev/null -w "HTTP %{http_code} en %{time_total}s\n" http://127.0.0.1:8012/login || true
    ;;
  up)
    parar
    sincronizar
    entorno
    setsid nohup php -d opcache.enable_cli=1 artisan serve --host=0.0.0.0 --port=8012 --no-reload >"$LOGS/serve.log" 2>&1 &
    setsid nohup php artisan schedule:work >"$LOGS/schedule.log" 2>&1 &
    for _ in $(seq 1 20); do
      curl -s -o /dev/null http://127.0.0.1:8012/login && break
      sleep 1
    done
    echo "Listo: http://127.0.0.1:8012  (demo@sirex.test / demo1234). Logs en $LOGS"
    ;;
  *) echo "Uso: $0 up|down|status|sync"; exit 1 ;;
esac
