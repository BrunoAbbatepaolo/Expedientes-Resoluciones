<?php

namespace App\Services\Rrhh\Relojes;

use RuntimeException;

/** Falla al hablar con un reloj (red, protocolo o formato). El mensaje es apto para mostrar. */
class ErrorReloj extends RuntimeException {}
