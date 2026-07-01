<div class="max-w-4xl mx-auto p-6 bg-white dark:bg-gray-900 shadow-lg rounded-lg" x-data="{ activeTab: 'datos' }">
    <!-- Encabezado -->
    <div class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <div class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
            Expediente #{{ $expediente->num_exp }}
        </div>
        <div class="text-gray-600 dark:text-gray-300 mb-2">
            <span class="font-semibold">Asunto:</span> {{ $expediente->asunto }}
        </div>
        <div class="text-gray-600 dark:text-gray-300">
            <span class="font-semibold">Oficina:</span> {{ $expediente->oficinaById->nombre ?? 'Sin oficina' }}
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <div class="flex space-x-4">
            <button @click="activeTab = 'datos'"
                :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': activeTab === 'datos' }"
                class="py-2 px-4 border-b-2 font-medium text-sm hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                Datos
            </button>
            <button @click="activeTab = 'pases'"
                :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': activeTab === 'pases' }"
                class="py-2 px-4 border-b-2 font-medium text-sm hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                Pases
            </button>
            <button @click="activeTab = 'adjuntos'"
                :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': activeTab === 'adjuntos' }"
                class="py-2 px-4 border-b-2 font-medium text-sm hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                Adjuntos
            </button>
        </div>
    </div>

    <!-- Datos -->
    <div x-show="activeTab === 'datos'" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded">
                <span class="font-semibold">Fecha ingreso:</span>
                {{ \Carbon\Carbon::parse($expediente->fecha_ingreso)->format('d/m/Y') }}
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded">
                <span class="font-semibold">Folios:</span> {{ $expediente->folio }}
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded col-span-2">
                <span class="font-semibold">Causante:</span> {{ $expediente->causante }}
            </div>
            @if($expediente->fecha_salida)
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded col-span-2">
                <span class="font-semibold">Fecha salida:</span>
                {{ \Carbon\Carbon::parse($expediente->fecha_salida)->format('d/m/Y') }}
            </div>
            @endif
        </div>
    </div>

    <!-- Pases -->
    <div x-show="activeTab === 'pases'" class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Origen</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Destino</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fecha</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Hora</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Observaciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($pases as $pase)
                <tr class="{{ !$pase['importado'] ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-900' }}">
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                        {{ $pase['origen'] }}
                    </td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                        {{ $pase['destino'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                        {{ $pase['fecha']->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                        {{ $pase['hora'] ? substr($pase['hora'], 0, 5) : '-' }}
                    </td>
                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                        {{ $pase['observacion'] ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        Sin historial de pases.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <!-- Adjuntos -->
    <div x-show="activeTab === 'adjuntos'" class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <h3 class="font-semibold text-lg mb-4 text-gray-800 dark:text-gray-200">Adjuntos</h3>
        <p class="text-gray-500 text-sm">Sin adjuntos por el momento.</p>
    </div>
</div>