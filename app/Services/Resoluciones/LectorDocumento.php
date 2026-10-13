<?php

namespace App\Services\Resoluciones;

use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

/** Saca el texto de un PDF (con texto) o de un Word .docx para poder analizarlo. */
class LectorDocumento
{
    /** Tope de lo que se expande de un archivo de usuario (una resolución real pesa mucho menos). */
    private const MAX_BYTES_EXPANDIDOS = 10 * 1024 * 1024;

    /** Texto máximo que se analiza: más que suficiente para una resolución. */
    private const MAX_CARACTERES = 200_000;

    /**
     * @throws \DomainException con un mensaje pensado para mostrar a la persona
     */
    public function texto(string $rutaAbsoluta, string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf' => $this->deUnPdf($rutaAbsoluta),
            'docx' => $this->deUnWord($rutaAbsoluta),
            default => throw new \DomainException('Solo se pueden leer archivos PDF o Word (.docx).'),
        };
    }

    private function deUnPdf(string $ruta): string
    {
        // El archivo viene de un usuario: se acota lo que el lector puede expandir en memoria
        // (flujos comprimidos que crecen de golpe) y cuánto se anida.
        $config = new Config;
        $config->setDecodeMemoryLimit(self::MAX_BYTES_EXPANDIDOS);
        $config->setMaxNestingDepth(50);
        $config->setRetainImageContent(false);

        try {
            $texto = mb_substr((new Parser([], $config))->parseFile($ruta)->getText(), 0, self::MAX_CARACTERES);
        } catch (\Throwable $e) {
            report($e);

            throw new \DomainException('No se pudo leer el PDF. Puede estar dañado o protegido con contraseña.');
        }

        if (mb_strlen(trim($texto)) < 30) {
            throw new \DomainException('Este PDF no tiene texto que se pueda leer (parece una foto o un escaneo). Completá los datos a mano.');
        }

        return $texto;
    }

    private function deUnWord(string $ruta): string
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new \DomainException('Este servidor todavía no puede leer archivos de Word. Subí el documento en PDF o completá los datos a mano.');
        }

        $zip = new \ZipArchive;
        if ($zip->open($ruta) !== true) {
            throw new \DomainException('No se pudo abrir el archivo de Word. Puede estar dañado.');
        }

        // Se mira el tamaño ya descomprimido ANTES de leerlo: un .docx chico puede expandirse
        // a gigabytes (zip bomb) y agotar la memoria del servidor.
        $datos = $zip->statName('word/document.xml');
        if ($datos === false) {
            $zip->close();

            throw new \DomainException('El archivo no parece un documento de Word válido.');
        }
        if ($datos['size'] > self::MAX_BYTES_EXPANDIDOS) {
            $zip->close();

            throw new \DomainException('El documento de Word es demasiado grande para leerlo. Completá los datos a mano.');
        }

        $xml = $zip->getFromName('word/document.xml', self::MAX_BYTES_EXPANDIDOS);
        $zip->close();

        if ($xml === false) {
            throw new \DomainException('El archivo no parece un documento de Word válido.');
        }

        // Cada párrafo y cada tabulación pasan a ser salto/espacio; el resto de las etiquetas se descarta.
        $xml = preg_replace(['#</w:p>#', '#<w:tab/>#', '#<w:br/>#'], ["\n", ' ', "\n"], $xml);
        $texto = mb_substr(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'), 0, self::MAX_CARACTERES);

        if (mb_strlen(trim($texto)) < 30) {
            throw new \DomainException('El documento de Word está vacío o casi vacío.');
        }

        return $texto;
    }
}
