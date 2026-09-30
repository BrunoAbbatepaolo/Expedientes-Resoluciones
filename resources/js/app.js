import Swal from 'sweetalert2'
import './quill-editor'

window.Swal = Swal

// Toasts: los componentes Livewire hacen $this->dispatch('toast', type: ..., message: ...)
// (ver App\Traits\NotificaUi). Reemplaza a Flux::toast y livewire-alert.
document.addEventListener('livewire:init', () => {
    Livewire.on('toast', ({ type = 'success', message = '', timer = 2500 }) => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            timer,
            timerProgressBar: true,
            showConfirmButton: false,
        })
    })
})

// Tema: 'light' | 'dark' guardado en localStorage; sin valor = sigue al sistema.
// El script inline de partials/head aplica la clase antes de pintar (sin parpadeo).
window.aplicarTema = function (tema) {
    try {
        if (tema === 'light' || tema === 'dark') {
            localStorage.setItem('theme', tema)
        } else {
            localStorage.removeItem('theme')
        }
    } catch (e) {
        // localStorage bloqueado: el tema se aplica igual en esta página.
    }

    const oscuro = tema === 'dark'
        || ((tema !== 'light') && window.matchMedia('(prefers-color-scheme: dark)').matches)

    document.documentElement.classList.toggle('dark', oscuro)
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: oscuro, tema } }))
}

window.temaActual = function () {
    try {
        return localStorage.getItem('theme') || 'system'
    } catch (e) {
        return 'system'
    }
}
