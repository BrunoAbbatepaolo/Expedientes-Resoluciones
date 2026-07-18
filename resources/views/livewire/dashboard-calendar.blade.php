<?php

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    public $currentMonth;

    public $currentYear;

    // Modal state
    public $showModal = false;

    public $taskId = null;

    public $taskTitle = '';

    public $taskDescription = '';

    public $taskDate = '';

    public $taskColor = 'blue';

    public $taskAssignedTo = '';

    public $isEditing = true;

    // TEMPORAL: Permisos manejados desde el front (forzamos a true para que puedas probar)
    public $hasPermission = true;

    public function mount()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    #[Computed]
    public function availableNames()
    {
        return ['Luis Leguizamon', 'Leo Ocaranza', 'Bruno Abbatepaolo', 'Mariana Remis', 'Luis Castro', 'Diego Martin', 'Jorge Debbo',
        ];
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
            $task = Task::find($this->taskId);
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
            <flux:icon.calendar-days variant="outline" class="size-4 shrink-0 !text-ipv-blue dark:!text-ipv-blue-light" />
            Calendario de Tareas
        </h3>

        <div class="flex items-center justify-between">
            <flux:button size="xs" variant="ghost" icon="chevron-left" wire:click="prevMonth" />
            <span class="whitespace-nowrap text-xs font-semibold capitalize text-ipv-ink/80 dark:text-ipv-ink-dark/85">
                {{ \Carbon\Carbon::create()->month($currentMonth)->locale('es')->translatedFormat('F') }} {{ $currentYear }}
            </span>
            <flux:button size="xs" variant="ghost" icon="chevron-right" wire:click="nextMonth" />
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
                    <button wire:click="toggleCompletion({{ $tt->id }})" class="ml-1 text-ipv-blue hover:underline dark:text-ipv-blue-light">✓</button>
                @endif
            </span>
        @endforeach
    </div>
    @endif

    <!-- Task Modal -->
    @if($this->hasPermission && $showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-[#001428]/50 p-4 backdrop-blur-sm" wire:click.self="closeModal">
            <div class="flex w-full max-w-sm flex-col overflow-hidden rounded-2xl border border-ipv-blue/20 bg-white/90 shadow-2xl backdrop-blur-2xl backdrop-saturate-150 dark:border-white/10 dark:bg-[#141c26]/95">
                <div class="flex items-center justify-between border-b border-ipv-blue/10 px-4 py-4 dark:border-white/10">
                    <h3 class="font-semibold text-ipv-ink dark:text-ipv-ink-dark">
                        @if($taskId)
                            @if($isEditing) Editar Tarea @else Detalles de Tarea @endif
                        @else
                            Nueva Tarea
                        @endif
                    </h3>
                    <div class="flex items-center gap-1.5">
                        @if($taskId && !$isEditing)
                            <flux:button size="xs" variant="ghost" icon="pencil" wire:click="$set('isEditing', true)" />
                        @endif
                        <flux:button size="xs" variant="ghost" icon="x-mark" wire:click="closeModal" />
                    </div>
                </div>

                <div class="space-y-4 p-4">
                    @if(!$isEditing && $taskId)
                        <!-- Vista de Detalles (Solo Lectura) -->
                        <div>
                            <h4 class="text-lg font-bold text-ipv-ink dark:text-ipv-ink-dark">{{ $taskTitle }}</h4>
                            @if($taskDescription)
                                <p class="mt-2 whitespace-pre-wrap rounded-lg border border-ipv-blue/10 bg-ipv-blue/5 p-3 text-sm text-ipv-ink/80 dark:border-white/10 dark:bg-white/5 dark:text-ipv-ink-dark/80">{{ $taskDescription }}</p>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div class="flex flex-col">
                                <span class="mb-1 text-xs font-semibold uppercase tracking-wider text-ipv-ink/45 dark:text-ipv-ink-dark/45">Responsable</span>
                                <span class="text-sm font-medium text-ipv-ink dark:text-ipv-ink-dark">
                                    {{ $taskAssignedTo ?: 'Sin asignar' }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="mb-1 text-xs font-semibold uppercase tracking-wider text-ipv-ink/45 dark:text-ipv-ink-dark/45">Fecha Límite</span>
                                <span class="text-sm font-medium text-ipv-ink dark:text-ipv-ink-dark">
                                    {{ \Carbon\Carbon::parse($taskDate)->format('d/m/Y') }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="mb-1 text-xs font-semibold uppercase tracking-wider text-ipv-ink/45 dark:text-ipv-ink-dark/45">Estado</span>
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
                        <flux:input label="Título de Tarea" wire:model="taskTitle" />
                        @error('taskTitle') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                        <flux:textarea label="Descripción (Opcional)" wire:model="taskDescription" rows="3" />
                        @error('taskDescription') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <flux:input type="date" label="Fecha" wire:model="taskDate" />
                                @error('taskDate') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <flux:select label="Color" wire:model="taskColor">
                                <flux:select.option value="blue">Azul institucional</flux:select.option>
                                <flux:select.option value="yellow">Dorado</flux:select.option>
                                <flux:select.option value="red">Urgente</flux:select.option>
                                <flux:select.option value="green">Neutro</flux:select.option>
                                <flux:select.option value="purple">Neutro</flux:select.option>
                            </flux:select>
                        </div>

                        <flux:select label="Responsable" wire:model="taskAssignedTo">
                            <flux:select.option value="">Seleccionar responsable...</flux:select.option>
                            @foreach($this->availableNames as $name)
                                <flux:select.option :value="$name">{{ $name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('taskAssignedTo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    @endif
                </div>

                <div class="flex justify-between border-t border-ipv-blue/10 bg-ipv-blue/[0.03] px-4 py-4 dark:border-white/10 dark:bg-white/[0.03]">
                    <div>
                        @if($taskId)
                            @php $currentTask = App\Models\Task::find($taskId); @endphp
                            <flux:button size="sm" :variant="$currentTask && $currentTask->is_completed ? 'ghost' : 'primary'" wire:click="toggleCompletion({{ $taskId }})">
                                {{ $currentTask && $currentTask->is_completed ? 'Volver a activar' : 'Completar' }}
                            </flux:button>
                        @endif
                    </div>

                    <div class="flex space-x-2">
                        @if($taskId)
                            <flux:button size="sm" class="!bg-ipv-magenta !text-white" wire:click="deleteTask({{ $taskId }})" onclick="confirm('¿Estás seguro de eliminar esta tarea?') || event.stopImmediatePropagation()">
                                Eliminar
                            </flux:button>
                        @endif
                        @if(!$taskId || $isEditing)
                            <flux:button size="sm" class="!bg-ipv-blue !text-white" wire:click="saveTask">
                                Guardar
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
