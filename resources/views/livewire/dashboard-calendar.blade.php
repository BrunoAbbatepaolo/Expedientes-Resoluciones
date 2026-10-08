<?php

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new class extends Component
{
    public $currentMonth;

    public $currentYear;

    // Modal state
    public $showModal = false;

    #[Locked]
    public $taskId = null;

    public $taskTitle = '';

    public $taskDescription = '';

    public $taskDate = '';

    public $taskColor = 'blue';

    public $taskAssignedTo = '';

    public $isEditing = true;

    // TEMPORAL: Permisos manejados desde el front (forzamos a true para que puedas probar)
    #[Locked]
    public $hasPermission = true;

    public function mount()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    #[Computed]
    public function availableNames()
    {
        // Compañeros de la oficina del usuario (antes era una lista fija en el código).
        $oficinaId = Auth::user()->oficinaAsignadaId();
        if (! $oficinaId) {
            return [Auth::user()->name];
        }

        $ids = \App\Models\Permiso::where('nombre', 'oficina_asignada')->where('oficina_id', $oficinaId)->pluck('user_id');

        return User::whereIn('id', $ids)->orderBy('nombre')->get()
            ->map(fn (User $u) => $u->name)
            ->filter()
            ->values()
            ->all();
    }

    // public function getHasPermissionProperty()
    // {
    //     return Auth::user()->permiso('calendario_admin');
    // }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function openModal($date = null, $taskId = null)
    {
        if (! $this->hasPermission) {
            return;
        }

        $this->resetValidation();

        if ($taskId) {
            $task = Task::find($taskId);
            if ($task) {
                $this->taskId = $task->id;
                $this->taskTitle = $task->title;
                $this->taskDescription = $task->description;
                $this->taskDate = $task->due_date->format('Y-m-d');
                $this->taskColor = $task->color;
                $this->taskAssignedTo = $task->assigned_to;
                $this->isEditing = false;
            }
        } else {
            $this->taskId = null;
            $this->taskTitle = '';
            $this->taskDescription = '';
            $this->taskDate = $date ?? now()->format('Y-m-d');
            $this->taskColor = 'blue';
            $this->taskAssignedTo = '';
            $this->isEditing = true;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function saveTask()
    {
        if (! $this->hasPermission) {
            return;
        }

        $this->validate([
            'taskTitle' => 'required|string|max:255',
            'taskDescription' => 'nullable|string',
            'taskDate' => 'required|date',
            'taskColor' => 'required|string',
            'taskAssignedTo' => 'required|string',
        ]);

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $task->update([
                'title' => $this->taskTitle,
                'description' => $this->taskDescription,
                'due_date' => $this->taskDate,
                'color' => $this->taskColor,
                'assigned_to' => $this->taskAssignedTo,
            ]);
        } else {
            Task::create([
                'title' => $this->taskTitle,
                'description' => $this->taskDescription,
                'due_date' => $this->taskDate,
                'color' => $this->taskColor,
                'assigned_to' => $this->taskAssignedTo,
                'created_by' => Auth::id(),
                'is_completed' => false,
            ]);
        }

        $this->closeModal();
    }

    public function toggleCompletion($taskId)
    {
        if (! $this->hasPermission) {
            return;
        }

        $task = Task::find($taskId);
        if ($task) {
            $task->update(['is_completed' => ! $task->is_completed]);
        }
    }

    public function deleteTask($taskId)
    {
        if (! $this->hasPermission) {
            return;
        }

        Task::find($taskId)?->delete();
        $this->closeModal();
    }

    #[Computed]
    public function daysInMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $daysInMonth = $date->daysInMonth;

        $firstDayOfWeek = $date->copy()->firstOfMonth()->dayOfWeekIso;

        $days = [];
        for ($i = 1; $i < $firstDayOfWeek; $i++) {
            $days[] = null;
        }
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = Carbon::createFromDate($this->currentYear, $this->currentMonth, $i);
        }

        return $days;
    }

    #[Computed]
    public function tasks()
    {
        return Task::query()
            ->whereYear('due_date', $this->currentYear)
            ->whereMonth('due_date', $this->currentMonth)
            ->get()
            ->groupBy(function ($task) {
                return $task->due_date->format('Y-m-d');
            });
    }

    #[Computed]
    public function todayTasks()
    {
        return Task::query()
            ->whereDate('due_date', now()->format('Y-m-d'))
            ->get();
    }
};
?>

<div class="sirex-glass-card relative flex h-full min-h-[400px] flex-col overflow-hidden rounded-[14px] xl:min-h-[450px]">
    {{-- Header: título y navegación en filas separadas para que nunca se recorte --}}
    <div class="flex flex-col gap-2 border-b border-ipv-blue/10 px-4 pb-2.5 pt-3 dark:border-white/10">
        <h3 class="flex items-center gap-1.5 text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark md:text-base">
            <svg class="size-4 shrink-0 text-ipv-blue dark:text-ipv-blue-light" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" />
            </svg>
            Calendario de Tareas
        </h3>

        <div class="flex items-center justify-between">
            <button wire:click="prevMonth" type="button"
                class="rounded-md p-1 text-ipv-blue hover:bg-black/[0.03] dark:text-ipv-blue-light dark:hover:bg-white/5">
                <svg class="size-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 6l-6 6 6 6" />
                </svg>
            </button>
            <span class="whitespace-nowrap text-xs font-semibold capitalize text-ipv-ink/80 dark:text-ipv-ink-dark/85">
                {{ \Carbon\Carbon::create()->month($currentMonth)->locale('es')->translatedFormat('F') }} {{ $currentYear }}
            </span>
            <button wire:click="nextMonth" type="button"
                class="rounded-md p-1 text-ipv-blue hover:bg-black/[0.03] dark:text-ipv-blue-light dark:hover:bg-white/5">
                <svg class="size-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 6l6 6-6 6" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="flex min-h-0 flex-1 flex-col overflow-y-auto overflow-x-hidden p-2">
        <!-- Days of week -->
        <div class="mb-1 grid grid-cols-7 gap-1">
            @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $day)
                <div class="text-center text-[0.65rem] font-semibold uppercase tracking-wider text-ipv-ink/45 dark:text-ipv-ink-dark/45">{{ $day }}</div>
            @endforeach
        </div>

        <!-- Days Grid -->
        <div class="grid min-h-0 flex-1 grid-cols-7 gap-1">
            @foreach($this->daysInMonth as $index => $date)
                @if($date)
                    @php
                        $isToday = $date->isToday();
                        $dateStr = $date->format('Y-m-d');
                        $dayTasks = $this->tasks->get($dateStr, collect());
                        $isFuture = $date->isFuture();
                        $daysRemaining = $isFuture && $dayTasks->isNotEmpty() ? now()->startOfDay()->diffInDays($date->copy()->startOfDay()) : null;
                    @endphp

                    <div
                        wire:key="day-{{ $dateStr }}"
                        class="relative flex min-h-[3.5rem] flex-col gap-0.5 overflow-hidden rounded-md border p-1
                               {{ $isToday
                                   ? 'bg-ipv-blue/10 border-ipv-blue/35 dark:bg-ipv-blue-light/20 dark:border-ipv-blue-light/50'
                                   : 'bg-white/40 border-ipv-blue/[0.08] dark:bg-white/[0.04] dark:border-white/[0.08]' }}
                               {{ $this->hasPermission ? 'cursor-pointer hover:bg-white/60 dark:hover:bg-white/[0.08]' : '' }}"
                        @if($this->hasPermission) wire:click="openModal('{{ $dateStr }}')" @endif
                    >
                        @if($daysRemaining !== null)
                            <span class="absolute right-0.5 top-0.5 z-10 flex size-3.5 items-center justify-center rounded-full bg-ipv-magenta text-[8.5px] font-bold text-white">
                                {{ $daysRemaining }}
                            </span>
                        @endif

                        <span class="text-[0.7rem] font-medium {{ $isToday ? 'font-bold text-ipv-blue dark:text-ipv-blue-light' : 'text-ipv-ink/70 dark:text-ipv-ink-dark/70' }}">{{ $date->day }}</span>

                        <div class="no-scrollbar mt-1 flex-1 space-y-1 overflow-y-auto">
                            @foreach($dayTasks as $task)
                                @php
                                    $colorClasses = match(true) {
                                        $task->is_completed => 'bg-gray-500/10 text-gray-500 dark:text-gray-400 border-gray-400/25 line-through opacity-70',
                                        $task->color === 'blue' => 'bg-ipv-blue/15 text-ipv-blue border-ipv-blue/30 dark:bg-ipv-blue-light/20 dark:text-ipv-blue-light',
                                        $task->color === 'yellow' => 'bg-ipv-gold/20 text-[#7a5200] border-ipv-gold/40 dark:text-ipv-gold-light',
                                        $task->color === 'red' => 'bg-ipv-magenta/15 text-ipv-magenta border-ipv-magenta/30',
                                        default => 'bg-gray-500/10 text-gray-600 dark:text-gray-300 border-gray-400/25',
                                    };

                                    // Highlight if it's within 3 days, not completed, and not past due
                                    $isUpcoming = !$task->is_completed && $task->due_date->isBetween(now()->startOfDay(), now()->addDays(3)->endOfDay());
                                    $alertClasses = $isUpcoming ? 'animate-pulse' : '';
                                @endphp

                                <div
                                    wire:key="task-{{ $task->id }}"
                                    class="flex items-center justify-between truncate rounded border px-1 text-[0.65rem] leading-tight {{ $colorClasses }} {{ $alertClasses }}"
                                    title="{{ $task->title }} - {{ $task->assigned_to ?? 'Sin responsable' }}"
                                    @if($this->hasPermission)
                                        wire:click.stop="openModal(null, {{ $task->id }})"
                                    @endif
                                >
                                    <span class="truncate">{{ $task->title }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div wire:key="empty-{{ $index }}" class="min-h-[3.5rem] rounded bg-white/10 dark:bg-white/[0.02]"></div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Legend / Quick info -->
    @if(count($this->todayTasks) > 0)
    <div class="flex items-center gap-2 overflow-x-auto whitespace-nowrap border-t border-ipv-blue/10 bg-ipv-blue/[0.04] px-3 py-1.5 text-xs text-ipv-ink/80 dark:border-white/10 dark:bg-white/[0.03] dark:text-ipv-ink-dark/85">
        <span class="font-bold uppercase tracking-wide text-ipv-magenta">Hoy:</span>
        @foreach($this->todayTasks as $tt)
            <span class="inline-flex items-center gap-1">
                <span class="size-1.5 rounded-full {{ $tt->is_completed ? 'bg-gray-400' : 'bg-ipv-blue dark:bg-ipv-blue-light' }}"></span>
                {{ $tt->title }}
                @if($this->hasPermission && !$tt->is_completed)
                    <button wire:click="toggleCompletion({{ $tt->id }})" type="button" class="ml-1 text-ipv-blue hover:underline dark:text-ipv-blue-light">✓</button>
                @endif
            </span>
        @endforeach
    </div>
    @endif

    <!-- Task Modal -->
    @if($this->hasPermission && $showModal)
        {{-- Ventana movible: se arrastra desde la barra de título (doble clic = volver al centro). Una vez
             movida, el fondo se aclara y deja de desenfocar para poder ver el calendario. Solo se cierra al
             tocar el fondo si el clic EMPEZÓ en el fondo: arrastrar y soltar fuera de la ventana no la cierra. --}}
        <div
            x-data="{
                x: 0, y: 0, arrastrando: false, fuera: false, inicio: null, rect: null,
                empezar(e) {
                    if (e.target.closest('button')) return;
                    this.arrastrando = true;
                    this.rect = this.$refs.ventana.getBoundingClientRect();
                    this.inicio = { px: e.clientX, py: e.clientY, x: this.x, y: this.y };
                    e.currentTarget.setPointerCapture(e.pointerId);
                },
                mover(e) {
                    if (!this.arrastrando) return;
                    // La ventana queda siempre entera dentro de la pantalla; si es más grande que la pantalla,
                    // se puede arrastrar lo justo para ver el resto.
                    const r = this.rect;
                    const limitar = (v, a, b) => Math.min(Math.max(v, Math.min(a, b)), Math.max(a, b));
                    const izquierda = limitar(r.left + e.clientX - this.inicio.px, 0, window.innerWidth - r.width);
                    const arriba = limitar(r.top + e.clientY - this.inicio.py, 0, window.innerHeight - r.height);
                    this.x = this.inicio.x + (izquierda - r.left);
                    this.y = this.inicio.y + (arriba - r.top);
                },
                soltar() { this.arrastrando = false; },
            }"
            class="sirex-modal-fondo"
            :class="{ 'sirex-modal-fondo-movido': x !== 0 || y !== 0 }"
            x-on:pointerdown="fuera = ($event.target === $el)"
            x-on:click.self="if (fuera) $wire.closeModal(); fuera = false"
        >
            <div x-ref="ventana" :style="`transform: translate(${x}px, ${y}px)`" class="sirex-modal sirex-modal-sm" role="dialog" aria-modal="true">
                <div
                    class="flex cursor-grab touch-none select-none items-center justify-between border-b border-ipv-blue/10 px-4 pb-3.5 pt-5 active:cursor-grabbing dark:border-white/10"
                    title="Arrastrá para mover la ventana (doble clic: volver al centro)"
                    x-on:pointerdown="empezar($event)"
                    x-on:pointermove="mover($event)"
                    x-on:pointerup="soltar()"
                    x-on:pointercancel="soltar()"
                    x-on:dblclick="x = 0; y = 0"
                >
                    <h3 class="flex items-center gap-2 text-[15px] font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                        <svg class="size-4 shrink-0 text-ipv-ink/30 dark:text-ipv-ink-dark/35" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <circle cx="9" cy="6" r="1.5" /><circle cx="15" cy="6" r="1.5" /><circle cx="9" cy="12" r="1.5" />
                            <circle cx="15" cy="12" r="1.5" /><circle cx="9" cy="18" r="1.5" /><circle cx="15" cy="18" r="1.5" />
                        </svg>
                        @if($taskId)
                            @if($isEditing) Editar Tarea @else Detalles de Tarea @endif
                        @else
                            Nueva Tarea
                        @endif
                    </h3>
                    <div class="flex items-center gap-1.5">
                        @if($taskId && !$isEditing)
                            <button wire:click="$set('isEditing', true)" type="button"
                                class="rounded-lg bg-ipv-blue/10 p-1.5 text-ipv-blue dark:text-ipv-blue-light">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M15.2 5.2 18.7 8.7M16.7 3.7a2.5 2.5 0 1 1 3.5 3.5L6.5 21H3v-3.5L16.7 3.7Z" />
                                </svg>
                            </button>
                        @endif
                        <button wire:click="closeModal" type="button"
                            class="rounded-lg p-1.5 text-ipv-ink/45 hover:bg-black/[0.03] dark:text-ipv-ink-dark/50 dark:hover:bg-white/5">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <path d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="sirex-modal-cuerpo space-y-3.5 !pt-4">
                    @if(!$isEditing && $taskId)
                        <!-- Vista de Detalles (Solo Lectura) -->
                        <div>
                            <h4 class="text-lg font-bold text-ipv-ink dark:text-ipv-ink-dark">{{ $taskTitle }}</h4>
                            @if($taskDescription)
                                <p class="mt-2 whitespace-pre-wrap rounded-lg border border-ipv-blue/10 bg-ipv-blue/5 p-2.5 text-sm text-ipv-ink/80 dark:border-white/10 dark:bg-white/5 dark:text-ipv-ink-dark/80">{{ $taskDescription }}</p>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex flex-col">
                                <span class="mb-1 text-[10.5px] font-bold uppercase tracking-wider text-ipv-ink/45 dark:text-ipv-ink-dark/45">Responsable</span>
                                <span class="text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                    {{ $taskAssignedTo ?: 'Sin asignar' }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="mb-1 text-[10.5px] font-bold uppercase tracking-wider text-ipv-ink/45 dark:text-ipv-ink-dark/45">Fecha Límite</span>
                                <span class="text-sm font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                                    {{ \Carbon\Carbon::parse($taskDate)->format('d/m/Y') }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="mb-1 text-[10.5px] font-bold uppercase tracking-wider text-ipv-ink/45 dark:text-ipv-ink-dark/45">Estado</span>
                                @php
                                    $task = App\Models\Task::find($taskId);
                                @endphp
                                <span class="text-sm font-bold {{ $task && $task->is_completed ? 'text-ipv-blue dark:text-ipv-blue-light' : 'text-[#7a5200] dark:text-ipv-gold-light' }}">
                                    {{ $task && $task->is_completed ? 'Completado' : 'Pendiente' }}
                                </span>
                            </div>
                        </div>
                    @else
                        <!-- Formulario de Edición/Creación -->
                        <div>
                            <label class="sirex-etiqueta">Título de tarea</label>
                            <input type="text" wire:model="taskTitle" class="sirex-campo">
                            @error('taskTitle') <p class="sirex-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="sirex-etiqueta">Descripción (opcional)</label>
                            <textarea wire:model="taskDescription" rows="3" class="sirex-campo resize-none"></textarea>
                            @error('taskDescription') <p class="sirex-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="sirex-etiqueta">Fecha</label>
                                <input type="date" wire:model="taskDate" class="sirex-campo">
                                @error('taskDate') <p class="sirex-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="sirex-etiqueta">Color</label>
                                <select wire:model="taskColor" class="sirex-campo">
                                    <option value="blue">Azul institucional</option>
                                    <option value="yellow">Dorado</option>
                                    <option value="red">Urgente</option>
                                    <option value="green">Neutro</option>
                                    <option value="purple">Neutro</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="sirex-etiqueta">Responsable</label>
                            <select wire:model="taskAssignedTo" class="sirex-campo">
                                <option value="">Seleccionar responsable...</option>
                                @foreach($this->availableNames as $name)
                                    <option value="{{ $name }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('taskAssignedTo') <p class="sirex-error">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="sirex-modal-pie !justify-between">
                    <div>
                        @if($taskId)
                            @php $currentTask = App\Models\Task::find($taskId); @endphp
                            <button wire:click="toggleCompletion({{ $taskId }})" type="button"
                                class="sirex-boton sirex-boton-chico {{ $currentTask && $currentTask->is_completed ? 'sirex-boton-secundario' : 'sirex-boton-ok' }}">
                                {{ $currentTask && $currentTask->is_completed ? 'Volver a activar' : 'Completar' }}
                            </button>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        @if($taskId)
                            <button wire:click="deleteTask({{ $taskId }})" type="button"
                                onclick="confirm('¿Estás seguro de eliminar esta tarea?') || event.stopImmediatePropagation()"
                                class="sirex-boton sirex-boton-chico sirex-boton-peligro">
                                Eliminar
                            </button>
                        @endif
                        @if(!$taskId || $isEditing)
                            <button wire:click="saveTask" type="button" class="sirex-boton sirex-boton-chico sirex-boton-primario">
                                Guardar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
