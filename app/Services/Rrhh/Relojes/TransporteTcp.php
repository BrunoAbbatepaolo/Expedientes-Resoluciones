<?php

namespace App\Services\Rrhh\Relojes;

/** Conexión TCP real con un reloj, con tiempo de espera en cada operación. */
final class TransporteTcp implements Transporte
{
    /** @var resource|null */
    private $socket;

    public function __construct(string $ip, int $puerto = 4370, int $timeout = 10)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new ErrorReloj('La dirección del reloj no es una IP válida.');
        }

        $socket = @stream_socket_client("tcp://{$ip}:{$puerto}", $codigo, $mensaje, $timeout);
        if ($socket === false) {
            throw new ErrorReloj("No se pudo conectar con el reloj {$ip}:{$puerto} ({$mensaje}).");
        }

        stream_set_timeout($socket, $timeout);
        $this->socket = $socket;
    }

    public function enviar(string $bytes): void
    {
        $pendiente = $bytes;
        while ($pendiente !== '') {
            $escritos = $this->socket === null ? false : @fwrite($this->socket, $pendiente);
            if ($escritos === false || $escritos === 0) {
                throw new ErrorReloj('Se perdió la conexión con el reloj al enviar.');
            }
            $pendiente = substr($pendiente, $escritos);
        }
    }

    public function leer(int $cantidad): string
    {
        $datos = '';
        while (strlen($datos) < $cantidad) {
            $parte = $this->socket === null ? false : fread($this->socket, $cantidad - strlen($datos));
            if ($parte === false || $parte === '') {
                $estado = $this->socket === null ? [] : stream_get_meta_data($this->socket);
                throw new ErrorReloj(($estado['timed_out'] ?? false)
                    ? 'El reloj no respondió a tiempo.'
                    : 'El reloj cerró la conexión.');
            }
            $datos .= $parte;
        }

        return $datos;
    }

    public function cerrar(): void
    {
        if ($this->socket !== null) {
            fclose($this->socket);
            $this->socket = null;
        }
    }
}
