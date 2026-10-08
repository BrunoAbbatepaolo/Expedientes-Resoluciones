<?php

namespace Tests\Support;

use App\Services\Rrhh\Relojes\ClienteZk;
use App\Services\Rrhh\Relojes\ErrorReloj;
use App\Services\Rrhh\Relojes\Transporte;

/**
 * Reloj ZKTeco simulado en memoria: habla el protocolo del lado del reloj (solo lectura) para probar
 * ClienteZk sin red. Las respuestas son las que dan los relojes reales (relevado con pyzk).
 */
class RelojSimulado implements Transporte
{
    public const SESION = 4660;

    /** @var list<array{0: string, 1: string, 2: int, 3: int}> userId, 'Y-m-d H:i:s', status, punch */
    public array $fichadas;

    public int $bytesPorRegistro = 40;

    /** 'bloques' = PREPARE_DATA + varios CMD_DATA + ACK (como los relojes reales); 'directo' = un solo CMD_DATA. */
    public string $modo = 'bloques';

    public int $fragmento = 5000;

    public bool $pideAutenticacion = true;

    public int $clave = 0;

    /** Fichadas que el reloj reporta de más en el tamaño (alguien ficha entre pedir el tamaño y leer). */
    public int $registrosDeMenos = 0;

    /** @var list<int> comandos recibidos, en orden */
    public array $comandos = [];

    /** @var list<int> sesión de cada comando recibido */
    public array $sesiones = [];

    public bool $cerrado = false;

    private string $entrada = '';

    private bool $autenticado = false;

    private string $blob = '';

    /** @param  list<array{0: string, 1: string, 2: int, 3: int}>  $fichadas */
    public function __construct(array $fichadas = [], public string $hora = '2026-10-08 08:34:12')
    {
        $this->fichadas = $fichadas;
    }

    public function enviar(string $bytes): void
    {
        if (substr($bytes, 0, 4) !== "\x50\x50\x82\x7d") {
            throw new ErrorReloj('simulador: marco inválido');
        }
        $largo = unpack('V', substr($bytes, 4, 4))[1];
        if (strlen($bytes) !== 8 + $largo) {
            throw new ErrorReloj('simulador: largo inválido');
        }
        $c = unpack('vcomando/vsuma/vsesion/vreply', substr($bytes, 8, 8));
        $datos = substr($bytes, 16);
        $this->comandos[] = $c['comando'];
        $this->sesiones[] = $c['sesion'];

        foreach ($this->responder($c['comando'], $datos, $c['reply']) as $marco) {
            $this->entrada .= $marco;
        }
    }

    public function leer(int $cantidad): string
    {
        if (strlen($this->entrada) < $cantidad) {
            throw new ErrorReloj('El reloj no respondió a tiempo.');
        }
        $parte = substr($this->entrada, 0, $cantidad);
        $this->entrada = substr($this->entrada, $cantidad);

        return $parte;
    }

    public function cerrar(): void
    {
        $this->cerrado = true;
    }

    public static function codificarHora(string $fecha): int
    {
        [$f, $h] = explode(' ', $fecha);
        [$anio, $mes, $dia] = array_map('intval', explode('-', $f));
        [$hora, $min, $seg] = array_map('intval', explode(':', $h));

        return ((($anio % 100) * 12 * 31 + ($mes - 1) * 31 + ($dia - 1)) * 86400) + $hora * 3600 + $min * 60 + $seg;
    }

    /** @return list<string> */
    private function responder(int $comando, string $datos, int $reply): array
    {
        $ok = fn (string $d = '', int $cmd = ClienteZk::CMD_ACK_OK) => [$this->marco($cmd, self::SESION, $reply, $d)];

        switch ($comando) {
            case ClienteZk::CMD_CONNECT:
                return $this->pideAutenticacion
                    ? [$this->marco(ClienteZk::CMD_ACK_UNAUTH, self::SESION, $reply, '')]
                    : $ok();
            case ClienteZk::CMD_AUTH:
                if ($datos !== ClienteZk::claveDeComunicacion($this->clave, self::SESION)) {
                    return [$this->marco(ClienteZk::CMD_ACK_UNAUTH, self::SESION, $reply, '')];
                }
                $this->autenticado = true;

                return $ok();
        }

        if ($this->pideAutenticacion && ! $this->autenticado) {
            return [$this->marco(ClienteZk::CMD_ACK_UNAUTH, self::SESION, $reply, '')];
        }

        switch ($comando) {
            case ClienteZk::CMD_EXIT:
            case ClienteZk::CMD_FREE_DATA:
                return $ok();
            case ClienteZk::CMD_GET_VERSION:
                return $ok("Ver 6.60 Sep 19 2019\0");
            case ClienteZk::CMD_OPTIONS_RRQ:
                return match (rtrim($datos, "\0")) {
                    '~SerialNumber' => $ok("SerialNumber=AEWD224660235\0"),
                    '~DeviceName' => $ok("~DeviceName=MB360/ID\0"),
                    '~Platform' => $ok("~Platform=ZMM220_TFT\0"),
                    default => [$this->marco(ClienteZk::CMD_ACK_ERROR, self::SESION, $reply, '')],
                };
            case ClienteZk::CMD_GET_TIME:
                return $ok(pack('V', self::codificarHora($this->hora)));
            case ClienteZk::CMD_GET_FREE_SIZES:
                $campos = array_fill(0, 20, 0);
                $campos[4] = 761;
                $campos[8] = max(0, count($this->fichadas) - $this->registrosDeMenos);
                $campos[16] = 100000;

                return $ok(pack('V20', ...$campos).pack('V3', 509, 0, 3000));
            case ClienteZk::CMD_PREPARE_BUFFER:
                $this->blob = $this->armarBlob();
                if ($this->modo === 'directo') {
                    return [$this->marco(ClienteZk::CMD_DATA, self::SESION, $reply, $this->blob)];
                }

                return $ok("\x00".pack('V', strlen($this->blob)));
            case ClienteZk::CMD_READ_BUFFER:
                [$inicio, $largo] = array_values(unpack('V2', $datos));
                $trozo = substr($this->blob, $inicio, $largo);
                if ($this->modo === 'directo') {
                    return [$this->marco(ClienteZk::CMD_DATA, self::SESION, $reply, $trozo)];
                }
                $marcos = [$this->marco(ClienteZk::CMD_PREPARE_DATA, self::SESION, $reply, pack('V', strlen($trozo)))];
                foreach (str_split($trozo, $this->fragmento) as $parte) {
                    $marcos[] = $this->marco(ClienteZk::CMD_DATA, self::SESION, $reply, $parte);
                }
                $marcos[] = $this->marco(ClienteZk::CMD_ACK_OK, self::SESION, $reply, '');

                return $marcos;
        }

        return [$this->marco(ClienteZk::CMD_ACK_ERROR, self::SESION, $reply, '')];
    }

    private function armarBlob(): string
    {
        $registros = '';
        foreach ($this->fichadas as $i => [$usuario, $fecha, $status, $punch]) {
            $tiempo = str_starts_with($fecha, 'INVALIDA') ? 0xFFFFFFFF : self::codificarHora($fecha);
            $registros .= match ($this->bytesPorRegistro) {
                40 => pack('v', $i + 1).str_pad($usuario, 24, "\0").pack('CVC', $status, $tiempo, $punch).str_repeat("\0", 8),
                16 => pack('VVCCvV', (int) $usuario, $tiempo, $status, $punch, 0, 0),
                8 => pack('vCVC', $i + 1, $status, $tiempo, $punch),
            };
        }

        return pack('V', strlen($registros)).$registros;
    }

    private function marco(int $comando, int $sesion, int $reply, string $datos): string
    {
        $mensaje = pack('v4', $comando, 0, $sesion, $reply).$datos;

        return "\x50\x50\x82\x7d".pack('V', strlen($mensaje)).$mensaje;
    }
}
