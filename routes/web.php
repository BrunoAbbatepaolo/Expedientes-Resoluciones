<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/expedientes/public/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get('/expedientes/public/livewire/livewire.js', $handle);
});
// Redirigir la ruta principal al login
Route::redirect('/', '/expedientes/public/login');
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('resoluciones', 'resoluciones')->middleware('auth')->name('resoluciones');

    Volt::route('expedientes', 'expedientes')->middleware('auth')->name('expedientes');
    Volt::route('expedientes/ingresados', 'expedientes')->middleware('auth')->name('expedientes.ingresados');
    Volt::route('expedientes/egresados', 'expedientes')->middleware('auth')->name('expedientes.egresados');
    Volt::route('expedientes/detalle/{id}', 'detalles')->middleware('auth')->name('expedientes.detalle');

    Volt::route('oficinas', 'oficinas')->middleware('auth')->name('oficinas');
    Volt::route('usuarios', 'ListaUsuario')->middleware('auth')->name('listausuarios');
    Volt::route('/resoluciones/elegir', 'ElegirResolucion')
        ->middleware('auth')
        ->name('resoluciones.elegir');
    Volt::route('/resoluciones/crear/{tipo}', 'CrearResolucion')
        ->middleware('auth')
        ->name('resoluciones.crear');
});

require __DIR__ . '/auth.php';
