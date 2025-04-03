@props(['class' => ''])

<th {{ $attributes->merge(['class' => 'px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider ' . $class]) }}>
    {{ $slot }}
</th>