<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class CrearResolucion extends Component
{
    public string $tipo;
    public string $modo = ''; // 'completo' o 'personalizado'

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
    ];

    // Plantilla para el modo personalizado
    public string $plantilla = '';
    
    // Cache para la vista previa
    private ?string $vistaPrevia = null;

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
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function guardarPersonalizado()
    {
        $this->validate([
            'plantilla' => 'required|min:10',
        ]);

        try {
            // Lógica para guardar la resolución personalizada
            // Ejemplo: ResolucionPersonalizadaService::crear($this->plantilla, $this->tipo);
            
            session()->flash('message', 'Resolución personalizada guardada exitosamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
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
            return View::make("prototipos.{$this->tipo}", $this->datos)->render();
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
        $dom = new \DOMDocument();
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
            // Usar translatedFormat con la localización en español
            return Carbon::parse($fecha)->locale('es')->translatedFormat('j \d\e F \d\e\l Y');
        } catch (\Exception $e) {
            return '';
        }
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