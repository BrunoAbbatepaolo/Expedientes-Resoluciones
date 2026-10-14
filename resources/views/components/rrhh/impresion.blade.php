{{-- Hoja de RRHH para imprimir / guardar como PDF: usa el formato institucional compartido (components/documento). --}}
@props(['titulo', 'subtitulo' => null, 'orientacion' => 'portrait', 'firmas' => false])
<x-documento :titulo="$titulo" tipo="Reporte" :subtitulo="$subtitulo" dependencia="Recursos Humanos" :orientacion="$orientacion" :firmas="$firmas">
    {{ $slot }}
    <p class="nota">Datos del sistema de personal del instituto. Información de uso administrativo.</p>
</x-documento>
