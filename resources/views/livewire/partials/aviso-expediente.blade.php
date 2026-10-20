{{-- Aviso debajo del campo "Número de expediente": confirma que existe (con su causante) o avisa que no se puede guardar. --}}
@php($vinculo = $this->expedienteVinculado)
@if ($vinculo['estado'] === 'ok')
    <p class="text-xs font-medium text-ipv-blue dark:text-ipv-blue-light">✓ Expediente encontrado{{ $vinculo['detalle'] ? ': '.$vinculo['detalle'] : '' }}</p>
    @if ($this->titularSugerido)
        <button type="button" wire:click="usarTitularSugerido" class="self-start rounded-full border border-ipv-blue/30 px-2.5 py-0.5 text-xs font-semibold text-ipv-blue hover:bg-ipv-blue/[0.08] dark:text-ipv-blue-light">
            Usar «{{ $this->titularSugerido }}» como titular
        </button>
    @endif
@elseif ($vinculo['estado'] === 'no')
    <p class="text-xs font-medium text-ipv-magenta dark:text-ipv-magenta-light">No existe ese expediente. Sin expediente no se puede guardar la resolución.</p>
@elseif ($vinculo['estado'] === 'error')
    <p class="text-xs text-ipv-ink/60 dark:text-ipv-ink-dark/60">No se pudo verificar el expediente ahora; se vuelve a comprobar al guardar.</p>
@endif
