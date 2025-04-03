@props(['class' => '', 'click' => null])

<td
    @if($click) wire:click="{{ $click }}" @endif
    {{ $attributes->merge(['class' => 'px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 ' . ($click ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800' : '') . ' ' . $class]) }}>
    {{ $slot }}
</td>