<!-- path: resources/views/partials/head.blade.php -->
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    @if (request()->routeIs('dashboard'))
        Panel de Control
    @elseif(request()->routeIs('expedientes*'))
        Gestion de Expedientes
    @elseif(request()->routeIs('resoluciones*'))
        Gestion de Resoluciones
    @elseif(request()->routeIs('listausuarios*'))
        Gestion de Usuarios
    @elseif (request()->routeIs('oficinas*'))
        Lista de Oficinas
    @else
        {{ 'Instituto Provincial de la Vivienda' ?? config('app.name') }}
    @endif
</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
