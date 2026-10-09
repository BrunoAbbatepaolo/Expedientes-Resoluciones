import Swal from 'sweetalert2'
import './quill-editor'

window.Swal = Swal

// Toasts: los componentes Livewire hacen $this->dispatch('toast', type: ..., message: ...)
// (ver App\Traits\NotificaUi). Reemplaza a Flux::toast y livewire-alert.
const ICONOS_TOAST = {
    success: 'M5 13l4 4L19 7',
    error: 'M6 18L18 6M6 6l12 12',
    warning: 'M12 8v5m0 3.5h.01',
    info: 'M12 11v6m0-9.5h.01',
}

// Toast propio: entra deslizando, ícono animado según el tipo y barra de tiempo; se pausa con el mouse.
function mostrarToast({ type = 'success', message = '', timer = 2500 }) {
    let pila = document.getElementById('sirex-toasts')
    if (!pila) {
        pila = document.createElement('div')
        pila.id = 'sirex-toasts'
        pila.className = 'sirex-toasts'
        pila.setAttribute('role', 'status')
        pila.setAttribute('aria-live', 'polite')
        document.body.appendChild(pila)
    }

    const tipo = ICONOS_TOAST[type] ? type : 'info'
    const toast = document.createElement('div')
    toast.className = 'sirex-toast'
    toast.dataset.tipo = tipo
    toast.style.setProperty('--dur', `${timer}ms`)
    toast.innerHTML = `
        <span class="sirex-toast-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path class="sirex-trazo" d="${ICONOS_TOAST[tipo]}"/></svg></span>
        <span class="sirex-toast-texto"></span>
        <span class="sirex-toast-barra"></span>`
    toast.querySelector('.sirex-toast-texto').textContent = message
    pila.appendChild(toast)

    let cierre = null
    const cerrar = () => {
        toast.dataset.saliendo = ''
        toast.addEventListener('animationend', () => toast.remove(), { once: true })
        setTimeout(() => toast.remove(), 400)
    }
    const programar = () => { cierre = setTimeout(cerrar, timer) }
    programar()
    toast.addEventListener('mouseenter', () => { clearTimeout(cierre); toast.querySelector('.sirex-toast-barra').style.animationPlayState = 'paused' })
    toast.addEventListener('mouseleave', () => { toast.querySelector('.sirex-toast-barra').style.animationPlayState = 'running'; programar() })
    toast.addEventListener('click', cerrar)
}

window.mostrarToast = mostrarToast

document.addEventListener('livewire:init', () => {
    Livewire.on('toast', (datos) => mostrarToast(datos))
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

function reaplicarTema() {
    let tema = null
    try {
        tema = localStorage.getItem('theme')
    } catch (e) {
        // localStorage bloqueado: se sigue al sistema.
    }
    const oscuro = tema === 'dark' || (!tema && window.matchMedia('(prefers-color-scheme: dark)').matches)
    document.documentElement.classList.toggle('dark', oscuro)
}

// wire:navigate copia los atributos del <html> que manda el servidor (sin la clase 'dark') y
// no vuelve a correr el script inline del <head>: hay que reaplicar el tema en cada navegación.
// onSwap lo hace antes de pintar (sin parpadeo); 'navigated' queda de respaldo.
document.addEventListener('livewire:navigating', (e) => {
    e.detail?.onSwap?.(reaplicarTema)
})
document.addEventListener('livewire:navigated', reaplicarTema)

window.temaActual = function () {
    try {
        return localStorage.getItem('theme') || 'system'
    } catch (e) {
        return 'system'
    }
}
