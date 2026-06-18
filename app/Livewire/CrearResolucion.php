<?php

namespace App\Livewire;

use App\Models\Resolucion;
use App\Models\ResolucionArchivo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrearResolucion extends Component
{
    use WithFileUploads;

    public string $tipo;

    public string $modo = ''; // 'completo' o 'personalizado'

    // Archivos temporales para PDFs
    public $archivosPDF = [];

    // Usar array para mejor organización
    public array $datos = [
        'fecha_res' => '',
        'num_exp' => '',
        'num_res' => '',
        'manzana' => '',
        'lote' => '',
        'nombre_barrio' => '',
        'num_foja_solicitud' => '',
        'num_foja_informe' => '',
        'fecha_cancelacion' => '',
        'num_foja_darrd' => '',
        'num_foja_dictamen' => '',
        'nombre_titular' => '',
        'dni_titular' => '',
        'nombre_cotitular' => '',
        'dni_cotitular' => '',
        'num_escritura' => '',
        'fecha_escritura' => '',
        'nombre_escribano' => '',
        'num_foja_boleto' => '',
        'num_dictamen' => '',
        'num_foja_social' => '',
        'num_res_adjudicacion' => '',
        'nombre_titular_anterior' => '',
        'dni_titular_anterior' => '',
        'nombre_cotitular_anterior' => '',
        'dni_cotitular_anterior' => '',
        'fecha_boleto' => '',
        'num_res_reglamentacion' => '',
        'num_res_modificatoria' => '',
        'unidad' => '',
        'sector' => '',
        'estado_civil_titular_nuevo' => '',
        'estado_civil_cotitular_nuevo' => '',
        // Campos específicos de AplicarPagos
        'nombre_barrio' => '',
        'año_cuotas' => '',
        'fecha_primer_pago' => '',
        'codigo_pago' => '',
        'causante' => 'DEPARTAMENTO RECURSOS FINANCIEROS',
        'fecha_documento' => '',
        'jefe_computos' => '',
        'monto' => '',
        'cuotas_adeudadas' => '',
        'firma_1_nombre' => '',
        'firma_1_cargo' => '',
        'firma_2_nombre' => '',
        'firma_2_cargo' => '',
        // Campos específicos de Rectificación
        'texto_rectificacion' => '',
        'texto_adjudicacion' => '',
        'texto_donde_dice' => '',
        'tabla_datos' => '',
        'res_reglamentacion' => '185/2017',
        'res_modificatoria' => '779/2017',
        'fecha_res_adjudicacion' => '',
        'orden' => '',
        'num_foja_darrd' => '',
        'num_foja_dni' => '',
        'num_foja_resolucion' => '',
        'num_foja_darrd_conf' => '',
        'cantidad_viviendas' => '',
        'cantidad_letras' => '',
        // Campos específicos de Resición
        'nombre_emprendimiento' => '',
        'departamento' => '',
        'monto_deuda' => '',
        'fecha_deuda' => '',
        'fecha_dictamen' => '',
        // Campos específicos de Transferencia
        'nombre_plan' => '',
        'nombre_titular_nuevo' => '',
        'dni_titular_nuevo' => '',
        'fecha_nacimiento_titular' => '',
        'nombre_cotitular_nuevo' => '',
        'dni_cotitular_nuevo' => '',
        'fecha_nacimiento_cotitular' => '',
        'fecha_instrumento' => '',
        'num_foja_plan' => '',
        'num_foja_recursos' => '',
        'num_foja_promocion' => '',
        'num_foja_ratificacion' => '',
        'num_foja_regularizacion' => '',
        'num_cuotas' => '',
        'monto_cuota' => '',
        'tasa_interes' => '',
        'fecha_tasa' => '',
        'numero_tramite' => '',
        // Campos específicos de ReconocimientoCuotaPagadaDosVeces
        'quien_suscribe' => '',
        'dni_suscribe' => '',
        'cuota_sin_gastos' => '',
        'nro_ultima_cuota' => '',
        'vencimiento_ultima_cuota' => '',
        'plazo_total_plan' => '',
    ];

    // Plantilla para el modo personalizado
    public string $plantilla = '';

    // Cache para la vista previa
    private ?string $vistaPrevia = null;

    public function getDisplayNameProperty(): string
    {
        return match ($this->tipo) {
            'ReconocimientoCuotaPagadaDosVeces' => 'Reconocimiento de Cuota Pagada Dos Veces',
            'ReconocimientoCuotaPagadaNoCargada' => 'Recon. de cta. pagadas y no cargadas',
            default => $this->tipo,
        };
    }

    public function mount($tipo)
    {
        $this->tipo = $tipo;
        // Establecer la fecha inicial en el formato correcto
        $this->datos['fecha_res'] = date('Y-m-d');
    }

    public function usarCompleto()
    {
        $this->modo = 'completo';
        $this->resetearVistaPrevia();
    }

    public function usarPersonalizado()
    {
        $this->modo = 'personalizado';
        $this->cargarPlantillaEditable();
    }

    public function verPlantillaCompleta()
    {
        $this->modo = 'plantilla';
        $this->resetFormulario();
        $this->cargarPlantillaHtml();
    }

    public function cargarPlantillaHtml(): void
    {
        try {
            $datosEjemplo = [
                'fecha_res' => '[FECHA_RESOLUCIÓN]',
                'num_exp' => '[NÚMERO_EXPEDIENTE]',
                'num_res' => '[NÚMERO_RESOLUCIÓN]',
                'manzana' => '[MANZANA]',
                'lote' => '[LOTE]',
                'nombre_barrio' => '[NOMBRE_BARRIO]',
                'num_foja_solicitud' => '[FOJA_SOLICITUD]',
                'num_foja_informe' => '[FOJA_INFORME]',
                'fecha_cancelacion' => '[FECHA_CANCELACIÓN]',
                'num_foja_darrd' => '[FOJA_DARRD]',
                'num_foja_dictamen' => '[FOJA_DICTAMEN]',
                'nombre_titular' => '[NOMBRE_TITULAR]',
                'dni_titular' => '[DNI_TITULAR]',
                'nombre_cotitular' => '[NOMBRE_COTITULAR]',
                'dni_cotitular' => '[DNI_COTITULAR]',
                'num_escritura' => '[NÚMERO_ESCRITURA]',
                'fecha_escritura' => '[FECHA_ESCRITURA]',
                'nombre_escribano' => '[NOMBRE_ESCRIBANO]',
                // Campos específicos de Resciciones
                'nombre_emprendimiento' => '[NOMBRE DEL EMPRENDIMIENTO]',
                'departamento' => '[DEPARTAMENTO]',
                'monto_deuda' => '[MONTO DE DEUDA]',
                'fecha_deuda' => '[FECHA DEUDA]',
                'fecha_dictamen' => '[FECHA DICTAMEN]',
                'num_dictamen' => '[NÚMERO DICTAMEN]',
                'num_res_adjudicacion' => '[NÚMERO RESOLUCIÓN ADJUDICACIÓN]',
                // Campos específicos de Transferencia
                'nombre_plan' => '[NOMBRE DEL PLAN]',
                'nombre_titular_nuevo' => '[NOMBRE NUEVO TITULAR]',
                'dni_titular_nuevo' => '[DNI NUEVO TITULAR]',
                'fecha_nacimiento_titular' => '[FECHA NACIMIENTO TITULAR]',
                'nombre_cotitular_nuevo' => '[NOMBRE NUEVO COTITULAR]',
                'dni_cotitular_nuevo' => '[DNI NUEVO COTITULAR]',
                'fecha_nacimiento_cotitular' => '[FECHA NACIMIENTO COTITULAR]',
                'fecha_instrumento' => '[FECHA INSTRUMENTO]',
                'num_foja_plan' => '[FOJA PLAN]',
                'num_foja_recursos' => '[FOJA RECURSOS]',
                'num_foja_promocion' => '[FOJA PROMOCIÓN]',
                'num_foja_ratificacion' => '[FOJA RATIFICACIÓN]',
                'num_foja_regularizacion' => '[FOJA REGULARIZACIÓN]',
                'num_cuotas' => '[NÚMERO DE CUOTAS]',
                'monto_cuota' => '[MONTO CUOTA]',
                'tasa_interes' => '[TASA DE INTERÉS]',
                'fecha_tasa' => '[FECHA TASA]',
                // Campos específicos de Transferencia-Cancelacion
                'num_res_reglamentacion' => '[NÚMERO RESOLUCIÓN REGLAMENTACIÓN]',
                'num_res_modificatoria' => '[NÚMERO RESOLUCIÓN MODIFICATORIA]',
                'unidad' => '[UNIDAD]',
                'sector' => '[SECTOR]',
                'estado_civil_titular_nuevo' => '[ESTADO CIVIL]',
                'estado_civil_cotitular_nuevo' => '[ESTADO CIVIL]',
                // Campos específicos de ReconocimientoCuotaPagadaDosVeces
                'quien_suscribe' => '[EL/QUE SUSCRIBE]',
                'dni_suscribe' => '[DNI]',
                'cuota_sin_gastos' => '[CUOTA S/GASTOS]',
                'nro_ultima_cuota' => '[NRO ÚLTIMA CUOTA]',
                'vencimiento_ultima_cuota' => now()->addMonth()->format('Y-m-d'),
                'plazo_total_plan' => '[PLAZO TOTAL PLAN]',
            ];

            $datosConFunciones = array_merge($datosEjemplo, [
                'formatearFecha' => [$this, 'formatearFecha'],
                'formatearFechaLarga' => [$this, 'formatearFechaLarga'],
                'formatearMoneda' => [$this, 'formatearMoneda'],
                'num2letras' => [$this, 'num2letras'],
            ]);

            $this->plantilla = View::make("prototipos.{$this->tipo}", $datosConFunciones)->render();

        } catch (\Exception $e) {
            $this->plantilla = "Error al cargar la plantilla: {$e->getMessage()}";
        }
    }

    public function cargarPlantillaConDatos(): void
    {
        try {
            $contenidoHTML = View::make("prototipos.{$this->tipo}", $this->datos)->render();
            $this->plantilla = $this->convertirHTMLaTexto($contenidoHTML);
        } catch (\Exception $e) {
            $this->plantilla = "Error al cargar la plantilla: {$e->getMessage()}";
        }
    }

    public function copiarPlantilla()
    {
        try {
            $html = $this->getVistaPrevia();
            $this->vistaPrevia = null;
            $this->dispatch('copiado-al-portapapeles', $html);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al copiar: '.$e->getMessage());
        }
    }

    public function guardarPlantilla()
    {
        $this->validate([
            'plantilla' => 'required|min:20',
        ]);

        try {
            // Generar número de trámite único
            $numeroTramite = $this->datos['numero_tramite'] ?? $this->generarNumeroTramite();

            // Verificar si el número ya existe, si existe generar otro
            while (\App\Models\Resolucion::where('numero_exp', $numeroTramite)->exists()) {
                $numeroTramite = $this->generarNumeroTramite();
            }

            // Crear la resolución
            $resolucion = Resolucion::create([
                'numero_exp' => $numeroTramite,
                'numero_resolucion' => $this->datos['num_res'] ?? null,
                'fecha' => $this->datos['fecha_res'] ?? now()->toDateString(),
                'cod_barrio' => $this->datos['manzana'] ?? null,
                'cod_casa' => $this->datos['lote'] ?? null,
                'plantilla' => $this->plantilla,
            ]);

            // Guardar los PDFs si hay archivos cargados
            if (! empty($this->archivosPDF)) {
                foreach ($this->archivosPDF as $archivo) {
                    $nombreArchivo = uniqid('resolucion_').'_'.$archivo->getClientOriginalName();
                    $ruta = $archivo->storeAs('resoluciones', $nombreArchivo, 'public');

                    ResolucionArchivo::create([
                        'resolucion_id' => $resolucion->id,
                        'nombre_original' => $archivo->getClientOriginalName(),
                        'nombre_archivo' => $nombreArchivo,
                        'ruta' => $ruta,
                        'tipo' => $archivo->getMimeType(),
                        'tamano' => $archivo->getSize(),
                    ]);
                }
            }

            // Limpiar archivos temporales
            $this->archivosPDF = [];

            session()->flash('message', 'Resolución guardada exitosamente con '.count($this->archivosPDF).' archivo(s)');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: '.$e->getMessage());
        }
    }

    public function removeArchivo($index)
    {
        if (isset($this->archivosPDF[$index])) {
            // Eliminar archivo físico si existe
            $sesionArchivos = session()->get('archivos_pdf_resolucion', []);
            if (isset($sesionArchivos[$index])) {
                Storage::disk('public')->delete($sesionArchivos[$index]);
                unset($sesionArchivos[$index]);
                $sesionArchivos = array_values($sesionArchivos);
                session()->put('archivos_pdf_resolucion', $sesionArchivos);
            }

            unset($this->archivosPDF[$index]);
            unset($this->archivosUrl[$index]);
            $this->archivosPDF = array_values($this->archivosPDF);
            $this->archivosUrl = array_values($this->archivosUrl);
        }
    }

    public $tempArchivos = [];

    public $pdfSeleccionado = null;

    public $mostrarModalPdf = false;

    public $archivosUrl = [];

    public function updatedTempArchivos()
    {
        if (! empty($this->tempArchivos)) {
            $sesionArchivos = session()->get('archivos_pdf_resolucion', []);

            foreach ($this->tempArchivos as $archivo) {
                // Guardar en storage público temporalmente
                $nombreArchivo = 'temp_'.uniqid().'_'.$archivo->getClientOriginalName();
                $ruta = $archivo->storeAs('temp', $nombreArchivo, 'public');

                $this->archivosPDF[] = $archivo;
                $this->archivosUrl[] = route('resoluciones.descargarPdf', ['index' => count($sesionArchivos)]);

                // Guardar en sesión para la ruta
                $sesionArchivos[] = $ruta;
            }

            session()->put('archivos_pdf_resolucion', $sesionArchivos);
            $this->tempArchivos = [];
        }
    }

    public function agregarArchivos()
    {
        // Este método queda vacío porque usamos tempArchivos con wire:model
    }

    public function abrirModalPdf($index)
    {
        $this->pdfSeleccionado = $index;
        $this->mostrarModalPdf = true;
    }

    public function cerrarModalPdf()
    {
        $this->pdfSeleccionado = null;
        $this->mostrarModalPdf = false;
    }

    public function guardar()
    {
        $this->validate($this->rules(), $this->messages());

        try {
            // Lógica para guardar la resolución
            // Ejemplo: ResolucionService::crear($this->datos, $this->tipo);

            session()->flash('message', 'Resolución guardada exitosamente');

            // Opcional: redireccionar o limpiar formulario
            $this->resetFormulario();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: '.$e->getMessage());
        }
    }

    public function guardarPersonalizado()
    {
        $this->validate([
            'plantilla' => 'required|min:20',
        ]);

        try {
            session()->flash('message', 'Resolución guardada exitosamente');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: '.$e->getMessage());
        }
    }

    public function updated($propertyName)
    {
        // Resetear cache cuando cambien los datos
        if (str_starts_with($propertyName, 'datos.')) {
            $this->resetearVistaPrevia();
        }
    }

    public function getVistaPrevia(): string
    {
        if ($this->vistaPrevia === null) {
            $this->vistaPrevia = $this->generarVistaPrevia();
        }

        return $this->vistaPrevia;
    }

    private function generarVistaPrevia(): string
    {
        try {
            $datos = array_merge($this->datos, [
                'formatearFecha' => [$this, 'formatearFecha'],
                'formatearFechaLarga' => [$this, 'formatearFechaLarga'],
                'formatearMoneda' => [$this, 'formatearMoneda'],
                'num2letras' => [$this, 'num2letras'],
                'numero_tramite' => $this->generarNumeroTramite(),
            ]);

            return View::make("prototipos.{$this->tipo}", $datos)->render();
        } catch (\Exception $e) {
            return "<div class='text-red-500'>Error al generar vista previa: {$e->getMessage()}</div>";
        }
    }

    private function resetearVistaPrevia(): void
    {
        $this->vistaPrevia = null;
    }

    private function resetFormulario(): void
    {
        $this->datos = array_fill_keys(array_keys($this->datos), '');
        // Restaurar el valor por defecto de la fecha, manteniéndolo en Y-m-d
        $this->datos['fecha_res'] = date('Y-m-d');
        $this->resetearVistaPrevia();
    }

    protected function cargarPlantillaEditable(): void
    {
        try {
            // Datos de ejemplo para generar la plantilla
            $datosEjemplo = [
                'fecha_res' => '[FECHA_RESOLUCIÓN]',
                'num_exp' => '[NÚMERO_EXPEDIENTE]',
                'num_res' => '[NÚMERO_RESOLUCIÓN]',
                'manzana' => '[MANZANA]',
                'lote' => '[LOTE]',
                'nombre_barrio' => '[NOMBRE_BARRIO]',
                'num_foja_solicitud' => '[FOJA_SOLICITUD]',
                'num_foja_informe' => '[FOJA_INFORME]',
                'fecha_cancelacion' => '[FECHA_CANCELACIÓN]',
                'num_foja_darrd' => '[FOJA_DARRD]',
                'num_foja_dictamen' => '[FOJA_DICTAMEN]',
                'nombre_titular' => '[NOMBRE_TITULAR]',
                'dni_titular' => '[DNI_TITULAR]',
                'nombre_cotitular' => '[NOMBRE_COTITULAR]',
                'dni_cotitular' => '[DNI_COTITULAR]',
                'num_escritura' => '[NÚMERO_ESCRITURA]',
                'fecha_escritura' => '[FECHA_ESCRITURA]',
                'nombre_escribano' => '[NOMBRE_ESCRIBANO]',
            ];

            // Renderizar el prototipo con datos placeholder
            $contenidoHTML = View::make("prototipos.{$this->tipo}", $datosEjemplo)->render();

            // Convertir a texto plano manteniendo estructura
            $this->plantilla = $this->convertirHTMLaTexto($contenidoHTML);

        } catch (\Exception $e) {
            $this->plantilla = "Error al cargar la plantilla: {$e->getMessage()}";
        }
    }

    private function convertirHTMLaTexto(string $html): string
    {
        // Usar DOMDocument para un procesamiento más robusto
        $dom = new \DOMDocument;
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Remover scripts y styles
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//script | //style') as $node) {
            $node->parentNode->removeChild($node);
        }

        // Convertir a texto manteniendo estructura
        $texto = $dom->textContent;

        // Limpiar espacios extra
        $texto = preg_replace('/\s+/', ' ', $texto);

        // Agregar saltos de línea para mejor legibilidad
        $texto = str_replace(['. ', ':', 'Resolución Nº'], [".\n\n", ":\n", "\nResolución Nº"], $texto);

        return trim($texto);
    }

    /**
     * Helper para formatear fechas a d-m-Y.
     * Retorna la fecha en formato d-m-Y o un string vacío si la fecha no es válida.
     */
    public function formatearFecha($fecha): string
    {
        if (empty($fecha)) {
            return '';
        }
        try {
            return Carbon::parse($fecha)->format('d-m-Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    public function formatearFechaLarga($fecha): string
    {
        if (empty($fecha)) {
            return '';
        }
        try {
            return Carbon::parse($fecha)->locale('es')->translatedFormat('j \d\e F \d\e\l Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    public function formatearMoneda($monto): string
    {
        if (empty($monto)) {
            return '';
        }
        try {
            return number_format((float) str_replace(['$', ','], '', $monto), 2, ',', '.');
        } catch (\Exception $e) {
            return $monto;
        }
    }

    public function num2letras($numero): string
    {
        if (empty($numero)) {
            return '';
        }
        $numero = str_replace(['$', ',', ' '], '', $numero);
        $numero = (float) $numero;
        if ($numero == 0) {
            return 'CERO';
        }

        $integerPart = floor($numero);
        $decimalPart = round(($numero - $integerPart) * 100);

        $letters = $this->convertNumberToLetters($integerPart);
        $result = $letters.' con '.str_pad($decimalPart, 2, '0', STR_PAD_LEFT).'/100';

        return $result;
    }

    private function convertNumberToLetters(int $number): string
    {
        if ($number < 0) {
            return 'MENOS '.$this->convertNumberToLetters(-$number);
        }
        if ($number == 0) {
            return '';
        }
        if ($number < 20) {
            $units = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];

            return $units[$number];
        }
        if ($number < 30) {
            return 'VEINTI'.($number == 21 ? 'UNO' : $this->convertNumberToLetters($number - 20));
        }
        if ($number < 40) {
            return 'TREINTA Y '.$this->convertNumberToLetters($number - 30);
        }
        if ($number < 50) {
            return 'CUARENTA Y '.$this->convertNumberToLetters($number - 40);
        }
        if ($number < 60) {
            return 'CINCUENTA Y '.$this->convertNumberToLetters($number - 50);
        }
        if ($number < 70) {
            return 'SESENTA Y '.$this->convertNumberToLetters($number - 60);
        }
        if ($number < 80) {
            return 'SETENTA Y '.$this->convertNumberToLetters($number - 70);
        }
        if ($number < 90) {
            return 'OCHENTA Y '.$this->convertNumberToLetters($number - 80);
        }
        if ($number < 100) {
            return 'NOVENTA Y '.$this->convertNumberToLetters($number - 90);
        }
        if ($number < 200) {
            return 'CIENTO '.$this->convertNumberToLetters($number - 100);
        }
        if ($number < 300) {
            return 'DOSCIENTOS '.$this->convertNumberToLetters($number - 200);
        }
        if ($number < 400) {
            return 'TRESCIENTOS '.$this->convertNumberToLetters($number - 300);
        }
        if ($number < 500) {
            return 'CUATROCIENTOS '.$this->convertNumberToLetters($number - 400);
        }
        if ($number < 600) {
            return 'QUINIENTOS '.$this->convertNumberToLetters($number - 500);
        }
        if ($number < 700) {
            return 'SEISCIENTOS '.$this->convertNumberToLetters($number - 600);
        }
        if ($number < 800) {
            return 'SETECIENTOS '.$this->convertNumberToLetters($number - 700);
        }
        if ($number < 900) {
            return 'OCHOCIENTOS '.$this->convertNumberToLetters($number - 800);
        }
        if ($number < 1000) {
            return 'NOVECIENTOS '.$this->convertNumberToLetters($number - 900);
        }
        if ($number < 2000) {
            return 'MIL '.$this->convertNumberToLetters($number - 1000);
        }
        if ($number < 1000000) {
            $thousands = floor($number / 1000);
            $remainder = $number % 1000;
            $thousandsWord = $thousands == 1 ? 'MIL' : $this->convertNumberToLetters($thousands);

            return $thousandsWord.($remainder > 0 ? ' '.$this->convertNumberToLetters($remainder) : '');
        }
        if ($number < 2000000) {
            return 'UN MILLÓN '.$this->convertNumberToLetters($number - 1000000);
        }

        $millions = floor($number / 1000000);
        $remainder = $number % 1000000;

        return $this->convertNumberToLetters($millions).' MILLONES'.($remainder > 0 ? ' '.$this->convertNumberToLetters($remainder) : '');
    }

    public function generarNumeroTramite(): string
    {
        $año = date('Y');
        do {
            $numero = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $numeroTramite = "{$numero}/440-{$año}";
        } while (\App\Models\Resolucion::where('numero_exp', $numeroTramite)->exists());

        return $numeroTramite;
    }

    protected function rules(): array
    {
        return [
            'datos.fecha_res' => 'required|date',
            'datos.num_exp' => 'required|string|max:50',
            'datos.num_res' => 'required|string|max:50',
            'datos.manzana' => 'nullable|string|max:10',
            'datos.lote' => 'nullable|string|max:10',
            'datos.nombre_barrio' => 'nullable|string|max:255',
            'datos.num_foja_solicitud' => 'nullable|string|max:50',
            'datos.num_foja_informe' => 'nullable|string|max:50',
            'datos.fecha_cancelacion' => 'nullable|date',
            'datos.num_foja_darrd' => 'nullable|string|max:50',
            'datos.num_foja_dictamen' => 'nullable|string|max:50',
            'datos.nombre_titular' => 'required|string|max:255',
            'datos.dni_titular' => 'required|string|max:20',
            'datos.nombre_cotitular' => 'nullable|string|max:255',
            'datos.dni_cotitular' => 'nullable|string|max:20',
            'datos.num_escritura' => 'nullable|string|max:50',
            'datos.fecha_escritura' => 'nullable|date',
            'datos.nombre_escribano' => 'nullable|string|max:255',
            // Campos específicos de Resciciones
            'datos.nombre_emprendimiento' => 'nullable|string|max:255',
            'datos.departamento' => 'nullable|string|max:100',
            'datos.monto_deuda' => 'nullable|numeric|min:0',
            'datos.fecha_deuda' => 'nullable|date',
            'datos.fecha_dictamen' => 'nullable|date',
            'datos.num_res_adjudicacion' => 'nullable|string|max:50',
            // Campos específicos de Transferencia
            'datos.nombre_plan' => 'nullable|string|max:255',
            'datos.nombre_titular_nuevo' => 'nullable|string|max:255',
            'datos.dni_titular_nuevo' => 'nullable|string|max:20',
            'datos.fecha_nacimiento_titular' => 'nullable|date',
            'datos.nombre_cotitular_nuevo' => 'nullable|string|max:255',
            'datos.dni_cotitular_nuevo' => 'nullable|string|max:20',
            'datos.fecha_nacimiento_cotitular' => 'nullable|date',
            'datos.fecha_instrumento' => 'nullable|date',
            'datos.num_foja_plan' => 'nullable|string|max:50',
            'datos.num_foja_recursos' => 'nullable|string|max:50',
            'datos.num_foja_promocion' => 'nullable|string|max:50',
            'datos.num_foja_ratificacion' => 'nullable|string|max:50',
            'datos.num_foja_regularizacion' => 'nullable|string|max:50',
            'datos.num_cuotas' => 'nullable|integer|min:1',
            'datos.monto_cuota' => 'nullable|numeric|min:0',
            'datos.tasa_interes' => 'nullable|numeric|min:0',
            'datos.fecha_tasa' => 'nullable|date',
            // Campos específicos de Transferencia-Cancelacion
            'datos.num_res_reglamentacion' => 'nullable|string|max:50',
            'datos.num_res_modificatoria' => 'nullable|string|max:50',
            'datos.unidad' => 'nullable|string|max:20',
            'datos.sector' => 'nullable|string|max:20',
            'datos.estado_civil_titular_nuevo' => 'nullable|string|max:30',
            'datos.estado_civil_cotitular_nuevo' => 'nullable|string|max:30',
            // Campos específicos de Rectificación
            'datos.texto_rectificacion' => 'nullable|string',
            'datos.texto_adjudicacion' => 'nullable|string',
            'datos.texto_donde_dice' => 'nullable|string',
            'datos.tabla_datos' => 'nullable|string',
            'datos.res_reglamentacion' => 'nullable|string|max:20',
            'datos.res_modificatoria' => 'nullable|string|max:20',
            'datos.fecha_res_adjudicacion' => 'nullable|date',
            'datos.orden' => 'nullable|string|max:10',
            'datos.num_foja_darrd' => 'nullable|string|max:20',
            'datos.num_foja_dni' => 'nullable|string|max:20',
            'datos.num_foja_resolucion' => 'nullable|string|max:20',
            'datos.num_foja_darrd_conf' => 'nullable|string|max:20',
            'datos.cantidad_viviendas' => 'nullable|string|max:20',
            'datos.cantidad_letras' => 'nullable|string|max:100',
            // Campos específicos de AplicarPagos
            'datos.nombre_barrio' => 'nullable|string|max:255',
            'datos.año_cuotas' => 'nullable|integer|min:2020|max:2100',
            'datos.fecha_primer_pago' => 'nullable|date',
        ];
    }

    protected function messages(): array
    {
        return [
            'datos.fecha_res.required' => 'La fecha de resolución es obligatoria',
            'datos.num_exp.required' => 'El número de expediente es obligatorio',
            'datos.num_res.required' => 'El número de resolución es obligatorio',
            'datos.nombre_titular.required' => 'El nombre del titular es obligatorio',
            'datos.dni_titular.required' => 'El DNI del titular es obligatorio',
            'datos.*.max' => 'El campo excede la longitud máxima permitida',
            'datos.*.date' => 'El campo debe ser una fecha válida',
        ];
    }

    public function render()
    {
        return view('livewire.crear-resolucion');
    }
}
