<?php

namespace App\Services\Rrhh\Relojes;

use App\Models\RrhhReloj;
use App\Services\Rrhh\Hora;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Lee los relojes activos y guarda sus fichadas en SiRex (y, con la doble escritura encendida, en el
 * sistema viejo). Ver docs/spec-agente-relojes.md.
 *
 * - Pide solo el tamaño de la memoria: si la cantidad de fichadas no cambió, no lee nada.
 * - Cada reloj va aparte: uno caído no frena a los demás ni a la doble escritura.
 * - Es idempotente: se puede correr las veces que haga falta sin duplicar fichadas.
 */
class SincronizadorRelojes
{
    public function __construct(private FabricaClienteReloj $fabrica, private EscritorMitiv $mitiv) {}

    /**
     * El estado de cada reloj es: sincronizado, sin_cambios, error u ocupado.
     *
     * @return list<array{reloj: RrhhReloj, estado: string, leidas: int, nuevas: int, mitiv: int, sin_tarjeta: int, descartadas: int, mensaje: ?string}>
     */
    public function sincronizar(?string $ip = null, ?string $mitivDesde = null): array
    {
        $relojes = RrhhReloj::where('activo', true)->when($ip, fn ($q) => $q->where('ip', $ip))->orderBy('id')->get();

        $resultados = [];
        foreach ($relojes as $reloj) {
            $resultados[] = $this->unReloj($reloj, $mitivDesde);
        }

        if ($this->mitiv->habilitado()) {
            $this->avisarAlSistemaViejo($resultados);
        }

        return $resultados;
    }

    /** @return array{reloj: RrhhReloj, estado: string, leidas: int, nuevas: int, mitiv: int, sin_tarjeta: int, descartadas: int, mensaje: ?string} */
    private function unReloj(RrhhReloj $reloj, ?string $mitivDesde): array
    {
        $base = ['reloj' => $reloj, 'estado' => 'error', 'leidas' => 0, 'nuevas' => 0, 'mitiv' => 0, 'sin_tarjeta' => 0, 'descartadas' => 0, 'mensaje' => null];

        $candado = Cache::lock('rrhh.reloj.'.$reloj->id, 600);
        if (! $candado->get()) {
            return ['estado' => 'ocupado', 'mensaje' => 'Ya hay una sincronización en curso.'] + $base;
        }

        try {
            $cliente = $this->fabrica->crear($reloj->ip, $reloj->puerto);
            try {
                $cliente->conectar();
                $info = $cliente->informacion();
                $registros = $cliente->memoria()['registros'];

                $resultado = ['estado' => 'sin_cambios'] + $base;
                $ultima = $reloj->ultima_fichada_at;
                $conteo = $registros;

                if ($this->hayCambios($reloj, $registros)) {
                    $lectura = $cliente->leerFichadas();
                    $guardado = $this->guardar($reloj, $lectura, $mitivDesde);
                    $resultado = [
                        'estado' => 'sincronizado', 'leidas' => count($lectura->fichadas), 'nuevas' => $guardado['nuevas'],
                        'mitiv' => $guardado['mitiv'], 'sin_tarjeta' => $guardado['sin_tarjeta'], 'descartadas' => $lectura->descartadas,
                    ] + $base;
                    $ultima = $guardado['ultima'] ?? $ultima;
                    // Si falló la escritura en el sistema viejo, la próxima vuelta tiene que volver a leer.
                    $conteo = $guardado['mitiv_ok'] ? $registros : null;
                    $resultado['mensaje'] = $guardado['mitiv_ok'] ? null : 'No se pudo escribir en el sistema viejo; se reintenta en la próxima vuelta.';
                }

                $reloj->update([
                    'serie' => $info['serie'] ?? $reloj->serie,
                    'modelo' => trim(($info['nombre'] ?? '').' '.($info['plataforma'] ?? '')) ?: $reloj->modelo,
                    'firmware' => $info['firmware'] ?? $reloj->firmware,
                    'ultimo_contacto_at' => now(),
                    'ultima_fichada_at' => $ultima,
                    'registros_en_reloj' => $conteo,
                    'desvio_segundos' => CarbonImmutable::parse($info['hora'])->getTimestamp() - Hora::ahora()->getTimestamp(),
                    'fallas_seguidas' => 0,
                    'ultimo_error' => null,
                ]);

                return $resultado;
            } finally {
                $cliente->desconectar();
            }
        } catch (ErrorReloj $e) {
            return $this->registrarFalla($reloj, $e->getMessage(), $base);
        } catch (Throwable $e) {
            report($e);

            return $this->registrarFalla($reloj, 'Error inesperado: '.class_basename($e), $base);
        } finally {
            $candado->release();
        }
    }

    private function hayCambios(RrhhReloj $reloj, int $registros): bool
    {
        return $reloj->registros_en_reloj === null
            || $reloj->ultima_fichada_at === null
            || $registros !== (int) $reloj->registros_en_reloj;
    }

    /** @return array{nuevas: int, mitiv: int, mitiv_ok: bool, sin_tarjeta: int, ultima: ?string} */
    private function guardar(RrhhReloj $reloj, LecturaReloj $lectura, ?string $mitivDesde): array
    {
        $validas = [];
        $sinTarjeta = 0;
        $ultima = $reloj->ultima_fichada_at;
        foreach ($lectura->fichadas as $f) {
            $tarjeta = $f->tarjeta();
            if ($tarjeta === null) {
                $sinTarjeta++;

                continue;
            }
            $validas[] = ['card_id' => $tarjeta, 'fecha_hora' => $f->fechaHora, 'status' => $f->status, 'punch' => $f->punch];
            $ultima = max((string) $ultima, $f->fechaHora);
        }

        // Solo se revisa desde unos días antes de la última fichada guardada; lo ya guardado se ignora por la clave única.
        $desde = $reloj->ultima_fichada_at
            ? CarbonImmutable::parse($reloj->ultima_fichada_at)->subDays((int) config('sirex.relojes_ventana_dias'))->format('Y-m-d H:i:s')
            : null;
        $candidatas = $desde === null ? $validas : array_values(array_filter($validas, fn ($r) => $r['fecha_hora'] >= $desde));

        $creado = now();
        $nuevas = 0;
        foreach (array_chunk($candidatas, 500) as $lote) {
            $nuevas += DB::connection('mysql_admin')->table('rrhh_fichadas')
                ->insertOrIgnore(array_map(fn ($r) => $r + ['reloj_id' => $reloj->id, 'created_at' => $creado], $lote));
        }

        $mitiv = 0;
        $mitivOk = true;
        if ($this->mitiv->habilitado()) {
            $desdeMitiv = $mitivDesde ?? Hora::ahora()->subDays((int) config('sirex.relojes_ventana_mitiv_dias'))->startOfDay()->format('Y-m-d H:i:s');
            $paraMitiv = array_map(
                fn ($r) => ['card_id' => $r['card_id'], 'fecha_hora' => $r['fecha_hora']],
                array_values(array_filter($validas, fn ($r) => $r['fecha_hora'] >= $desdeMitiv)),
            );
            try {
                $mitiv = $this->mitiv->guardarFichadas($reloj->ip, $paraMitiv);
            } catch (Throwable $e) {
                report($e);
                $mitivOk = false;
            }
        }

        return ['nuevas' => $nuevas, 'mitiv' => $mitiv, 'mitiv_ok' => $mitivOk, 'sin_tarjeta' => $sinTarjeta, 'ultima' => $ultima];
    }

    /**
     * @param  array{reloj: RrhhReloj, estado: string, leidas: int, nuevas: int, mitiv: int, sin_tarjeta: int, descartadas: int, mensaje: ?string}  $base
     * @return array{reloj: RrhhReloj, estado: string, leidas: int, nuevas: int, mitiv: int, sin_tarjeta: int, descartadas: int, mensaje: ?string}
     */
    private function registrarFalla(RrhhReloj $reloj, string $mensaje, array $base): array
    {
        $reloj->update([
            'fallas_seguidas' => min(65000, $reloj->fallas_seguidas + 1),
            'ultimo_error' => mb_substr($mensaje, 0, 500),
            'ultimo_error_at' => now(),
        ]);
        Log::warning("Reloj {$reloj->ip}: {$mensaje}");

        return ['estado' => 'error', 'mensaje' => $mensaje] + $base;
    }

    /** Latido si algún reloj respondió, y un aviso por cada reloj caído (como hacía el lector viejo). */
    private function avisarAlSistemaViejo(array $resultados): void
    {
        try {
            if (collect($resultados)->contains(fn ($r) => in_array($r['estado'], ['sincronizado', 'sin_cambios'], true))) {
                $this->mitiv->latido();
            }
            foreach ($resultados as $r) {
                if ($r['estado'] === 'error') {
                    $this->mitiv->falla($r['reloj']->ip);
                }
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
