<div class="sirex-glass-card mx-auto max-w-4xl rounded-[14px] p-6" x-data="{ activeTab: 'datos' }">
    <!-- Encabezado -->
    <div class="mb-6 rounded-lg bg-black/[0.02] p-4 dark:bg-white/[0.04]">
        <div class="mb-2 text-2xl font-bold text-ipv-ink dark:text-ipv-ink-dark">
            Expediente #{{ $expediente->num_exp }}
        </div>
        <div class="mb-2 text-ipv-ink/70 dark:text-ipv-ink-dark/75">
            <span class="font-semibold">Asunto:</span> {{ $expediente->asunto }}
        </div>
        <div class="text-ipv-ink/70 dark:text-ipv-ink-dark/75">
            <span class="font-semibold">Oficina:</span> {{ $expediente->oficinaById->nombre ?? 'Sin oficina' }}
        </div>
        @if ($idIpv)
            <a href="{{ route('expedientes.ipv.ficha', $idIpv) }}" wire:navigate.hover
                class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-ipv-blue transition hover:gap-2.5 dark:text-ipv-blue-light">
                Ver recorrido completo en el IPV <span aria-hidden="true">→</span>
            </a>
        @endif
    </div>

    <!-- Tabs -->
    <div class="mb-6 border-b border-ipv-blue/10 dark:border-white/10">
        <div class="flex space-x-4">
            <button @click="activeTab = 'datos'"
                :class="activeTab === 'datos' ? 'border-ipv-blue text-ipv-blue dark:text-ipv-blue-light' : 'border-transparent text-ipv-ink/60 dark:text-ipv-ink-dark/60'"
                class="border-b-2 px-4 py-2 text-sm font-medium transition-colors hover:text-ipv-blue dark:hover:text-ipv-blue-light">
                Datos
            </button>
            <button @click="activeTab = 'pases'"
                :class="activeTab === 'pases' ? 'border-ipv-blue text-ipv-blue dark:text-ipv-blue-light' : 'border-transparent text-ipv-ink/60 dark:text-ipv-ink-dark/60'"
                class="border-b-2 px-4 py-2 text-sm font-medium transition-colors hover:text-ipv-blue dark:hover:text-ipv-blue-light">
                Pases
            </button>
            <button @click="activeTab = 'historial'"
                :class="activeTab === 'historial' ? 'border-ipv-blue text-ipv-blue dark:text-ipv-blue-light' : 'border-transparent text-ipv-ink/60 dark:text-ipv-ink-dark/60'"
                class="border-b-2 px-4 py-2 text-sm font-medium transition-colors hover:text-ipv-blue dark:hover:text-ipv-blue-light">
                Historial de cambios
            </button>
        </div>
    </div>

    <!-- Datos -->
    <div x-show="activeTab === 'datos'" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded bg-black/[0.02] p-4 text-ipv-ink dark:bg-white/[0.04] dark:text-ipv-ink-dark">
                <span class="font-semibold">Fecha ingreso:</span>
                {{ \Carbon\Carbon::parse($expediente->fecha_ingreso)->format('d/m/Y') }}
            </div>
            <div class="rounded bg-black/[0.02] p-4 text-ipv-ink dark:bg-white/[0.04] dark:text-ipv-ink-dark">
                <span class="font-semibold">Folios:</span> {{ $expediente->folio }}
            </div>
            <div class="col-span-2 rounded bg-black/[0.02] p-4 text-ipv-ink dark:bg-white/[0.04] dark:text-ipv-ink-dark">
                <span class="font-semibold">Causante:</span> {{ $expediente->causante }}
            </div>
            @if($expediente->fecha_salida)
            <div class="col-span-2 rounded bg-black/[0.02] p-4 text-ipv-ink dark:bg-white/[0.04] dark:text-ipv-ink-dark">
                <span class="font-semibold">Fecha salida:</span>
                {{ \Carbon\Carbon::parse($expediente->fecha_salida)->format('d/m/Y') }}
            </div>
            @endif
        </div>
    </div>

    <!-- Pases -->
    <div x-show="activeTab === 'pases'" class="overflow-x-auto">
    <table class="min-w-full divide-y divide-ipv-blue/10 dark:divide-white/10">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-ipv-ink/50 dark:text-ipv-ink-dark/50">Origen</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-ipv-ink/50 dark:text-ipv-ink-dark/50">Destino</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-ipv-ink/50 dark:text-ipv-ink-dark/50">Fecha</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-ipv-ink/50 dark:text-ipv-ink-dark/50">Hora</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-ipv-ink/50 dark:text-ipv-ink-dark/50">Observaciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-ipv-blue/10 dark:divide-white/10">
            @forelse ($pases as $pase)
                <tr wire:key="pase-{{ $loop->index }}" class="{{ !$pase['importado'] ? 'bg-ipv-blue/[0.04] dark:bg-ipv-blue-light/[0.06]' : '' }}">
                    <td class="px-6 py-4 text-ipv-ink/80 dark:text-ipv-ink-dark/85">
                        {{ $pase['origen'] }}
                    </td>
                    <td class="px-6 py-4 text-ipv-ink/80 dark:text-ipv-ink-dark/85">
                        {{ $pase['destino'] }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-ipv-ink/80 dark:text-ipv-ink-dark/85">
                        {{ $pase['fecha']->format('d/m/Y') }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-ipv-ink/80 dark:text-ipv-ink-dark/85">
                        {{ $pase['hora'] ? substr($pase['hora'], 0, 5) : '-' }}
                    </td>
                    <td class="px-6 py-4 text-ipv-ink/80 dark:text-ipv-ink-dark/85">
                        {{ $pase['observacion'] ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-ipv-ink/40 dark:text-ipv-ink-dark/40">
                        Sin historial de pases.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <!-- Historial de cambios (tabla audits) -->
    <div x-show="activeTab === 'historial'" x-cloak class="rounded-lg bg-black/[0.02] p-4 dark:bg-white/[0.04]">
        <ol class="space-y-3">
            @forelse ($cambios as $cambio)
                <li wire:key="audit-{{ $loop->index }}" class="rounded-lg bg-white/60 p-3 dark:bg-white/[0.03]">
                    <p class="text-sm text-ipv-ink dark:text-ipv-ink-dark">
                        <span class="font-semibold">{{ $cambio['usuario'] }}</span> {{ mb_strtolower($cambio['evento']) }} el expediente
                        <span class="text-xs text-ipv-ink/50 dark:text-ipv-ink-dark/50">· {{ $cambio['fecha']?->format('d/m/Y H:i') }}</span>
                    </p>
                    @if ($cambio['evento'] === 'Editó' && $cambio['campos']->isNotEmpty())
                        <ul class="mt-1.5 space-y-0.5 text-xs text-ipv-ink/70 dark:text-ipv-ink-dark/70">
                            @foreach ($cambio['campos'] as $c)
                                <li>
                                    <span class="font-medium capitalize">{{ $c['campo'] }}:</span>
                                    <span class="line-through opacity-60">{{ is_scalar($c['antes']) ? $c['antes'] : '—' }}</span>
                                    → {{ is_scalar($c['despues']) ? $c['despues'] : '—' }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @empty
                <li class="text-sm text-ipv-ink/50 dark:text-ipv-ink-dark/50">Sin cambios registrados.</li>
            @endforelse
        </ol>
    </div>
</div>