<?php

namespace App\Livewire;

use App\Models\Resolucion;
use App\Models\ResolucionArchivo;
use App\Services\ExpedientesIpv;
use App\Services\Resoluciones\AnalizadorResolucion;
use App\Services\Resoluciones\Articulos;
use App\Services\Resoluciones\Documento;
use App\Services\Resoluciones\LectorDocumento;
use App\Traits\AuthorizesOficina;
use App\Traits\NotificaUi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
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

    /** Id de la resolución a copiar ("Usar como base" en la lista); se usa solo al abrir la pantalla. */
    #[Url]
    public ?int $base = null;

    public string $modo = ''; // 'plantilla' (datos editados en el documento) o 'personalizado' (editor libre)

    /** Ya guardada en esta pantalla: evita guardarla dos veces con otro clic. */
    #[Locked]
    public ?int $resolucionGuardadaId = null;

    /** Posiciones (1..n) de los artículos de RESUELVE que se quitaron de la vista previa; ver Articulos. */
    #[Locked]
    public array $articulosQuitados = [];

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
        'num_res_adjudicacion' => '',
        'nombre_titular_anterior' => '',
        'dni_titular_anterior' => '',
        'nombre_cotitular_anterior' => '',
        'dni_cotitular_anterior' => '',
        'num_res_reglamentacion' => '',
        'num_res_modificatoria' => '',
        'unidad' => '',
        'sector' => '',
        'estado_civil_titular_nuevo' => '',
        'estado_civil_cotitular_nuevo' => '',
        // Campos específicos de AplicarPagos (nombre_barrio ya está arriba)
        'codigo_pago' => '',
        'causante' => 'DEPARTAMENTO RECURSOS FINANCIEROS',
        'jefe_computos' => '',
        'monto' => '',
        'cuotas_adeudadas' => '',
        'firma_1_nombre' => '',
        'firma_1_cargo' => '',
        'firma_2_nombre' => '',
        'firma_2_cargo' => '',
        'firma_1_dependencia' => '',
        'firma_2_dependencia' => '',
        // Campos específicos de Rectificación
        'texto_donde_dice' => '',
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
        // Campos específicos de ReconocimientoCuotaPagadaDosVeces
        'quien_suscribe' => '',
        'dni_suscribe' => '',
        'cuota_sin_gastos' => '',
        'nro_ultima_cuota' => '',
        'vencimiento_ultima_cuota' => '',
        'plazo_total_plan' => '',
        // Tablas de los Reconocimiento (filas que se agregan en el documento; ver components/resolucion/tabla)
        'tabla_cuotas_pagadas' => [],
        'tabla_cuotas_a_reconocer' => [],
        // Rectificación: adjudicatarios "donde dice" / "debe decir" (se pueden quitar enteras)
        'tabla_donde_dice' => [],
        'sin_tabla_donde_dice' => false,
        'tabla_debe_decir' => [],
        'sin_tabla_debe_decir' => false,
    ];

    // Plantilla para el modo personalizado
    public string $plantilla = '';

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

        // Viene de "Usar como base" en la lista de resoluciones.
        if ($this->base) {
            $this->cargarBase($this->base);
            $this->base = null;
        }

        // Viene de "Autocompletar": precarga los datos del documento y abre el formulario.
        $sugerida = session('sugerencia_resolucion');
        if (is_array($sugerida) && ($sugerida['tipo'] ?? null) === $tipo) {
            session()->forget('sugerencia_resolucion');
            $cargados = $this->aplicarDatosDetectados($sugerida['datos'] ?? []);
            $this->modo = 'plantilla';
            $this->toast('info', "Completé {$cargados} campos con los datos del documento. Revisalos antes de guardar y volvé a adjuntar el archivo si lo necesitás.", 8000);
        }
    }

    /** "Usar como base": copia los datos de otra resolución del mismo tipo; queda sin número y con la fecha de hoy. */
    private function cargarBase(int $id): void
    {
        $base = Resolucion::find($id);
        if (! $base || $base->tipo !== $this->tipo || ! $base->esPlantillaCompleta()) {
            return;
        }
        $this->autorizarResolucion($base, 'resolucion_editar');

        /** @var array{campos: array<string, mixed>, articulos_quitados?: list<int>} $guardados */
        $guardados = $base->datos;
        foreach ($guardados['campos'] as $campo => $valor) {
            if (array_key_exists($campo, $this->datos)) {
                $this->datos[$campo] = $valor;
            }
        }
        $this->datos['num_res'] = '';
        $this->datos['fecha_res'] = date('Y-m-d');
        $this->articulosQuitados = array_map('intval', $guardados['articulos_quitados'] ?? []);
        $this->modo = 'plantilla';
        $this->toast('info', 'Copié los datos de la resolución '.($base->numero_resolucion ?: 'sin número').'. Revisalos y cambiá lo que corresponda.', 8000);
    }

    public function usarPlantilla(): void
    {
        // Sin resetear: si se llegó desde "Autocompletar", los datos del documento ya están cargados.
        $this->modo = 'plantilla';
    }

    /** Para x-resolucion.derivado: el número en letras tal como saldrá al guardar. Sin re-render. */
    #[Renderless]
    public function enLetras(mixed $valor): string
    {
        $valor = trim((string) $valor);

        return is_numeric(str_replace(['$', ',', ' '], '', $valor)) ? num2letras($valor) : '';
    }

    public function usarPersonalizado()
    {
        $this->modo = 'personalizado';
        $this->cargarPlantillaEditable();
    }

    /**
     * Documento del modo 'plantilla' con los datos como inputs (ligados a $wire.datos). La vista lo imprime
     * sin escapar: se arma en el servidor y los datos van escapados por Blade dentro de los componentes.
     */
    public function documentoEditable(): string
    {
        try {
            return Documento::html($this->tipo, $this->datos, $this->articulosQuitados, true);
        } catch (\Throwable $e) {
            report($e);

            return "<div class='text-red-500'>No se pudo mostrar la plantilla.</div>";
        }
    }

    private function mensajePlantillaNoDisponible(): string
    {
        return "Todavía no hay una plantilla completa para \"{$this->tipo}\". Podés escribir el contenido en el modo \"Personalizado\".";
    }

    /**
     * "Guardar resolución" del modo 'plantilla'. El documento se arma en el servidor desde $datos (lo que se tipeó
     * en los campos del documento), no desde el DOM. Devuelve la URL para imprimirla, o null si no se guardó:
     * en ese caso la pantalla queda como estaba (los datos no se pierden).
     */
    public function guardarPlantilla(): ?string
    {
        if ($this->resolucionGuardadaId) {
            return route('resoluciones.imprimir', $this->resolucionGuardadaId);
        }

        if (! Documento::existe($this->tipo)) {
            $this->toast('error', $this->mensajePlantillaNoDisponible(), 6000);

            return null;
        }

        $this->validarConAviso($this->reglasPlantilla(), $this->messages());

        try {
            $resolucion = $this->persistirResolucion(
                numeroExp: trim($this->datos['num_exp']),
                numeroResolucion: $this->datos['num_res'] ?? null,
                fecha: $this->datos['fecha_res'],
                codBarrio: $this->datos['manzana'] ?? null,
                codCasa: $this->datos['lote'] ?? null,
                plantilla: Documento::html($this->tipo, $this->datos, $this->articulosQuitados),
                tipo: $this->tipo,
                datos: ['campos' => $this->datos, 'articulos_quitados' => $this->articulosQuitados],
            );
        } catch (\Exception $e) {
            $this->flashErrorGuardado($e);

            return null;
        }

        $resolucion->regenerarDocumento();
        $this->resolucionGuardadaId = $resolucion->id;
        // La lista lo muestra con el enlace para imprimir (por si el navegador bloqueó la pestaña nueva).
        session()->put('resolucion_guardada', ['id' => $resolucion->id, 'numero_exp' => $resolucion->numero_exp]);
        $this->toast('success', 'Resolución guardada. Abriendo el PDF…', 4000);

        return route('resoluciones.imprimir', $resolucion);
    }

    /**
     * @param  array<string, mixed>|null  $datos  ['campos' => ..., 'articulos_quitados' => ...] o ['personalizado' => true]
     * @param  'default'|'resolucion'  $purificador  config de Purifier ('resolucion' conserva las clases del prototipo)
     */
    private function persistirResolucion(
        string $numeroExp,
        ?string $numeroResolucion,
        string $fecha,
        ?string $codBarrio,
        ?string $codCasa,
        string $plantilla,
        ?string $tipo = null,
        ?array $datos = null,
        string $purificador = 'default',
    ): Resolucion {
        // Sin oficina la resolución quedaría visible y editable para todas las
        // oficinas (ver AuthorizesOficina::autorizarResolucion()).
        $oficinaId = auth()->user()->oficinaAsignadaId();
        if (! $oficinaId) {
            throw new \DomainException('No tenés una oficina asignada. Pedile a un administrador que te asigne una.');
        }

        $rutasGuardadas = [];

        try {
            $resolucion = DB::transaction(function () use ($oficinaId, $numeroExp, $numeroResolucion, $fecha, $codBarrio, $codCasa, $plantilla, $tipo, $datos, $purificador, &$rutasGuardadas) {
                $resolucion = Resolucion::create([
                    'oficina_id' => $oficinaId,
                    'numero_exp' => $numeroExp,
                    'numero_resolucion' => $numeroResolucion !== null && trim($numeroResolucion) !== '' ? trim($numeroResolucion) : null,
                    'fecha' => $fecha,
                    // cod_barrio/cod_casa son integer: los tipos que no usan manzana/lote
                    // llegan como '' (string vacío) y no como null, lo que rompe el insert
                    // en modo estricto de MySQL.
                    'cod_barrio' => $codBarrio !== '' ? $codBarrio : null,
                    'cod_casa' => $codCasa !== '' ? $codCasa : null,
                    // Sanitizado antes de guardar: en modo personalizado 'plantilla' es HTML editado a mano
                    // en el navegador y podría contener HTML/JS malicioso si se muestra a otros usuarios.
                    'plantilla' => Purifier::clean($plantilla, $purificador),
                    'tipo' => $tipo,
                    'datos' => $datos,
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
        $this->modo = 'plantilla';
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

    /** Devuelve la URL para imprimir la resolución guardada, o null si no se guardó. */
    public function guardarPersonalizado(): ?string
    {
        if ($this->resolucionGuardadaId) {
            return route('resoluciones.imprimir', $this->resolucionGuardadaId);
        }

        $this->validarConAviso([
            'datos.num_exp' => ['required', 'string', 'max:50', $this->reglaExpedienteExiste()],
            'plantilla' => [
                'required',
                function (string $atributo, mixed $valor, \Closure $fallar) {
                    $texto = trim(html_entity_decode(strip_tags((string) $valor)));
                    if (mb_strlen($texto) < 20) {
                        $fallar('La resolución está vacía o es demasiado corta. Escribí el contenido antes de guardar.');

                        return;
                    }

                    // Marcadores de la plantilla ([NÚMERO_EXPEDIENTE], [FECHA_RESOLUCIÓN]...) sin reemplazar.
                    if (preg_match_all('/\[[A-ZÁÉÍÓÚÜÑ0-9][A-ZÁÉÍÓÚÜÑ0-9 _\/\.]*\]/u', $texto, $m)) {
                        $lista = implode(', ', array_slice(array_unique($m[0]), 0, 5));
                        $fallar("Todavía hay datos sin completar: {$lista}. Reemplazalos por los datos reales antes de guardar.");
                    }
                },
            ],
        ]);

        // Si viene del documento de la plantilla se guarda con el tipo, para imprimirlo con su diseño.
        $conDiseno = Documento::existe($this->tipo) && str_contains($this->plantilla, 'documento-resolucion');

        try {
            $resolucion = $this->persistirResolucion(
                numeroExp: trim($this->datos['num_exp']),
                numeroResolucion: $this->datos['num_res'] ?? null,
                fecha: ($this->datos['fecha_res'] ?? '') ?: now()->toDateString(),
                codBarrio: $this->datos['manzana'] ?? null,
                codCasa: $this->datos['lote'] ?? null,
                plantilla: $conDiseno ? Documento::rutasRelativas($this->plantilla) : $this->plantilla,
                tipo: $conDiseno ? $this->tipo : null,
                datos: $conDiseno ? ['personalizado' => true] : null,
                purificador: $conDiseno ? 'resolucion' : 'default',
            );
        } catch (\Exception $e) {
            $this->flashErrorGuardado($e);

            return null;
        }

        $this->resolucionGuardadaId = $resolucion->id;
        session()->put('resolucion_guardada', ['id' => $resolucion->id, 'numero_exp' => $resolucion->numero_exp]);
        $this->toast('success', 'Resolución guardada. Abriendo el PDF…', 4000);
        // Vuelve al selector: evita guardar la misma resolución dos veces con otro clic.
        $this->plantilla = '';
        $this->modo = '';

        return route('resoluciones.imprimir', $resolucion);
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
            // El editor de la plantilla marca en rojo esos campos dentro del documento.
            $this->dispatch('campos-con-error', campos: collect(array_keys($e->errors()))
                ->filter(fn ($k) => str_starts_with($k, 'datos.'))
                ->map(fn ($k) => explode('.', substr($k, strlen('datos.')))[0])
                ->unique()->values()->all());

            throw $e;
        }
    }

    /** Saca un artículo del documento (y de lo que se guarda); los siguientes se renumeran solos. */
    public function quitarArticulo(int $posicion): void
    {
        try {
            $total = Articulos::contar(Documento::html($this->tipo, $this->datos));
        } catch (\Throwable $e) {
            report($e);

            return;
        }

        if ($posicion < 1 || $posicion > $total || in_array($posicion, $this->articulosQuitados, true)) {
            return;
        }

        if (count($this->articulosQuitados) + 1 >= $total) {
            $this->toast('error', 'La resolución tiene que conservar al menos un artículo.');

            return;
        }

        $this->articulosQuitados[] = $posicion;
    }

    public function restaurarArticulos(): void
    {
        $this->articulosQuitados = [];
    }

    /**
     * Personalizado: el mismo documento de la plantilla (con su diseño) y todo el texto editable. Arranca con los
     * datos ya cargados y, donde falta uno, con su marcador ([NÚMERO DE DICTAMEN]...) resaltado.
     */
    protected function cargarPlantillaEditable(): void
    {
        try {
            $this->plantilla = Documento::rutasRelativas(Documento::separarEstilos(
                Documento::html($this->tipo, $this->datos, $this->articulosQuitados, marcadores: true)
            )['cuerpo']);
        } catch (\Throwable $e) {
            report($e);
            // Sin plantilla base: se deja vacío para que la persona escriba desde cero
            // en vez de precargar un mensaje de error que podría guardarse por accidente.
            $this->plantilla = '';
        }
    }

    /** Los <style> del prototipo, para mostrar el documento del modo Personalizado con su diseño. */
    public function estilosPersonalizado(): string
    {
        return Documento::existe($this->tipo) ? Documento::estilos($this->tipo) : '';
    }

    protected function rules(): array
    {
        return [
            'datos.fecha_res' => 'required|date',
            'datos.num_exp' => ['required', 'string', 'max:50', $this->reglaExpedienteExiste()],
            // Opcional: el borrador sale sin número y se asigna al firmar (desde la lista).
            'datos.num_res' => 'nullable|string|max:50',
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
            'datos.texto_donde_dice' => 'nullable|string',
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
            'datos.codigo_pago' => 'nullable|string|max:20',
            'datos.monto' => 'nullable|string|max:30',
            'datos.cuotas_adeudadas' => 'nullable|string|max:10',
            'datos.causante' => 'nullable|string|max:255',
            'datos.jefe_computos' => 'nullable|string|max:255',
            'datos.firma_1_nombre' => 'nullable|string|max:255',
            'datos.firma_1_cargo' => 'nullable|string|max:255',
            'datos.firma_2_nombre' => 'nullable|string|max:255',
            'datos.firma_2_cargo' => 'nullable|string|max:255',
            'datos.firma_1_dependencia' => 'nullable|string|max:255',
            'datos.firma_2_dependencia' => 'nullable|string|max:255',
            // Cada resolución cita su propio dictamen (en los PDF reales nunca se repite entre expedientes).
            'datos.num_dictamen' => [$this->tipoConDictamen() ? 'required' : 'nullable', 'string', 'max:50'],
            // Campos específicos de los Reconocimiento
            'datos.quien_suscribe' => 'nullable|string|max:255',
            'datos.dni_suscribe' => 'nullable|string|max:20',
            'datos.cuota_sin_gastos' => 'nullable|string|max:30',
            'datos.nro_ultima_cuota' => 'nullable|string|max:10',
            'datos.vencimiento_ultima_cuota' => 'nullable|date',
            'datos.plazo_total_plan' => 'nullable|string|max:30',
            'datos.tabla_cuotas_pagadas' => 'nullable|array|max:50',
            'datos.tabla_cuotas_pagadas.*.*' => 'nullable|string|max:100',
            'datos.tabla_cuotas_a_reconocer' => 'nullable|array|max:50',
            'datos.tabla_cuotas_a_reconocer.*.*' => 'nullable|string|max:100',
            'datos.tabla_donde_dice' => 'nullable|array|max:50',
            'datos.tabla_donde_dice.*.*' => 'nullable|string|max:100',
            'datos.tabla_debe_decir' => 'nullable|array|max:50',
            'datos.tabla_debe_decir.*.*' => 'nullable|string|max:100',
            'datos.sin_tabla_donde_dice' => 'nullable|boolean',
            'datos.sin_tabla_debe_decir' => 'nullable|boolean',
        ];
    }

    /**
     * Datos obligatorios de los tipos cuya plantilla no tiene todos los de rules() (p. ej. las notas de
     * Aplicar pagos y Reconocimiento no llevan número de resolución ni titular): sin esto no se podría guardar nunca,
     * porque el dato exigido no aparece en el documento. null = se usan las reglas de rules() tal cual.
     *
     * @return list<string>|null
     */
    private function camposObligatorios(): ?array
    {
        return match ($this->tipo) {
            'AplicarPagos' => ['fecha_res', 'num_exp', 'codigo_pago', 'monto', 'cuotas_adeudadas'],
            'ReconocimientoCuotaPagadaDosVeces', 'ReconocimientoCuotaPagadaNoCargada' => ['fecha_res', 'num_exp', 'codigo_pago', 'quien_suscribe', 'dni_suscribe'],
            default => null,
        };
    }

    /** rules() con 'required' solo en los datos obligatorios de la plantilla de este tipo. */
    private function reglasPlantilla(): array
    {
        $reglas = $this->rules();
        $obligatorios = $this->camposObligatorios();
        if ($obligatorios === null) {
            return $reglas;
        }

        foreach ($reglas as $clave => $regla) {
            $lista = array_values(array_filter(
                is_array($regla) ? $regla : explode('|', $regla),
                fn ($r) => ! in_array($r, ['required', 'nullable'], true),
            ));
            $campo = substr($clave, strlen('datos.'));
            $reglas[$clave] = [in_array($campo, $obligatorios, true) ? 'required' : 'nullable', ...$lista];
        }

        return $reglas;
    }

    private function tipoConDictamen(): bool
    {
        // En los PDF reales de estos tipos el dictamen está siempre (189 de 189). De Rescisiones no hay muestras: opcional.
        return in_array($this->tipo, ['Cancelaciones', 'Transferencias', 'Transferencia-Cancelacion', 'Rectificacion'], true);
    }

    /**
     * Datos obligatorios de la plantilla de este tipo, para el aviso "Faltan N datos" del editor.
     *
     * @return list<string>
     */
    public function obligatorios(): array
    {
        $campos = [];
        foreach ($this->reglasPlantilla() as $clave => $regla) {
            $lista = is_array($regla) ? $regla : explode('|', $regla);
            if (in_array('required', $lista, true) && ! str_contains($clave, '*')) {
                $campos[] = substr($clave, strlen('datos.'));
            }
        }

        return $campos;
    }

    private function tipoConTitularNuevo(): bool
    {
        return in_array($this->tipo, ['Transferencias', 'Transferencia-Cancelacion'], true);
    }

    private function tipoConTitular(): bool
    {
        return ! $this->tipoConTitularNuevo() && $this->tipo !== 'Rectificacion';
    }

    /** Toda resolución va vinculada a un expediente real: se comprueba en mitivdb (sistema viejo). */
    private function reglaExpedienteExiste(): \Closure
    {
        return function (string $atributo, mixed $valor, \Closure $fallar) {
            try {
                if (app(ExpedientesIpv::class)->resumenPorNumero((string) $valor) === null) {
                    $fallar('El expediente '.trim((string) $valor).' no existe. Una resolución tiene que estar vinculada a un expediente cargado.');
                }
            } catch (\Throwable $e) {
                report($e);
                $fallar('No se pudo verificar el expediente en este momento. Probá de nuevo en unos minutos.');
            }
        };
    }

    /**
     * Estado del número de expediente tipeado, para el aviso debajo del campo:
     * ['estado' => 'vacio'|'ok'|'no'|'error', 'detalle' => ?string].
     *
     * @return array{estado: string, detalle: ?string}
     */
    #[Computed]
    public function expedienteVinculado(): array
    {
        $numero = trim((string) ($this->datos['num_exp'] ?? ''));
        if (mb_strlen($numero) < 3) {
            return ['estado' => 'vacio', 'detalle' => null];
        }

        try {
            $exp = app(ExpedientesIpv::class)->resumenPorNumero($numero);
        } catch (\Throwable $e) {
            report($e);

            return ['estado' => 'error', 'detalle' => null];
        }

        return $exp
            ? ['estado' => 'ok', 'detalle' => trim(($exp->causante ?? '').' — '.($exp->asunto ?? ''), ' —')]
            : ['estado' => 'no', 'detalle' => null];
    }

    protected function messages(): array
    {
        return [
            'datos.nombre_titular_nuevo.required' => 'El nombre del nuevo titular es obligatorio',
            'datos.dni_titular_nuevo.required' => 'El DNI del nuevo titular es obligatorio',
            'datos.fecha_res.required' => 'La fecha de resolución es obligatoria',
            'datos.num_exp.required' => 'El número de expediente es obligatorio',
            'datos.nombre_titular.required' => 'El nombre del titular es obligatorio',
            'datos.dni_titular.required' => 'El DNI del titular es obligatorio',
            'datos.num_dictamen.required' => 'El número de dictamen es obligatorio',
            'datos.codigo_pago.required' => 'El código de pago es obligatorio',
            'datos.monto.required' => 'El monto es obligatorio',
            'datos.cuotas_adeudadas.required' => 'La cantidad de cuotas adeudadas es obligatoria',
            'datos.quien_suscribe.required' => 'El nombre de quien suscribe es obligatorio',
            'datos.dni_suscribe.required' => 'El DNI de quien suscribe es obligatorio',
            'datos.*.max' => 'El campo excede la longitud máxima permitida',
            'datos.*.date' => 'El campo debe ser una fecha válida',
        ];
    }

    public function render()
    {
        return view('livewire.crear-resolucion');
    }
}
