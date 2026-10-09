// Ventana de permisos (Usuarios). El catálogo (módulos, dependencias y plantillas) viene del servidor
// (App\Support\CatalogoPermisos); el estado vive en $wire.permisos y se guarda recién al apretar Guardar.
// Las dependencias acá son solo comodidad: el servidor las vuelve a aplicar al guardar.

const sinTildes = (texto) => texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()

export function modalPermisos(catalogo) {
    const todasLasClaves = catalogo.modulos.flatMap((m) => m.permisos.map((p) => p.clave))
    const permisosPorClave = Object.fromEntries(catalogo.modulos.flatMap((m) => m.permisos.map((p) => [p.clave, p])))

    return {
        catalogo,
        show: false,
        busqueda: '',
        soloActivos: false,
        abiertas: [],
        resaltadas: [],
        temporizador: null,

        init() {
            this.abrirSegunPantalla()
            this.$watch('show', (visible) => {
                if (visible) {
                    this.busqueda = ''
                    this.soloActivos = false
                    this.abrirSegunPantalla()
                }
            })
        },

        // En celulares los módulos arrancan cerrados (con su contador); en pantallas grandes, abiertos.
        abrirSegunPantalla() {
            this.abiertas = window.innerWidth < 640 ? [] : catalogo.modulos.map((m) => m.clave)
        },

        activo(clave) {
            return !!this.$wire.permisos[clave]
        },
        titulo(clave) {
            return permisosPorClave[clave]?.titulo ?? clave
        },
        requiere(clave) {
            return catalogo.dependencias[clave] ?? null
        },

        // ---- Cambios ----
        alternar(clave) {
            this.fijar([clave], !this.activo(clave))
        },

        // Fija uno o varios permisos aplicando dependencias: activar uno activa lo que necesita;
        // desactivar uno desactiva lo que depende de él.
        fijar(claves, valor) {
            const p = { ...this.$wire.permisos }
            const tocadas = new Set()

            claves.forEach((clave) => {
                if (p[clave] !== valor) tocadas.add(clave)
                p[clave] = valor

                if (valor) {
                    let base = catalogo.dependencias[clave]
                    while (base && !p[base]) {
                        p[base] = true
                        tocadas.add(base)
                        base = catalogo.dependencias[base]
                    }
                }
            })

            if (!valor) {
                let cambio = true
                while (cambio) {
                    cambio = false
                    Object.entries(catalogo.dependencias).forEach(([dependiente, base]) => {
                        if (p[dependiente] && !p[base]) {
                            p[dependiente] = false
                            tocadas.add(dependiente)
                            cambio = true
                        }
                    })
                }
            }

            this.$wire.permisos = p
            this.resaltar([...tocadas])
        },

        resaltar(claves) {
            this.resaltadas = claves
            clearTimeout(this.temporizador)
            this.temporizador = setTimeout(() => { this.resaltadas = [] }, 1400)
        },

        // ---- Módulos ----
        clavesDe(modulo) {
            return modulo.permisos.map((p) => p.clave)
        },
        cuantosActivos(modulo) {
            return this.clavesDe(modulo).filter((c) => this.activo(c)).length
        },
        moduloCompleto(modulo) {
            return this.cuantosActivos(modulo) === modulo.permisos.length
        },
        alternarModulo(modulo) {
            this.fijar(this.clavesDe(modulo), !this.moduloCompleto(modulo))
        },
        estaAbierto(modulo) {
            return this.busqueda.trim() !== '' || this.abiertas.includes(modulo.clave)
        },
        alternarAbierto(modulo) {
            this.abiertas = this.abiertas.includes(modulo.clave)
                ? this.abiertas.filter((c) => c !== modulo.clave)
                : [...this.abiertas, modulo.clave]
        },

        // ---- Búsqueda y filtro ----
        visible(permiso) {
            if (this.soloActivos && !this.activo(permiso.clave)) return false
            const q = sinTildes(this.busqueda.trim())
            if (!q) return true
            return sinTildes(`${permiso.titulo} ${permiso.descripcion}`).includes(q)
        },
        moduloVisible(modulo) {
            return modulo.permisos.some((p) => this.visible(p))
        },
        get hayResultados() {
            return catalogo.modulos.some((m) => this.moduloVisible(m))
        },

        // ---- Plantillas de rol ----
        modulosDeRol(rol) {
            return catalogo.modulos.filter((m) => m.permisos.some((p) => rol.permisos.includes(p.clave)))
        },
        descripcionRol(rol) {
            return `${rol.descripcion} Reemplaza lo de: ${this.modulosDeRol(rol).map((m) => m.titulo).join(', ')}.`
        },
        // Un rol "está aplicado" si los permisos de los módulos que toca coinciden exactamente con los suyos.
        rolAplicado(rol) {
            return this.modulosDeRol(rol).every((m) => m.permisos.every((p) => this.activo(p.clave) === rol.permisos.includes(p.clave)))
        },
        aplicarRol(rol) {
            const p = { ...this.$wire.permisos }
            const antes = { ...p }
            this.modulosDeRol(rol).forEach((m) => m.permisos.forEach((permiso) => { p[permiso.clave] = rol.permisos.includes(permiso.clave) }))
            this.$wire.permisos = p
            this.resaltar(todasLasClaves.filter((c) => antes[c] !== p[c]))
        },
        quitarTodo() {
            this.fijar(todasLasClaves.filter((c) => this.activo(c)), false)
        },

        // ---- Resumen de cambios ----
        get agregados() {
            const originales = this.$wire.permisosOriginales ?? {}
            return todasLasClaves.filter((c) => this.activo(c) && !originales[c])
        },
        get quitados() {
            const originales = this.$wire.permisosOriginales ?? {}
            return todasLasClaves.filter((c) => !this.activo(c) && originales[c])
        },
        get hayCambios() {
            return this.agregados.length + this.quitados.length > 0
        },
        get totalActivos() {
            return todasLasClaves.filter((c) => this.activo(c)).length
        },
    }
}

function registrar() {
    window.Alpine.data('modalPermisos', modalPermisos)
}

if (window.Alpine) {
    registrar()
} else {
    document.addEventListener('alpine:init', registrar)
}
