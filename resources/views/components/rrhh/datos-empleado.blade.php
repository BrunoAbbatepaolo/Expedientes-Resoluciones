{{-- Datos del legajo agrupados por nivel; solo se muestran los grupos que el nivel incluye
     (las columnas de niveles superiores ni siquiera se leyeron de la base). --}}
@props(['empleado', 'nivel'])

@php
    use App\Enums\NivelDatoRrhh;

    $fecha = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') : null;
    $e = $empleado;
    $grupos = [
        ['Laborales', NivelDatoRrhh::Basico, [
            'Legajo' => $e->legajo,
            'Oficina' => $e->oficinaRrhh?->nombre,
            'Función' => $e->funcion,
            'Situación laboral' => $e->situacionLaboral?->nombre,
            'Categoría titular' => $e->categoriaTitular?->nombre,
            'Categoría interina' => $e->categoriaInterina?->nombre,
            'Ingreso' => $fecha($e->fecha_ingreso),
            'Horario' => $e->horario,
            'Email' => $e->email,
        ]],
        ['Personales', NivelDatoRrhh::Personal, [
            'DNI' => $e->numero_documento,
            'CUIL' => $e->cuil,
            'Nacimiento' => trim(($fecha($e->fecha_nacimiento) ?? '').' '.($e->lugar_nacimiento ? '· '.$e->lugar_nacimiento : '')),
            'Sexo' => $e->sexo,
            'Nacionalidad' => $e->nacionalidad,
            'Domicilio' => $e->domicilio,
            'Teléfono' => $e->telefono,
            'Celular' => $e->telefono_celular,
            'Estudios' => $e->nivel_estudio,
            'Matrícula' => $e->matricula_profesional,
            'Obra social' => trim(($e->numero_obra_social ?? '').' '.($e->otra_obra_social ?? '')),
            'IPSS' => $e->numero_ipss,
            'Seguro' => trim(($e->aseguradora ?? $e->otra_aseguradora ?? '').' '.($e->numero_poliza ? 'póliza '.$e->numero_poliza : '')),
            'Emergencia' => trim(($e->emergencia ?? '').' '.($e->telefono_emergencia ?? '')),
            'Resolución' => trim(($e->resolucion ?? '').' '.($fecha($e->fecha_resolucion) ?? '')),
            'Contratado' => trim(($fecha($e->fecha_contratado) ?? '').' '.($e->res_dec_contrato ?? '')),
            'Cónyuge' => trim(($e->conyuge_nombre ?? '').' '.($e->conyuge_cuil ? 'CUIL '.$e->conyuge_cuil : '')),
            'Observación' => $e->observacion,
        ]],
        ['Sensibles', NivelDatoRrhh::Sensible, [
            'Discapacidad' => $e->es_discapacitado ? trim('Sí '.($e->tipo_discapacidad ?? '').' '.($e->grado_discapacidad ?? '')) : null,
            'Vigencia certificado' => $fecha($e->vigencia_certificado_discapacidad),
            'Enfermedad' => $e->enfermedad,
            'Tratamiento' => $e->tratamiento,
            'Cuenta bancaria' => $e->nro_cuenta_bancaria,
        ]],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @foreach ($grupos as [$titulo, $requerido, $campos])
        @continue(! $nivel->incluye($requerido))
        <div>
            <h3 class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">
                {{ $titulo }}
                @if ($requerido === NivelDatoRrhh::Sensible)
                    <span class="rounded bg-ipv-magenta/10 px-1.5 py-0.5 text-[10px] normal-case text-ipv-magenta">confidencial</span>
                @endif
            </h3>
            <dl class="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($campos as $etiqueta => $valor)
                    <div>
                        <dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">{{ $etiqueta }}</dt>
                        <dd class="text-ipv-ink dark:text-ipv-ink-dark">{{ filled($valor) ? $valor : '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endforeach
</div>
