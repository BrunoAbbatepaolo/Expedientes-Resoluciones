<?php

namespace App\Livewire;

use App\Models\Resolucion;
use App\Models\ResolucionArchivo;
use App\Services\ExpedientesIpv;
use App\Services\Resoluciones\AnalizadorResolucion;
use App\Services\Resoluciones\Articulos;
use App\Services\Resoluciones\Documento;
use App\Services\Resoluciones\LectorDocumento;
use App\Services\Resoluciones\Tipos;
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

    /** Datos de la resolución: lo que se tipea en el documento (ver Tipos::datosVacios()). */
    public array $datos = [];

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

        // Solo letras, números y guiones: evita que un tipo con '.' o '/' apunte a otra vista.
        abort_unless(is_string($tipo) && preg_match('/^[\pL\pN\-]+$/u', $tipo), 404);

        $this->tipo = $tipo;
        $this->datos = Tipos::datosVacios();
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

        $this->validarConAviso($this->rules(), $this->messages());

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
                    // cod_barrio/cod_casa son integer: los tipos que no usan manzana/lote llegan como '' y
                    // las manzanas o lotes con letra ("B", "6A") no entran: en MySQL estricto romperían el insert.
                    'cod_barrio' => ctype_digit((string) $codBarrio) ? $codBarrio : null,
                    'cod_casa' => ctype_digit((string) $codCasa) ? $codCasa : null,
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
            $this->sugerencia = ['tipo' => $resultado['tipo'], 'nombre' => Tipos::nombre($resultado['tipo'])];
            $this->toast('info', 'Este documento parece ser de otro tipo: '.Tipos::nombre($resultado['tipo']).'.', 6000);

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
        abort_unless(is_string($tipo) && in_array($tipo, Tipos::TODOS, true), 404);

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

    /**
     * Datos obligatorios de la plantilla de este tipo, para el aviso "Faltan N datos" del editor.
     *
     * @return list<string>
     */
    public function obligatorios(): array
    {
        $campos = [];
        foreach ($this->rules() as $clave => $regla) {
            $lista = is_array($regla) ? $regla : explode('|', $regla);
            if (in_array('required', $lista, true) && ! str_contains($clave, '*')) {
                $campos[] = substr($clave, strlen('datos.'));
            }
        }

        return $campos;
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
     * ['estado' => 'vacio'|'ok'|'no'|'error', 'detalle' => ?string, 'causante' => ?string].
     *
     * @return array{estado: string, detalle: ?string, causante: ?string}
     */
    #[Computed]
    public function expedienteVinculado(): array
    {
        $numero = trim((string) ($this->datos['num_exp'] ?? ''));
        if (mb_strlen($numero) < 3) {
            return ['estado' => 'vacio', 'detalle' => null, 'causante' => null];
        }

        try {
            $exp = app(ExpedientesIpv::class)->resumenPorNumero($numero);
        } catch (\Throwable $e) {
            report($e);

            return ['estado' => 'error', 'detalle' => null, 'causante' => null];
        }

        return $exp
            ? ['estado' => 'ok', 'detalle' => trim(($exp->causante ?? '').' — '.($exp->asunto ?? ''), ' —'), 'causante' => $exp->causante ?: null]
            : ['estado' => 'no', 'detalle' => null, 'causante' => null];
    }

    /**
     * El causante del expediente (sistema viejo) como titular, si este tipo de resolución lleva titular y todavía
     * no se cargó uno. Es una sugerencia que la persona acepta: no siempre el causante es el titular.
     */
    #[Computed]
    public function titularSugerido(): ?string
    {
        $campo = Tipos::campoTitular($this->tipo);
        if (! $campo || trim((string) ($this->datos[$campo] ?? '')) !== '') {
            return null;
        }

        return $this->expedienteVinculado()['causante'];
    }

    public function usarTitularSugerido(): void
    {
        $campo = Tipos::campoTitular($this->tipo);
        $causante = $this->titularSugerido();
        if ($campo && $causante) {
            $this->datos[$campo] = mb_strtoupper($causante);
        }
    }

    protected function rules(): array
    {
        return Tipos::reglas($this->tipo, $this->reglaExpedienteExiste());
    }

    protected function messages(): array
    {
        return Tipos::mensajes();
    }

    public function render()
    {
        return view('livewire.crear-resolucion');
    }
}
