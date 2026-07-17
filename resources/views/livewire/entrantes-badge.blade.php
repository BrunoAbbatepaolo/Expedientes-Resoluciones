<span wire:poll.30s>
    @if ($count > 0)
        <span
            class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full bg-red-500 text-white text-xs font-semibold">
            {{ $count }}
        </span>
    @endif
</span>
