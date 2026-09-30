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

// Luz que sigue al mouse sobre las tarjetas (.sirex-glass-card). Un solo listener
// delegado y como mucho una actualización por frame; se desactiva en pantallas táctiles
// y si el usuario pidió reducir el movimiento.
if (window.matchMedia('(pointer: fine)').matches && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let frame = null
    let evento = null

    document.addEventListener('pointermove', (e) => {
        evento = e
        if (frame) return

        frame = requestAnimationFrame(() => {
            frame = null
            const tarjeta = evento.target instanceof Element ? evento.target.closest('.sirex-glass-card') : null
            if (!tarjeta) return

            const caja = tarjeta.getBoundingClientRect()
            tarjeta.style.setProperty('--mx', `${evento.clientX - caja.left}px`)
            tarjeta.style.setProperty('--my', `${evento.clientY - caja.top}px`)
        })
    }, { passive: true })
}

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
