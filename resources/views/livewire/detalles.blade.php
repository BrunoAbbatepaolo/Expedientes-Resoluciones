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
            <button @click="activeTab = 'adjuntos'"
                :class="activeTab === 'adjuntos' ? 'border-ipv-blue text-ipv-blue dark:text-ipv-blue-light' : 'border-transparent text-ipv-ink/60 dark:text-ipv-ink-dark/60'"
                class="border-b-2 px-4 py-2 text-sm font-medium transition-colors hover:text-ipv-blue dark:hover:text-ipv-blue-light">
                Adjuntos
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
                <tr class="{{ !$pase['importado'] ? 'bg-ipv-blue/[0.04] dark:bg-ipv-blue-light/[0.06]' : '' }}">
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

    <!-- Adjuntos -->
    <div x-show="activeTab === 'adjuntos'" class="rounded-lg bg-black/[0.02] p-4 dark:bg-white/[0.04]">
        <h3 class="mb-4 text-lg font-semibold text-ipv-ink dark:text-ipv-ink-dark">Adjuntos</h3>
        <p class="text-sm text-ipv-ink/50 dark:text-ipv-ink-dark/50">Sin adjuntos por el momento.</p>
    </div>
</div>