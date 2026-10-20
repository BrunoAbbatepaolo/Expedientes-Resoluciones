<?php

use App\Http\Controllers\ExportarResolucionesController;
use App\Http\Controllers\ImprimirExpedienteIpvController;
use App\Http\Controllers\ImprimirResolucionController;
use App\Http\Controllers\InformesExpedientesController;
use App\Http\Controllers\IngresosOficinaCsvController;
use App\Http\Controllers\ResolucionArchivoController;
use App\Http\Controllers\Rrhh\AdjuntoPedidoController;
use App\Http\Controllers\Rrhh\ArchivoEmpleadoController;
use App\Http\Controllers\Rrhh\ReporteController;
use App\Livewire\Admin;
use App\Livewire\ConsultaIpv;
use App\Livewire\FichaIpv;
use App\Livewire\IngresosOficina;
use App\Livewire\PasesEmitidos;
use App\Livewire\Rrhh;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// La entrada es el login (sin página de presentación): con sesión va directo al panel.
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'password.change'])
    ->name('dashboard');

// Fuera del grupo protegido por password.change: si el usuario tiene la
// contraseña provisoria, esta es la única ruta a la que puede llegar además
// de logout (ver App\Http\Middleware\EnsurePasswordIsChanged).
Route::middleware(['auth'])->group(function () {
    Volt::route('cambiar-clave-inicial', 'auth.cambiar-clave-inicial')->name('cambiar-clave-inicial');
});

Route::middleware(['auth', 'password.change'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('resoluciones', 'resoluciones')->middleware('auth')->name('resoluciones');
    Volt::route('expedientes', 'expedientes')->middleware('auth')->name('expedientes');
    Volt::route('expedientes/ingresados', 'expedientes')->middleware('auth')->name('expedientes.ingresados');
    Volt::route('expedientes/egresados', 'expedientes')->middleware('auth')->name('expedientes.egresados');
    Volt::route('expedientes/entrantes', 'expedientes')->middleware('auth')->name('expedientes.entrantes');
    Volt::route('expedientes/detalle/{id}', 'detalles')->middleware('auth')->name('expedientes.detalle');

    // Consulta de todo el IPV (solo lectura sobre el sistema viejo).
    Route::livewire('expedientes/ipv', ConsultaIpv::class)->name('expedientes.ipv');
    Route::livewire('expedientes/ipv/ingresos', IngresosOficina::class)->name('expedientes.ipv.ingresos');
    Route::get('expedientes/ipv/ingresos/csv', IngresosOficinaCsvController::class)->name('expedientes.ipv.ingresos.csv');
    Route::get('expedientes/ipv/ingresos/imprimir', [InformesExpedientesController::class, 'ingresosImprimir'])->name('expedientes.ipv.ingresos.imprimir');
    Route::livewire('expedientes/ipv/pases-emitidos', PasesEmitidos::class)->name('expedientes.ipv.pases');
    Route::get('expedientes/ipv/pases-emitidos/csv', [InformesExpedientesController::class, 'pasesCsv'])->name('expedientes.ipv.pases.csv');
    Route::get('expedientes/ipv/pases-emitidos/imprimir', [InformesExpedientesController::class, 'pasesImprimir'])->name('expedientes.ipv.pases.imprimir');
    Route::livewire('expedientes/ipv/{id}', FichaIpv::class)->whereNumber('id')->name('expedientes.ipv.ficha');
    Route::get('expedientes/ipv/{id}/imprimir', ImprimirExpedienteIpvController::class)->whereNumber('id')->name('expedientes.ipv.imprimir');
    // RRHH (fase 1: consulta de solo lectura del sistema viejo). Ver docs/spec-modulo-rrhh.md.
    Route::prefix('rrhh')->name('rrhh.')->group(function () {
        Route::livewire('/', Rrhh\MiRrhh::class)->name('mio');
        Route::livewire('calendario', Rrhh\Calendario::class)->name('calendario');
        Route::livewire('equipo', Rrhh\MiEquipo::class)->name('equipo');
        Route::livewire('personal', Rrhh\Personal::class)->name('personal');
        Route::livewire('asistencia', Rrhh\Asistencia::class)->name('asistencia');
        Route::livewire('licencias', Rrhh\Licencias::class)->name('licencias');
        Route::livewire('organigrama', Rrhh\Organigrama::class)->name('organigrama');
        Route::livewire('reportes', Rrhh\Reportes::class)->name('reportes');
        Route::get('reportes/{reporte}', ReporteController::class)->name('reportes.ver');
        Route::get('pedidos/{pedido}/adjunto', AdjuntoPedidoController::class)->whereNumber('pedido')->name('pedido.adjunto');
        Route::livewire('personal/{empleado}', Rrhh\FichaEmpleado::class)->whereNumber('empleado')->name('ficha');
        Route::get('personal/{empleado}/archivos/{archivo}', ArchivoEmpleadoController::class)
            ->whereNumber(['empleado', 'archivo'])
            ->name('archivo');
    });

    Route::livewire('admin', Admin\PanelAdmin::class)->name('admin');
    Volt::route('oficinas', 'oficinas')->middleware('auth')->name('oficinas');
    Volt::route('usuarios', 'ListaUsuario')->middleware('auth')->name('listausuarios');
    Volt::route('/resoluciones/elegir', 'ElegirResolucion')
        ->middleware('auth')
        ->name('resoluciones.elegir');
    Volt::route('/resoluciones/crear/{tipo}', 'CrearResolucion')
        ->middleware('auth')
        ->name('resoluciones.crear');

    // PDFs de resoluciones: se sirven desde disco privado, con autorización.
    Route::get('/resoluciones/descargar-pdf/{index}', [ResolucionArchivoController::class, 'temporal'])
        ->whereNumber('index')
        ->name('resoluciones.descargarPdf');
    Route::get('/resoluciones/archivo/{archivo}', [ResolucionArchivoController::class, 'archivo'])
        ->name('resoluciones.archivo');
    Route::get('/resoluciones/{resolucion}/pdf', [ResolucionArchivoController::class, 'pdf'])
        ->whereNumber('resolucion')
        ->name('resoluciones.pdf');
    Route::get('/resoluciones/exportar', ExportarResolucionesController::class)
        ->name('resoluciones.exportar');
    Route::get('/resoluciones/{resolucion}/imprimir', ImprimirResolucionController::class)
        ->whereNumber('resolucion')
        ->name('resoluciones.imprimir');
});

require __DIR__.'/auth.php';
