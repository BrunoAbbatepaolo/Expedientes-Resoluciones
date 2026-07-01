{{-- ── Grilla superior ── --}}
<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl min-w-0">

<div class="grid gap-4 grid-cols-1 md:grid-cols-3 items-stretch mb-4">

          {{-- Bienvenida --}}
    <div class="overflow-hidden rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg flex items-center min-h-[220px]">
        <div class="flex items-center gap-4 p-5 w-full">
            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center shrink-0 text-lg font-semibold">
                {{ Auth::user()->initials() }}
            </div>
            <div class="min-w-0">
                <h2 class="font-semibold text-lg leading-tight truncate">
                    ¡Hola, {{ Auth::user()->nombre }}!
                </h2>
                <p class="text-blue-100 text-sm truncate">
                    {{ Auth::user()->permisos()->where('nombre','oficina_asignada')->first()?->oficina?->nombre ?? 'Sistema Administrativo' }}
                </p>
                <div x-data="{
                        now: new Date(),
                        pad(n){ return String(n).padStart(2,'0') },
                        fmt(){ const d=this.now; return `${this.pad(d.getDate())}/${this.pad(d.getMonth()+1)}/${d.getFullYear()} — ${this.pad(d.getHours())}:${this.pad(d.getMinutes())}` }
                     }"
                     x-init="setInterval(()=> now = new Date(), 1000)"
                     class="text-xs text-blue-200 mt-2">
                    <span x-text="fmt()"></span>
                </div>
            </div>
        </div>
    </div>

        {{-- Métricas del mes --}}
    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 p-5">
            <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-3">
                Resumen del mes
            </p>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                    <p class="text-2xl font-semibold text-blue-700 dark:text-blue-300">{{ $activos }}</p>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">Activos en oficina</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                    <p class="text-2xl font-semibold text-green-700 dark:text-green-300">{{ $ingresados }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-0.5">Ingresados este mes</p>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
                    <p class="text-2xl font-semibold text-amber-700 dark:text-amber-300">{{ $egresados }}</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">Egresados este mes</p>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3">
                    <p class="text-2xl font-semibold text-purple-700 dark:text-purple-300">{{ $resoluciones }}</p>
                    <p class="text-xs text-purple-600 dark:text-purple-400 mt-0.5">Resoluciones emitidas</p>
                </div>
            </div>
        </div>

        {{-- Calendario (componente existente) --}}
        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 min-h-[220px]">
            <livewire:dashboard-calendar />
        </div>
    </div>

    {{-- ── Sección inferior ── --}}
    <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800">
        <div class="p-4 sm:p-6">

            {{-- Accesos rápidos --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-4">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">Accesos rápidos</h3>
                <span class="text-xs text-gray-400">Sistema v0.5</span>
            </div>

            <div class="grid grid-cols-5 gap-3 mb-8">
                @php
                    $accesos = [
                        ['route' => 'expedientes',    'label' => 'Expedientes',  'color' => 'blue',   'icon' => 'M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z'],
                        ['route' => 'resoluciones',   'label' => 'Resoluciones', 'color' => 'green',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['route' => 'listausuarios',  'label' => 'Usuarios',     'color' => 'purple', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                        ['route' => '#',              'label' => 'Faltas',       'color' => 'red',    'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'],
                        ['route' => 'settings.profile', 'label' => 'Configuración', 'color' => 'gray', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                    ];
                    $colorMap = [
                        'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',   'icon' => 'bg-blue-100 dark:bg-blue-800',   'svg' => 'text-blue-600 dark:text-blue-300',   'text' => 'text-blue-800 dark:text-blue-200'],
                        'green'  => ['bg' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800', 'icon' => 'bg-green-100 dark:bg-green-800', 'svg' => 'text-green-600 dark:text-green-300', 'text' => 'text-green-800 dark:text-green-200'],
                        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800','icon'=>'bg-purple-100 dark:bg-purple-800','svg'=>'text-purple-600 dark:text-purple-300','text'=>'text-purple-800 dark:text-purple-200'],
                        'red'    => ['bg' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',       'icon' => 'bg-red-100 dark:bg-red-800',     'svg' => 'text-red-600 dark:text-red-300',     'text' => 'text-red-800 dark:text-red-200'],
                        'gray'   => ['bg' => 'bg-gray-50 dark:bg-gray-900/10 border-gray-200 dark:border-gray-700',   'icon' => 'bg-gray-100 dark:bg-gray-700',   'svg' => 'text-gray-600 dark:text-gray-300',   'text' => 'text-gray-800 dark:text-gray-200'],
                    ];
                @endphp

                @foreach($accesos as $item)
                    @php $c = $colorMap[$item['color']]; $href = $item['route'] === '#' ? '#' : route($item['route']); @endphp
                    <a href="{{ $href }}"
                       class="group flex flex-col items-center gap-2 p-3 rounded-xl border transition-all duration-200 hover:scale-105 {{ $c['bg'] }}">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center {{ $c['icon'] }}">
                            <svg class="w-5 h-5 {{ $c['svg'] }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-center {{ $c['text'] }}">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>

            {{-- Expedientes recientes --}}
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">Expedientes recientes</h3>
                <a href="{{ route('expedientes') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    Ver todos →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700">
                            <th class="text-left py-2 px-3 text-xs font-medium text-gray-400 uppercase tracking-wide">N° Expediente</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Asunto / Causante</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden md:table-cell">Oficina</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden sm:table-cell">Fecha ingreso</th>
                            <th class="text-left py-2 px-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                        @forelse($recientes as $exp)
                            @php
                                $estado = $exp->fecha_salida ? 'Egresado' : 'Activo';
                                $badge  = $exp->fecha_salida
                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                    : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="py-2.5 px-3 font-medium text-blue-600 dark:text-blue-400">
                                    {{ $exp->num_exp }}
                                </td>
                                <td class="py-2.5 px-3 text-gray-700 dark:text-gray-300 max-w-xs">
                                    <p class="truncate">{{ $exp->asunto }}</p>
                                    @if($exp->causante)
                                        <p class="text-xs text-gray-400 truncate">{{ $exp->causante }}</p>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                    {{ $exp->oficinaById?->nombre ?? '—' }}
                                </td>
                                <td class="py-2.5 px-3 text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                    {{ \Carbon\Carbon::parse($exp->fecha_ingreso)->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ $estado }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400 text-sm">
                                    No hay expedientes registrados para esta oficina.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>