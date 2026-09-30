<?php

namespace App\Traits;

/**
 * Reemplaza a Flux (modal()->close(), Flux::toast) y a livewire-alert.
 * Los eventos los escuchan las vistas (modal-close) y resources/js/app.js (toast).
 */
trait NotificaUi
{
    protected function cerrarModal(string $nombre): void
    {
        $this->dispatch('modal-close', name: $nombre);
    }

    /**
     * @param  'success'|'error'|'warning'|'info'  $tipo
     */
    protected function toast(string $tipo, string $mensaje, int $timer = 2500): void
    {
        $this->dispatch('toast', type: $tipo, message: $mensaje, timer: $timer);
    }
}
