@props(['mostrar' => false])

@if ($mostrar)
    <div role="alert" class="rounded-lg border border-ipv-magenta/30 bg-ipv-magenta/10 px-4 py-3 text-sm text-ipv-magenta dark:text-ipv-magenta-light">
        No se pudieron consultar los datos de RRHH del sistema viejo. Probá de nuevo en unos minutos.
    </div>
@endif
