<?php

use Livewire\Volt\Component;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

new class extends Component {
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
        return [
            'Lucas Carbone', 'Sebastian Carabajal', 'Josefina Katz', 'Carolina San Juan', 
            'Luis Leguizamon', 'Leo Ocaranza', 'Bruno Abbatepaolo', 'Mariana Remis', 'Luis Castro', 'Diego Martin', 'Jorge Debbo'
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
        if (!$this->hasPermission) return;

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
        if (!$this->hasPermission) return;

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
        if (!$this->hasPermission) return;

        $task = Task::find($taskId);
        if ($task) {
            $task->update(['is_completed' => !$task->is_completed]);
        }
    }

    public function deleteTask($taskId)
    {
        if (!$this->hasPermission) return;

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
            ->groupBy(function($task) {
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

<div class="relative flex flex-col h-full min-h-[400px] xl:min-h-[450px] overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-800 min-w-0">
    <div class="flex items-center justify-between p-3 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-gray-800 dark:text-gray-200 font-semibold text-sm md:text-base flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Calendario de Tareas
        </h3>
        
        <div class="flex items-center space-x-2">
            <button wire:click="prevMonth" class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 w-24 text-center">
                {{ \Carbon\Carbon::create()->month($currentMonth)->locale('es')->translatedFormat('F') }} {{ $currentYear }}
            </span>
            <button wire:click="nextMonth" class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="flex-1 flex flex-col p-2 overflow-y-auto overflow-x-hidden min-h-0">
        <!-- Days of week -->
        <div class="grid grid-cols-7 gap-1 mb-1">
            @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $day)
                <div class="text-[0.65rem] uppercase tracking-wider font-semibold text-center text-gray-500">{{ $day }}</div>
            @endforeach
        </div>
        
        <!-- Days Grid -->
        <div class="grid grid-cols-7 gap-1 flex-1 min-h-0">
            @foreach($this->daysInMonth as $index => $date)
                @if($date)
                    @php
                        $isToday = $date->isToday();
                        $dateStr = $date->format('Y-m-d');
                        $dayTasks = $this->tasks->get($dateStr, collect());
                        
                        $remainingDaysHtml = '';
                        if ($date->isFuture() && $dayTasks->isNotEmpty()) {
                            $daysRemaining = now()->startOfDay()->diffInDays($date->startOfDay());
                            $remainingDaysHtml = '<div class="absolute top-1 right-1 w-4 h-4 flex items-center justify-center text-[0.6rem] font-bold text-white bg-red-500 rounded-full shadow-sm z-10">' . $daysRemaining . '</div>';
                        }
                    @endphp
                    
                    <div 
                        wire:key="day-{{ $dateStr }}"
                        class="flex flex-col border border-gray-100 dark:border-gray-700 rounded p-1 min-h-[3.5rem] relative {{ $isToday ? 'bg-indigo-50 dark:bg-indigo-900/20 ring-1 ring-indigo-300' : 'bg-white dark:bg-gray-800' }} {{ $this->hasPermission ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750' : '' }}"
                        @if($this->hasPermission) wire:click="openModal('{{ $dateStr }}')" @endif
                    >
                        {!! $remainingDaysHtml !!}
                        <span class="text-[0.7rem] font-medium {{ $isToday ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400' }}">{{ $date->day }}</span>
                        
                        <div class="flex-1 overflow-y-auto space-y-1 mt-1 no-scrollbar">
                            @foreach($dayTasks as $task)
                                @php
                                    $colorClasses = match($task->color) {
                                        'red' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/50 dark:text-red-200 dark:border-red-800',
                                        'green' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/50 dark:text-green-200 dark:border-green-800',
                                        'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/50 dark:text-yellow-200 dark:border-yellow-800',
                                        'purple' => 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/50 dark:text-purple-200 dark:border-purple-800',
                                        default => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/50 dark:text-blue-200 dark:border-blue-800',
                                    };
                                    
                                    // Highlight if it's within 3 days, not completed, and not past due
                                    $isUpcoming = !$task->is_completed && $task->due_date->isBetween(now()->startOfDay(), now()->addDays(3)->endOfDay());
                                    $isOverdue = !$task->is_completed && $task->due_date->isBefore(now()->startOfDay());
                                    
                                    $alertClasses = '';
                                    if ($isUpcoming) $alertClasses = 'animate-pulse ring-1 ring-offset-1 ring-yellow-400';
                                    if ($isOverdue) $alertClasses = 'ring-1 ring-offset-1 ring-red-500';
                                    if ($task->is_completed) $colorClasses = 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-gray-800 dark:text-gray-400 opacity-60 line-through';
                                @endphp
                                
                                <div 
                                    wire:key="task-{{ $task->id }}"
                                    class="flex items-center justify-between text-[0.65rem] px-1 rounded border leading-tight truncate {{ $colorClasses }} {{ $alertClasses }}"
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
                    <div wire:key="empty-{{ $index }}" class="bg-gray-50 dark:bg-gray-800/50 rounded p-1 min-h-[3.5rem]"></div>
                @endif
            @endforeach
        </div>
    </div>
    
    <!-- Legend / Quick info -->
    @if(count($this->todayTasks) > 0)
    <div class="px-3 py-1.5 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 flex items-center gap-2 overflow-x-auto whitespace-nowrap">
        <span class="font-bold text-red-600 dark:text-red-400 uppercase tracking-wide">Ojo hoy:</span>
        @foreach($this->todayTasks as $tt)
            <span class="inline-flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full {{ $tt->is_completed ? 'bg-gray-400' : 'bg-red-500' }}"></span>
                {{ $tt->title }}
                @if($this->hasPermission && !$tt->is_completed)
                    <button wire:click="toggleCompletion({{ $tt->id }})" class="text-indigo-600 hover:text-indigo-800 ml-1">✓</button>
                @endif
            </span>
        @endforeach
    </div>
    @endif

    <!-- Task Modal -->
    @if($this->hasPermission && $showModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50" wire:click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-sm overflow-hidden flex flex-col">
                <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">
                        @if($taskId)
                            @if($isEditing) Editar Tarea @else Detalles de Tarea @endif
                        @else
                            Nueva Tarea
                        @endif
                    </h3>
                    <div class="flex items-center gap-2">
                        @if($taskId && !$isEditing)
                            <button wire:click="$set('isEditing', true)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 p-1.5 rounded-full" title="Editar tarea">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                        @endif
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-4 space-y-4">
                    @if(!$isEditing && $taskId)
                        <!-- Vista de Detalles (Solo Lectura) -->
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $taskTitle }}</h4>
                            @if($taskDescription)
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-600 whitespace-pre-wrap">{{ $taskDescription }}</p>
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Responsable</span>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $taskAssignedTo ?: 'Sin asignar' }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Fecha Límite</span>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ \Carbon\Carbon::parse($taskDate)->format('d/m/Y') }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Estado</span>
                                @php
                                    $task = App\Models\Task::find($taskId);
                                @endphp
                                <span class="text-sm font-bold {{ $task && $task->is_completed ? 'text-green-600' : 'text-amber-600' }}">
                                    {{ $task && $task->is_completed ? 'Completado' : 'Pendiente' }}
                                </span>
                            </div>
                        </div>
                    @else
                        <!-- Formulario de Edición/Creación -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Título de Tarea</label>
                            <input type="text" wire:model="taskTitle" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                            @error('taskTitle') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción (Opcional)</label>
                            <textarea wire:model="taskDescription" rows="3" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border resize-none"></textarea>
                            @error('taskDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                            <input type="date" wire:model="taskDate" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                            @error('taskDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                            <select wire:model="taskColor" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                                <option value="blue">Azul</option>
                                <option value="green">Verde</option>
                                <option value="yellow">Amarillo</option>
                                <option value="red">Rojo</option>
                                <option value="purple">Púrpura</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Responsable</label>
                        <select wire:model="taskAssignedTo" class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                            <option value="">Seleccionar responsable...</option>
                            @foreach($this->availableNames as $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('taskAssignedTo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    @endif
                </div>
                
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-between">
                    <div>
                        @if($taskId)
                            @php $currentTask = App\Models\Task::find($taskId); @endphp
                            <button wire:click="toggleCompletion({{ $taskId }})" class="mr-2 text-xs px-3 py-1.5 border rounded-md shadow-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 flex items-center {{ $currentTask && $currentTask->is_completed ? 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50' : 'border-transparent text-white bg-green-600 hover:bg-green-700 focus:ring-green-500' }}">
                                {{ $currentTask && $currentTask->is_completed ? 'Volver a activar' : 'Completar' }}
                            </button>
                        @endif
                    </div>
                    
                    <div class="flex space-x-2">
                        @if($taskId)
                            <button wire:click="deleteTask({{ $taskId }})" onclick="confirm('¿Estás seguro de eliminar esta tarea?') || event.stopImmediatePropagation()" class="text-xs px-3 py-1.5 border border-transparent rounded-md shadow-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Eliminar
                            </button>
                        @endif
                        @if(!$taskId || $isEditing)
                            <button wire:click="saveTask" class="text-xs px-4 py-1.5 border border-transparent rounded-md shadow-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Guardar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
