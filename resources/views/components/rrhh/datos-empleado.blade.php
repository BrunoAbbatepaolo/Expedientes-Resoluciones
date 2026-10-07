{{-- Datos del legajo agrupados por nivel (ver App\Services\Rrhh\CamposLegajo). --}}
@props(['empleado', 'nivel'])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @foreach (\App\Services\Rrhh\CamposLegajo::grupos($empleado, $nivel) as $grupo)
        <div>
            <h3 class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-ipv-ink/50 dark:text-ipv-ink-dark/50">
                {{ $grupo['titulo'] }}
                @if ($grupo['sensible'])
                    <span class="rounded bg-ipv-magenta/10 px-1.5 py-0.5 text-[10px] normal-case text-ipv-magenta">confidencial</span>
                @endif
            </h3>
            <dl class="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($grupo['campos'] as $etiqueta => $valor)
                    <div>
                        <dt class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">{{ $etiqueta }}</dt>
                        <dd class="text-ipv-ink dark:text-ipv-ink-dark">{{ $valor ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endforeach
</div>
