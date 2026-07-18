<span wire:poll.30s>
    @if ($count > 0)
        <span
            class="inline-flex min-w-[19px] h-[19px] items-center justify-center rounded-full bg-ipv-gold px-1.5 text-[11px] font-extrabold text-ipv-gold-ink dark:text-ipv-gold-ink-dark animate-[sirex-badge-pop_0.4s_ease-out]">
            {{ $count }}
        </span>
    @endif
</span>
