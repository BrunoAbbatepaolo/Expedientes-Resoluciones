<?php

use App\Http\Controllers\ImprimirExpedienteIpvController;
use App\Http\Controllers\ResolucionArchivoController;
use App\Livewire\ConsultaIpv;
use App\Livewire\FichaIpv;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
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
    Route::livewire('expedientes/ipv/{id}', FichaIpv::class)->whereNumber('id')->name('expedientes.ipv.ficha');
    Route::get('expedientes/ipv/{id}/imprimir', ImprimirExpedienteIpvController::class)->whereNumber('id')->name('expedientes.ipv.imprimir');
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
});

require __DIR__.'/auth.php';
