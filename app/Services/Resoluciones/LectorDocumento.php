<?php

namespace App\Services\Resoluciones;

use Smalot\PdfParser\Parser;

/** Saca el texto de un PDF (con texto) o de un Word .docx para poder analizarlo. */
class LectorDocumento
{
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
        try {
            $texto = (new Parser)->parseFile($ruta)->getText();
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

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new \DomainException('El archivo no parece un documento de Word válido.');
        }

        // Cada párrafo y cada tabulación pasan a ser salto/espacio; el resto de las etiquetas se descarta.
        $xml = preg_replace(['#</w:p>#', '#<w:tab/>#', '#<w:br/>#'], ["\n", ' ', "\n"], $xml);
        $texto = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');

        if (mb_strlen(trim($texto)) < 30) {
            throw new \DomainException('El documento de Word está vacío o casi vacío.');
        }

        return $texto;
    }
}
