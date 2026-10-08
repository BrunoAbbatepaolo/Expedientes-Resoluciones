@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'sirex-error']) }}>{{ $message }}</p>
@enderror
