<?php

namespace App\Livewire;

use App\Models\Resolucion;
use App\Models\ResolucionArchivo;
use App\Services\Resoluciones\AnalizadorResolucion;
use App\Services\Resoluciones\LectorDocumento;
use App\Traits\AuthorizesOficina;
use App\Traits\NotificaUi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mews\Purifier\Facades\Purifier;

class CrearResolucion extends Component
{
    use AuthorizesOficina;
    use NotificaUi;
    use WithFileUploads;

    // Locked: se usa para armar el nombre de la vista (prototipos.{tipo}).
    #[Locked]
    public string $tipo;

    public string $modo = ''; // 'completo' o 'personalizado'

    // Archivos temporales para PDFs
    public $archivosPDF = [];

    // Documento subido solo para leerlo y autocompletar (no se guarda con la resolución).
    public $documentoAnalizar = null;

    // Tipo detectado distinto al de esta pantalla: ['tipo' => ..., 'nombre' => ...].
    #[Locked]
    public ?array $sugerencia = null;

    /** Tipos de resolución con formulario (los mismos nombres que prototipos.*). */
    public const TIPOS = [
        'Cancelaciones', 'Resciciones', 'Transferencias', 'Transferencia-Cancelacion',
        'Rectificacion', 'AplicarPagos', 'ReconocimientoCuotaPagadaDosVeces', 'ReconocimientoCuotaPagadaNoCargada',
    ];

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
        // Campos específicos de AplicarPagos (nombre_barrio ya está arriba)
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
        $this->autorizarPermiso('resolucion_editar');

        // Solo letras y guiones: evita que un tipo con '.' o '/' apunte a otra vista.
        abort_unless(is_string($tipo) && preg_match('/^[\pL\-]+$/u', $tipo), 404);

        $this->tipo = $tipo;
        // Establecer la fecha inicial en el formato correcto
        $this->datos['fecha_res'] = date('Y-m-d');

        // Viene de "Autocompletar": precarga los datos del documento y abre el formulario.
        $sugerida = session('sugerencia_resolucion');
        if (is_array($sugerida) && ($sugerida['tipo'] ?? null) === $tipo) {
            session()->forget('sugerencia_resolucion');
            $cargados = $this->aplicarDatosDetectados($sugerida['datos'] ?? []);
            $this->modo = 'completo';
            $this->toast('info', "Completé {$cargados} campos con los datos del documento. Revisalos antes de guardar y volvé a adjuntar el archivo si lo necesitás.", 8000);
        }
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
        $this->plantilla = $this->plantillaCompletaHtml();
    }

    /**
     * HTML del prototipo con datos de ejemplo. La vista lo imprime sin escapar, por
     * eso se genera en el servidor en vez de leer $plantilla (que el cliente puede
     * modificar vía wire:model).
     */
    public function plantillaCompletaHtml(): string
    {
        try {
            $datosConFunciones = array_merge($this->getDatosEjemplo(), [
                'formatearFecha' => 'formatearFecha',
                'formatearFechaLarga' => 'formatearFechaLarga',
                'formatearMoneda' => 'formatearMoneda',
                'num2letras' => 'num2letras',
            ]);

            return View::make("prototipos.{$this->tipo}", $datosConFunciones)->render();
        } catch (\Exception $e) {
            return e($this->mensajePlantillaNoDisponible());
        }
    }

    private function mensajePlantillaNoDisponible(): string
    {
        return "Todavía no hay una plantilla completa para \"{$this->tipo}\". Podés escribir el contenido en el modo \"Personalizado\".";
    }

    private function getDatosEjemplo(): array
    {
        return [
            'fecha_res' => '[FECHA_RESOLUCIÓN]',
            'num_exp' => '[NÚMERO_EXPEDIENTE]',
            'num_res' => '[NÚMERO_RESOLUCIÓN]',
            'numero_tramite' => '[NÚMERO_TRÁMITE]',
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
            // Campos específicos de Transferencia y Transferencia-Cancelacion
            'nombre_plan' => '[NOMBRE DEL PLAN]',
            'nombre_titular_anterior' => '[NOMBRE TITULAR ANTERIOR]',
            'dni_titular_anterior' => '[DNI TITULAR ANTERIOR]',
            'nombre_cotitular_anterior' => '[NOMBRE COTITULAR ANTERIOR]',
            'dni_cotitular_anterior' => '[DNI COTITULAR ANTERIOR]',
            'num_foja_boleto' => '[FOJA BOLETO]',
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
            'num_res_reglamentacion' => '[NÚMERO RESOLUCIÓN REGLAMENTACIÓN]',
            'num_res_modificatoria' => '[NÚMERO RESOLUCIÓN MODIFICATORIA]',
            'unidad' => '[UNIDAD]',
            'sector' => '[SECTOR]',
            'estado_civil_titular_nuevo' => '[ESTADO CIVIL]',
            'estado_civil_cotitular_nuevo' => '[ESTADO CIVIL]',
            // Campos específicos de Rectificación
            'texto_rectificacion' => '',
            'texto_adjudicacion' => '',
            'texto_donde_dice' => '',
            'tabla_datos' => '',
            'res_reglamentacion' => '185/2017',
            'res_modificatoria' => '779/2017',
            'fecha_res_adjudicacion' => '[FECHA RESOLUCIÓN ADJUDICACIÓN]',
            'orden' => '[ORDEN]',
            'num_foja_dni' => '[FOJA DNI]',
            'num_foja_resolucion' => '[FOJA RESOLUCIÓN]',
            'num_foja_darrd_conf' => '[FOJA DARRD CONF]',
            'cantidad_viviendas' => '[CANTIDAD]',
            'cantidad_letras' => '[CANTIDAD EN LETRAS]',
            // Campos específicos de ReconocimientoCuotaPagadaDosVeces
            'quien_suscribe' => '[EL/QUE SUSCRIBE]',
            'dni_suscribe' => '[DNI]',
            'cuota_sin_gastos' => '[CUOTA S/GASTOS]',
            'nro_ultima_cuota' => '[NRO ÚLTIMA CUOTA]',
            'vencimiento_ultima_cuota' => now()->addMonth()->format('Y-m-d'),
            'plazo_total_plan' => '[PLAZO TOTAL PLAN]',
            // Campos específicos de AplicarPagos
            'año_cuotas' => '[AÑO]',
            'fecha_primer_pago' => '[FECHA PRIMER PAGO]',
            'codigo_pago' => '[CÓDIGO PAGO]',
            'causante' => 'DEPARTAMENTO RECURSOS FINANCIEROS',
            'fecha_documento' => '[FECHA DOCUMENTO]',
            'jefe_computos' => '[JEFE CÓMPUTOS]',
            'monto' => '[MONTO]',
            'cuotas_adeudadas' => '[CUOTAS ADEUDADAS]',
            'firma_1_nombre' => '[FIRMA 1]',
            'firma_1_cargo' => '[CARGO 1]',
            'firma_2_nombre' => '[FIRMA 2]',
            'firma_2_cargo' => '[CARGO 2]',
        ];
    }

    // Botón "Guardar resolución" del modo 'plantilla': mismo guardado que el personalizado.
    public function guardarPlantilla()
    {
        $this->guardarPersonalizado();
    }

    private function siguienteNumeroTramite(): string
    {
        $numeroTramite = $this->datos['numero_tramite'] ?? $this->generarNumeroTramite();

        while (\App\Models\Resolucion::where('numero_exp', $numeroTramite)->exists()) {
            $numeroTramite = $this->generarNumeroTramite();
        }

        return $numeroTramite;
    }

    private function persistirResolucion(
        string $numeroExp,
        ?string $numeroResolucion,
        string $fecha,
        ?string $codBarrio,
        ?string $codCasa,
        string $plantilla
    ): Resolucion {
        // Sin oficina la resolución quedaría visible y editable para todas las
        // oficinas (ver AuthorizesOficina::autorizarResolucion()).
        $oficinaId = auth()->user()->oficinaAsignadaId();
        if (! $oficinaId) {
            throw new \DomainException('No tenés una oficina asignada. Pedile a un administrador que te asigne una.');
        }

        $rutasGuardadas = [];

        try {
            $resolucion = DB::transaction(function () use ($oficinaId, $numeroExp, $numeroResolucion, $fecha, $codBarrio, $codCasa, $plantilla, &$rutasGuardadas) {
                $resolucion = Resolucion::create([
                    'oficina_id' => $oficinaId,
                    'numero_exp' => $numeroExp,
                    'numero_resolucion' => $numeroResolucion,
                    'fecha' => $fecha,
                    // cod_barrio/cod_casa son integer: los tipos que no usan manzana/lote
                    // llegan como '' (string vacío) y no como null, lo que rompe el insert
                    // en modo estricto de MySQL.
                    'cod_barrio' => $codBarrio !== '' ? $codBarrio : null,
                    'cod_casa' => $codCasa !== '' ? $codCasa : null,
                    // Sanitizado antes de guardar: 'plantilla' llega del editor Quill en modo
                    // personalizado (HTML libre del usuario) y podría contener HTML/JS
                    // malicioso si se muestra sin escapar a otros usuarios/oficinas.
                    'plantilla' => Purifier::clean($plantilla),
                ]);

                foreach ($this->archivosPDF as $archivo) {
                    // Nombre generado a partir de la extensión, no del nombre original del
                    // cliente (ver updatedTempArchivos(): mismo riesgo de path traversal).
                    // Disco privado ('local'): los PDFs tienen DNI y datos personales; se
                    // sirven solo por la ruta autorizada 'resoluciones.archivo'.
                    $nombreArchivo = uniqid('resolucion_').'.'.$archivo->getClientOriginalExtension();
                    $ruta = $archivo->storeAs('resoluciones', $nombreArchivo, 'local');
                    $rutasGuardadas[] = $ruta;

                    ResolucionArchivo::create([
                        'resolucion_id' => $resolucion->id,
                        'nombre_original' => $archivo->getClientOriginalName(),
                        'nombre_archivo' => $nombreArchivo,
                        'ruta' => $ruta,
                        'tipo' => $archivo->getMimeType(),
                        'tamano' => $archivo->getSize(),
                    ]);
                }

                return $resolucion;
            });
        } catch (\Throwable $e) {
            // La transacción deshace la base, pero no los archivos ya copiados.
            Storage::disk('local')->delete($rutasGuardadas);

            throw $e;
        }

        $this->limpiarArchivosTemporales();

        return $resolucion;
    }

    private function limpiarArchivosTemporales(): void
    {
        Storage::disk('local')->delete(session()->pull('archivos_pdf_resolucion', []));
        $this->archivosPDF = [];
    }

    private function flashErrorGuardado(\Throwable $e): void
    {
        // DomainException = error de negocio con mensaje pensado para el usuario;
        // el resto se loguea y se muestra un mensaje genérico (sin SQL ni rutas).
        if ($e instanceof \DomainException) {
            $this->toast('error', $e->getMessage(), 6000);

            return;
        }

        report($e);
        $this->toast('error', 'Ocurrió un error al guardar la resolución. Intentá de nuevo.', 6000);
    }

    public function removeArchivo($index)
    {
        if (isset($this->archivosPDF[$index])) {
            // Eliminar archivo físico si existe
            $sesionArchivos = session()->get('archivos_pdf_resolucion', []);
            if (isset($sesionArchivos[$index])) {
                Storage::disk('local')->delete($sesionArchivos[$index]);
                unset($sesionArchivos[$index]);
                $sesionArchivos = array_values($sesionArchivos);
                session()->put('archivos_pdf_resolucion', $sesionArchivos);
            }

            // Mismo reindexado que la sesión: la vista previa usa el índice para
            // pedir el archivo a 'resoluciones.descargarPdf'.
            unset($this->archivosPDF[$index]);
            $this->archivosPDF = array_values($this->archivosPDF);
        }
    }

    public $tempArchivos = [];

    public function updatedTempArchivos()
    {
        if (! empty($this->tempArchivos)) {
            // Sin esto, se podía subir cualquier tipo de archivo (incluido .php/.html)
            // al disco público: mismo riesgo que un upload sin restricción de tipo.
            try {
                $this->validarConAviso(['tempArchivos.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048']);
            } catch (ValidationException $e) {
                $this->tempArchivos = [];

                throw $e;
            }

            foreach ($this->tempArchivos as $archivo) {
                $this->adjuntar($archivo);
            }

            $this->tempArchivos = [];
        }
    }

    /** Deja el archivo adjunto a la resolución que se está creando (se guarda con ella). */
    private function adjuntar($archivo): void
    {
        $sesionArchivos = session()->get('archivos_pdf_resolucion', []);

        // Copia temporal en disco privado para la vista previa. Nombre generado
        // a partir de la extensión ya validada, no del nombre original del
        // cliente: ese nombre podía traer '../' y escribir fuera de 'temp'.
        $nombreArchivo = 'temp_'.uniqid().'.'.$archivo->getClientOriginalExtension();
        $sesionArchivos[] = $archivo->storeAs('temp', $nombreArchivo, 'local');

        $this->archivosPDF[] = $archivo;

        session()->put('archivos_pdf_resolucion', $sesionArchivos);
    }

    /**
     * Sube un PDF o Word, detecta de qué tipo de resolución es y precarga el formulario.
     * Es una sugerencia: nunca guarda; la persona revisa los datos antes de guardar.
     */
    public function updatedDocumentoAnalizar(): void
    {
        $archivo = $this->documentoAnalizar;
        $this->sugerencia = null;

        try {
            $this->validarConAviso(['documentoAnalizar' => 'required|file|mimes:pdf,docx|max:5120']);
        } catch (ValidationException $e) {
            $this->documentoAnalizar = null;

            throw $e;
        }

        try {
            $texto = app(LectorDocumento::class)->texto($archivo->getRealPath(), $archivo->getClientOriginalExtension());
        } catch (\DomainException $e) {
            $this->toast('warning', $e->getMessage(), 7000);

            return;
        }

        $resultado = app(AnalizadorResolucion::class)->analizar($texto);

        if (! $resultado['tipo']) {
            $this->toast('warning', 'No pude reconocer qué tipo de resolución es. Elegí el formulario y completá los datos a mano.', 7000);

            return;
        }

        if ($resultado['tipo'] !== $this->tipo) {
            // Los datos viajan por sesión al formulario correcto (no por el cliente).
            session()->put('sugerencia_resolucion', $resultado);
            $this->sugerencia = ['tipo' => $resultado['tipo'], 'nombre' => self::nombreDeTipo($resultado['tipo'])];
            $this->toast('info', 'Este documento parece ser de otro tipo: '.self::nombreDeTipo($resultado['tipo']).'.', 6000);

            return;
        }

        $cargados = $this->aplicarDatosDetectados($resultado['datos']);
        $this->modo = 'completo';
        if (strtolower($archivo->getClientOriginalExtension()) === 'pdf') {
            $this->adjuntar($archivo);
        }
        $this->toast('info', "Completé {$cargados} campos con los datos del documento. Revisalos antes de guardar.", 7000);
    }

    /** Pasa al formulario del tipo detectado, con los datos del documento ya cargados. */
    public function aplicarSugerencia() // redirección: devuelve el redirect de Livewire
    {
        $tipo = session('sugerencia_resolucion.tipo');
        abort_unless(is_string($tipo) && in_array($tipo, self::TIPOS, true), 404);

        return $this->redirectRoute('resoluciones.crear', ['tipo' => $tipo], navigate: true);
    }

    /** @param  array<string, string>  $detectados */
    private function aplicarDatosDetectados(array $detectados): int
    {
        $cargados = 0;
        foreach ($detectados as $campo => $valor) {
            if (array_key_exists($campo, $this->datos)) {
                $this->datos[$campo] = $valor;
                $cargados++;
            }
        }
        $this->resetearVistaPrevia();

        return $cargados;
    }

    public static function nombreDeTipo(string $tipo): string
    {
        return match ($tipo) {
            'Cancelaciones' => 'Cancelaciones',
            'Resciciones' => 'Rescisiones',
            'Transferencias' => 'Transferencias',
            'Transferencia-Cancelacion' => 'Transferencia y Cancelación',
            'Rectificacion' => 'Rectificación',
            'AplicarPagos' => 'Aplicar pagos',
            'ReconocimientoCuotaPagadaDosVeces' => 'Reconocimiento de cuota pagada dos veces',
            'ReconocimientoCuotaPagadaNoCargada' => 'Reconocimiento de cuota pagada y no cargada',
            default => $tipo,
        };
    }

    public function guardar()
    {
        if (! View::exists("prototipos.{$this->tipo}")) {
            $this->toast('error', $this->mensajePlantillaNoDisponible(), 6000);

            return;
        }

        $this->validarConAviso($this->rules(), $this->messages());

        try {
            $html = View::make("prototipos.{$this->tipo}", array_merge($this->datos, [
                'formatearFecha' => 'formatearFecha',
                'formatearFechaLarga' => 'formatearFechaLarga',
                'formatearMoneda' => 'formatearMoneda',
                'num2letras' => 'num2letras',
            ]))->render();

            $cantidadArchivos = count($this->archivosPDF);

            $this->persistirResolucion(
                numeroExp: $this->datos['num_exp'],
                numeroResolucion: $this->datos['num_res'] ?? null,
                fecha: $this->datos['fecha_res'],
                codBarrio: $this->datos['manzana'] ?? null,
                codCasa: $this->datos['lote'] ?? null,
                plantilla: $html,
            );

            $this->toast('success', 'Resolución guardada exitosamente'.($cantidadArchivos ? " con {$cantidadArchivos} archivo(s)" : ''), 4000);
            $this->resetFormulario();
        } catch (\Exception $e) {
            $this->flashErrorGuardado($e);
        }
    }

    public function guardarPersonalizado()
    {
        $this->validarConAviso([
            'plantilla' => [
                'required',
                function (string $atributo, mixed $valor, \Closure $fallar) {
                    $texto = trim(html_entity_decode(strip_tags((string) $valor)));
                    if (mb_strlen($texto) < 20) {
                        $fallar('La resolución está vacía o es demasiado corta. Escribí el contenido antes de guardar.');

                        return;
                    }

                    // Marcadores de la plantilla ([NÚMERO_EXPEDIENTE], [FECHA_RESOLUCIÓN]...) sin reemplazar.
                    if (preg_match_all('/\[[A-ZÁÉÍÓÚÜÑ0-9]+(?:_[A-ZÁÉÍÓÚÜÑ0-9]+)*\]/u', $texto, $m)) {
                        $lista = implode(', ', array_slice(array_unique($m[0]), 0, 5));
                        $fallar("Todavía hay datos sin completar: {$lista}. Reemplazalos por los datos reales antes de guardar.");
                    }
                },
            ],
        ]);

        try {
            $numeroTramite = $this->siguienteNumeroTramite();
            $cantidadArchivos = count($this->archivosPDF);

            $this->persistirResolucion(
                numeroExp: $numeroTramite,
                numeroResolucion: $this->datos['num_res'] ?? null,
                fecha: $this->datos['fecha_res'] ?? now()->toDateString(),
                codBarrio: $this->datos['manzana'] ?? null,
                codCasa: $this->datos['lote'] ?? null,
                plantilla: $this->plantilla,
            );

            $this->toast('success', 'Resolución guardada exitosamente'.($cantidadArchivos ? " con {$cantidadArchivos} archivo(s)" : ''), 4000);
            // Vuelve al selector: evita guardar la misma resolución dos veces con otro clic.
            $this->plantilla = '';
            $this->modo = '';
        } catch (\Exception $e) {
            $this->flashErrorGuardado($e);
        }
    }

    /**
     * Valida y, si falla, avisa con un cartel (además de dejar los errores en $errors para
     * el resumen de la pantalla): antes la validación fallaba sin que se viera nada.
     */
    private function validarConAviso(array $reglas, array $mensajes = []): void
    {
        try {
            $this->validate($reglas, $mensajes);
        } catch (ValidationException $e) {
            $cantidad = count($e->errors());
            $primero = collect($e->errors())->flatten()->first() ?: 'Revisá los datos.';
            $this->toast('error', $primero.($cantidad > 1 ? ' (y '.($cantidad - 1).' más)' : ''), 6000);

            throw $e;
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
                'formatearFecha' => 'formatearFecha',
                'formatearFechaLarga' => 'formatearFechaLarga',
                'formatearMoneda' => 'formatearMoneda',
                'num2letras' => 'num2letras',
                'numero_tramite' => $this->generarNumeroTramite(),
            ]);

            return View::make("prototipos.{$this->tipo}", $datos)->render();
        } catch (\Exception $e) {
            report($e);

            return "<div class='text-red-500'>No se pudo generar la vista previa.</div>";
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
            $datosConFunciones = array_merge($this->getDatosEjemplo(), [
                'formatearFecha' => 'formatearFecha',
                'formatearFechaLarga' => 'formatearFechaLarga',
                'formatearMoneda' => 'formatearMoneda',
                'num2letras' => 'num2letras',
            ]);

            // Renderizar el prototipo con datos placeholder
            $contenidoHTML = View::make("prototipos.{$this->tipo}", $datosConFunciones)->render();

            // Convertir a texto plano manteniendo estructura
            $this->plantilla = $this->convertirHTMLaTexto($contenidoHTML);

        } catch (\Exception $e) {
            // Sin plantilla base: se deja vacío para que la persona escriba desde cero
            // en vez de precargar un mensaje de error que podría guardarse por accidente.
            $this->plantilla = '';
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

    public function generarNumeroTramite(): string
    {
        $año = date('Y');
        do {
            $numero = str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
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
            // Se guardan en cod_barrio/cod_casa, columnas integer.
            'datos.manzana' => 'nullable|integer|min:0',
            'datos.lote' => 'nullable|integer|min:0',
            'datos.nombre_barrio' => 'nullable|string|max:255',
            'datos.num_foja_solicitud' => 'nullable|string|max:50',
            'datos.num_foja_informe' => 'nullable|string|max:50',
            'datos.fecha_cancelacion' => 'nullable|date',
            'datos.num_foja_dictamen' => 'nullable|string|max:50',
            // El titular que se exige depende del formulario: Transferencias usa el titular
            // nuevo y Rectificación no tiene titular (antes no se podían guardar nunca).
            'datos.nombre_titular' => [$this->tipoConTitular() ? 'required' : 'nullable', 'string', 'max:255'],
            'datos.dni_titular' => [$this->tipoConTitular() ? 'required' : 'nullable', 'string', 'max:20'],
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
            'datos.nombre_titular_nuevo' => [$this->tipoConTitularNuevo() ? 'required' : 'nullable', 'string', 'max:255'],
            'datos.dni_titular_nuevo' => [$this->tipoConTitularNuevo() ? 'required' : 'nullable', 'string', 'max:20'],
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
            'datos.año_cuotas' => 'nullable|integer|min:2020|max:2100',
            'datos.fecha_primer_pago' => 'nullable|date',
        ];
    }

    private function tipoConTitularNuevo(): bool
    {
        return in_array($this->tipo, ['Transferencias', 'Transferencia-Cancelacion'], true);
    }

    private function tipoConTitular(): bool
    {
        return ! $this->tipoConTitularNuevo() && $this->tipo !== 'Rectificacion';
    }

    protected function messages(): array
    {
        return [
            'datos.nombre_titular_nuevo.required' => 'El nombre del nuevo titular es obligatorio',
            'datos.dni_titular_nuevo.required' => 'El DNI del nuevo titular es obligatorio',
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
