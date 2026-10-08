<?php

namespace App\Services\Rrhh\Relojes;

/**
 * Cliente de SOLO LECTURA del protocolo TCP de los relojes ZKTeco (puerto 4370).
 *
 * Portado de la implementación de referencia pyzk, que se validó contra los relojes reales
 * (docs/spec-agente-relojes.md, sección 1). Hace únicamente: conectar, pedir tamaños, hora, serie y
 * firmware, leer la memoria de fichadas y desconectar. No deshabilita el reloj, no borra, no escribe
 * y no lee usuarios ni huellas.
 *
 * Cada mensaje del reloj llega con su largo: 50 50 82 7D + largo (4 bytes) + [comando, suma,
 * sesión, respuesta (2 bytes c/u)] + datos. Se leen mensajes completos, sin reensamblado manual.
 */
final class ClienteZk
{
    public const CMD_OPTIONS_RRQ = 11;

    public const CMD_ATTLOG_RRQ = 13;

    public const CMD_GET_FREE_SIZES = 50;

    public const CMD_GET_TIME = 201;

    public const CMD_CONNECT = 1000;

    public const CMD_EXIT = 1001;

    public const CMD_GET_VERSION = 1100;

    public const CMD_AUTH = 1102;

    public const CMD_PREPARE_DATA = 1500;

    public const CMD_DATA = 1501;

    public const CMD_FREE_DATA = 1502;

    public const CMD_PREPARE_BUFFER = 1503;

    public const CMD_READ_BUFFER = 1504;

    public const CMD_ACK_OK = 2000;

    public const CMD_ACK_ERROR = 2001;

    public const CMD_ACK_UNAUTH = 2005;

    private const MAGIA = "\x50\x50\x82\x7d";

    private const MAX_BLOQUE = 0xFFC0;

    private const MAX_MARCO = 16 * 1024 * 1024;

    private int $sesion = 0;

    private int $replyId = 65534;

    private bool $conectado = false;

    public function __construct(private readonly Transporte $transporte, private readonly int $clave = 0) {}

    /** Suma de verificación del protocolo (copiada del SDK de ZKTeco, igual que pyzk). */
    public static function checksum(string $bytes): int
    {
        $largo = strlen($bytes);
        $suma = 0;
        for ($i = 0; $i + 1 < $largo; $i += 2) {
            $suma += ord($bytes[$i]) | (ord($bytes[$i + 1]) << 8);
            if ($suma > 65535) {
                $suma -= 65535;
            }
        }
        if ($largo % 2 === 1) {
            $suma += ord($bytes[$largo - 1]);
        }
        while ($suma > 65535) {
            $suma -= 65535;
        }
        $resultado = ~$suma;
        while ($resultado < 0) {
            $resultado += 65535;
        }

        return $resultado;
    }

    /** Mensaje TCP completo listo para enviar. La suma se calcula con el número de respuesta anterior al incremento. */
    public static function paquete(int $comando, string $datos, int $sesion, int $reply): string
    {
        $suma = self::checksum(pack('v4', $comando, 0, $sesion, $reply).$datos);
        $reply++;
        if ($reply >= 65535) {
            $reply -= 65535;
        }
        $mensaje = pack('v4', $comando, $suma, $sesion, $reply).$datos;

        return self::MAGIA.pack('V', strlen($mensaje)).$mensaje;
    }

    /** Hora codificada del reloj a 'Y-m-d H:i:s' (hora local, sin zona); null si no es una fecha válida. */
    public static function decodificarHora(int $t): ?string
    {
        $segundo = $t % 60;
        $t = intdiv($t, 60);
        $minuto = $t % 60;
        $t = intdiv($t, 60);
        $hora = $t % 24;
        $t = intdiv($t, 24);
        $dia = $t % 31 + 1;
        $t = intdiv($t, 31);
        $mes = $t % 12 + 1;
        $anio = intdiv($t, 12) + 2000;

        return $anio <= 2100 && checkdate($mes, $dia, $anio)
            ? sprintf('%04d-%02d-%02d %02d:%02d:%02d', $anio, $mes, $dia, $hora, $minuto, $segundo)
            : null;
    }

    /**
     * Clave de autenticación a partir de la clave de comunicación del reloj y la sesión (copiada del
     * SDK, igual que pyzk). Los relojes la exigen aunque la clave sea 0.
     */
    public static function claveDeComunicacion(int $clave, int $sesion, int $ticks = 50): string
    {
        $k = 0;
        for ($i = 0; $i < 32; $i++) {
            $k = ($clave & (1 << $i)) ? (($k << 1) | 1) : ($k << 1);
        }
        $k = ($k + $sesion) & 0xFFFFFFFF;

        $b = [$k & 0xFF, ($k >> 8) & 0xFF, ($k >> 16) & 0xFF, ($k >> 24) & 0xFF];
        $b = [$b[0] ^ ord('Z'), $b[1] ^ ord('K'), $b[2] ^ ord('S'), $b[3] ^ ord('O')];
        $c = [$b[2], $b[3], $b[0], $b[1]]; // se intercambian las dos mitades de 16 bits
        $t = $ticks & 0xFF;

        return chr($c[0] ^ $t).chr($c[1] ^ $t).chr($t).chr($c[3] ^ $t);
    }

    public function conectar(): void
    {
        $this->sesion = 0;
        $this->replyId = 65534;
        $respuesta = $this->intercambiar(self::CMD_CONNECT, '');
        $this->sesion = $respuesta['sesion'];

        if ($respuesta['comando'] === self::CMD_ACK_UNAUTH) {
            $respuesta = $this->intercambiar(self::CMD_AUTH, self::claveDeComunicacion($this->clave, $this->sesion));
            if ($respuesta['comando'] === self::CMD_ACK_UNAUTH) {
                throw new ErrorReloj('El reloj rechazó la clave de comunicación.');
            }
        }
        if ($respuesta['comando'] !== self::CMD_ACK_OK) {
            throw new ErrorReloj("El reloj rechazó la conexión (código {$respuesta['comando']}).");
        }

        $this->conectado = true;
    }

    public function desconectar(): void
    {
        try {
            if ($this->conectado) {
                $this->intercambiar(self::CMD_EXIT, '');
            }
        } catch (ErrorReloj) {
            // Ya no importa: se cierra igual.
        } finally {
            $this->conectado = false;
            $this->transporte->cerrar();
        }
    }

    /** @return array{firmware: ?string, serie: ?string, nombre: ?string, plataforma: ?string, hora: string} */
    public function informacion(): array
    {
        $firmware = null;
        try {
            $firmware = $this->texto($this->ejecutar(self::CMD_GET_VERSION)['datos']) ?: null;
        } catch (ErrorReloj) {
            // Algunos modelos no lo informan.
        }

        return [
            'firmware' => $firmware,
            'serie' => $this->opcion('SerialNumber'),
            'nombre' => $this->opcion('DeviceName'),
            'plataforma' => $this->opcion('Platform'),
            'hora' => $this->hora(),
        ];
    }

    /** Hora del reloj ('Y-m-d H:i:s', hora local). */
    public function hora(): string
    {
        $datos = $this->ejecutar(self::CMD_GET_TIME)['datos'];
        if (strlen($datos) < 4) {
            throw new ErrorReloj('El reloj no informó su hora.');
        }

        return self::decodificarHora(unpack('V', $datos)[1]) ?? throw new ErrorReloj('El reloj informó una hora inválida.');
    }

    /** @return array{registros: int, usuarios: int, capacidad_registros: int} */
    public function memoria(): array
    {
        $datos = $this->ejecutar(self::CMD_GET_FREE_SIZES)['datos'];
        if (strlen($datos) < 80) {
            throw new ErrorReloj('El reloj no informó el uso de su memoria.');
        }
        $campos = array_values(unpack('V20', substr($datos, 0, 80)));

        return ['registros' => $campos[8], 'usuarios' => $campos[4], 'capacidad_registros' => $campos[16]];
    }

    /** Lee toda la memoria de fichadas del reloj. */
    public function leerFichadas(): LecturaReloj
    {
        $registros = $this->memoria()['registros'];
        if ($registros === 0) {
            return new LecturaReloj([], 0, 0);
        }

        $datos = $this->leerConBuffer(self::CMD_ATTLOG_RRQ);
        if (strlen($datos) < 4) {
            return new LecturaReloj([], 0, 0);
        }

        $total = unpack('V', substr($datos, 0, 4))[1];
        $cuerpo = substr($datos, 4);
        $tamano = $this->bytesPorRegistro($total, $registros);

        if (strlen($cuerpo) < $total) {
            throw new ErrorReloj('La lectura de fichadas quedó incompleta.');
        }

        $fichadas = [];
        $descartadas = 0;
        for ($i = 0; $i + $tamano <= $total; $i += $tamano) {
            $registro = substr($cuerpo, $i, $tamano);
            $campos = $tamano === 40
                ? unpack('vuid/Z24usuario/Cstatus/Vtiempo/Cpunch', $registro)
                : unpack('Vusuario/Vtiempo/Cstatus/Cpunch', $registro);

            $fecha = self::decodificarHora($campos['tiempo']);
            if ($fecha === null) {
                $descartadas++;

                continue;
            }
            $fichadas[] = new FichadaReloj((string) $campos['usuario'], $fecha, $campos['status'], $campos['punch']);
        }

        return new LecturaReloj($fichadas, $descartadas, $tamano);
    }

    /**
     * El tamaño del registro se deduce de total ÷ cantidad. Puede dar algo apenas distinto de 40 si
     * alguien fichó entre pedir el tamaño y leer: se toma el formato más cercano.
     */
    private function bytesPorRegistro(int $total, int $registros): int
    {
        $razon = $total / $registros;
        $mejor = null;
        foreach ([8, 16, 40] as $tamano) {
            if ($mejor === null || abs($razon - $tamano) < abs($razon - $mejor)) {
                $mejor = $tamano;
            }
        }

        if (abs($razon - $mejor) / $mejor > 0.05) {
            throw new ErrorReloj(sprintf('Formato de fichadas desconocido (%.1f bytes por registro).', $razon));
        }
        if ($mejor === 8) {
            throw new ErrorReloj('Este reloj guarda registros de 8 bytes (sin número de tarjeta): no está soportado.');
        }
        if ($total % $mejor !== 0) {
            throw new ErrorReloj("La lectura de fichadas no es múltiplo de {$mejor} bytes.");
        }

        return $mejor;
    }

    /** Lectura de un conjunto grande de datos: se prepara un buffer en el reloj y se lo pide por bloques. */
    private function leerConBuffer(int $comando): string
    {
        $preparado = $this->ejecutar(self::CMD_PREPARE_BUFFER, pack('CvVV', 1, $comando, 0, 0));
        if ($preparado['comando'] === self::CMD_DATA) {
            return $preparado['datos']; // el reloj devolvió todo de una vez
        }
        if (strlen($preparado['datos']) < 5) {
            throw new ErrorReloj('El reloj no informó el tamaño de los datos.');
        }

        $tamano = unpack('V', substr($preparado['datos'], 1, 4))[1];
        $partes = [];
        try {
            for ($leidos = 0; $leidos < $tamano; $leidos += self::MAX_BLOQUE) {
                $partes[] = $this->leerBloque($leidos, min(self::MAX_BLOQUE, $tamano - $leidos));
            }
        } finally {
            try {
                $this->ejecutar(self::CMD_FREE_DATA);
            } catch (ErrorReloj) {
                // Se libera al desconectar.
            }
        }

        return implode('', $partes);
    }

    private function leerBloque(int $inicio, int $largo): string
    {
        for ($intento = 1; $intento <= 3; $intento++) {
            $respuesta = $this->intercambiar(self::CMD_READ_BUFFER, pack('VV', $inicio, $largo));

            if ($respuesta['comando'] === self::CMD_DATA) {
                return $respuesta['datos'];
            }

            if ($respuesta['comando'] === self::CMD_PREPARE_DATA) {
                if (strlen($respuesta['datos']) < 4) {
                    throw new ErrorReloj('El reloj no informó el tamaño del bloque.');
                }
                $esperado = unpack('V', substr($respuesta['datos'], 0, 4))[1];
                $acumulado = '';
                while (strlen($acumulado) < $esperado) {
                    $marco = $this->leerMarco();
                    if ($marco['comando'] !== self::CMD_DATA) {
                        throw new ErrorReloj("Respuesta inesperada del reloj ({$marco['comando']}) leyendo datos.");
                    }
                    $acumulado .= $marco['datos'];
                }
                if ($this->leerMarco()['comando'] !== self::CMD_ACK_OK) {
                    throw new ErrorReloj('El reloj no confirmó el fin del bloque.');
                }

                return substr($acumulado, 0, $esperado);
            }

            if ($respuesta['comando'] !== self::CMD_ACK_ERROR) {
                throw new ErrorReloj("Respuesta inesperada del reloj ({$respuesta['comando']}).");
            }
        }

        throw new ErrorReloj("No se pudo leer el bloque que empieza en {$inicio}.");
    }

    /** Pide una opción de configuración del reloj (~SerialNumber, ~DeviceName…); null si no la tiene. */
    private function opcion(string $nombre): ?string
    {
        try {
            $datos = $this->ejecutar(self::CMD_OPTIONS_RRQ, "~{$nombre}\0")['datos'];
        } catch (ErrorReloj) {
            return null;
        }
        $valor = $this->texto(explode('=', $datos, 2)[1] ?? '');

        return $valor !== '' ? $valor : null;
    }

    private function texto(string $datos): string
    {
        return trim(explode("\0", $datos, 2)[0]);
    }

    /**
     * Envía un comando y devuelve la respuesta; falla si el reloj no lo acepta.
     *
     * @return array{comando: int, sesion: int, reply: int, datos: string}
     */
    private function ejecutar(int $comando, string $datos = ''): array
    {
        if (! $this->conectado) {
            throw new ErrorReloj('No hay conexión con el reloj.');
        }
        $respuesta = $this->intercambiar($comando, $datos);
        if (! in_array($respuesta['comando'], [self::CMD_ACK_OK, self::CMD_PREPARE_DATA, self::CMD_DATA], true)) {
            throw new ErrorReloj("El reloj rechazó el comando {$comando} (código {$respuesta['comando']}).");
        }

        return $respuesta;
    }

    /** @return array{comando: int, sesion: int, reply: int, datos: string} */
    private function intercambiar(int $comando, string $datos): array
    {
        $this->transporte->enviar(self::paquete($comando, $datos, $this->sesion, $this->replyId));
        $marco = $this->leerMarco();
        $this->replyId = $marco['reply'];

        return $marco;
    }

    /** @return array{comando: int, sesion: int, reply: int, datos: string} */
    private function leerMarco(): array
    {
        $cabecera = $this->transporte->leer(8);
        if (! str_starts_with($cabecera, self::MAGIA)) {
            throw new ErrorReloj('El reloj respondió con un formato desconocido.');
        }

        $largo = unpack('V', substr($cabecera, 4, 4))[1];
        if ($largo < 8 || $largo > self::MAX_MARCO) {
            throw new ErrorReloj('El reloj respondió con un tamaño inválido.');
        }

        $cuerpo = $this->transporte->leer($largo);
        $campos = unpack('vcomando/vsuma/vsesion/vreply', substr($cuerpo, 0, 8));

        return ['comando' => $campos['comando'], 'sesion' => $campos['sesion'], 'reply' => $campos['reply'], 'datos' => substr($cuerpo, 8)];
    }
}
