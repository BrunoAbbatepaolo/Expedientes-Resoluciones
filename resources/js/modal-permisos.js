// Ventana de permisos (Usuarios). El catálogo (módulos, niveles, dependencias y plantillas) viene del servidor
// (App\Support\CatalogoPermisos); el estado vive en $wire.permisos y se guarda recién al apretar Guardar.
// Cada módulo es una fila con un selector de nivel (Sin acceso / Ver / Editar); RRHH suma interruptores sueltos.
// Las dependencias acá son solo comodidad: el servidor las vuelve a aplicar al guardar.

export function modalPermisos(catalogo, puedeAdmin = true) {
    const todasLasClaves = catalogo.modulos.flatMap((m) => m.permisos.map((p) => p.clave))
    const permisosPorClave = Object.fromEntries(catalogo.modulos.flatMap((m) => m.permisos.map((p) => [p.clave, p])))

    return {
        catalogo,
        puedeAdmin,
        show: false,
        resaltadas: [],
        temporizador: null,

        activo(clave) {
            return !!this.$wire.permisos[clave]
        },
        titulo(clave) {
            return permisosPorClave[clave]?.titulo ?? clave
        },
        // El servidor ignora los cambios de admin_ver si quien edita no lo tiene: acá se bloquea para no mentir en el resumen.
        bloqueado(clave) {
            return clave === 'admin_ver' && !this.puedeAdmin
        },

        // ---- Cambios ----
        alternar(clave) {
            if (this.bloqueado(clave)) return
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

        // ---- Módulos y niveles ----
        // 0 = sin acceso; k = tiene los primeros k niveles.
        nivelDe(modulo) {
            let nivel = 0
            modulo.niveles.forEach((n, i) => { if (this.activo(n.clave)) nivel = i + 1 })
            return nivel
        },
        fijarNivel(modulo, nivel) {
            if (modulo.niveles.some((n) => this.bloqueado(n.clave))) return
            const claves = modulo.niveles.map((n) => n.clave)
            if (nivel > 0) this.fijar(claves.slice(0, nivel), true)
            if (claves.slice(nivel).some((c) => this.activo(c))) this.fijar(claves.slice(nivel), false)
        },
        // Una línea con lo que el nivel actual permite hacer.
        detalle(modulo) {
            const nivel = this.nivelDe(modulo)
            return nivel === 0 ? 'Sin acceso.' : permisosPorClave[modulo.niveles[nivel - 1].clave].descripcion
        },
        extrasDe(modulo) {
            return modulo.extras.map((c) => permisosPorClave[c])
        },
        cuantosActivos(modulo) {
            return modulo.permisos.filter((p) => this.activo(p.clave)).length
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
            this.modulosDeRol(rol).forEach((m) => m.permisos.forEach((permiso) => {
                if (!this.bloqueado(permiso.clave)) p[permiso.clave] = rol.permisos.includes(permiso.clave)
            }))
            this.$wire.permisos = p
            this.resaltar(todasLasClaves.filter((c) => antes[c] !== p[c]))
        },
        quitarTodo() {
            this.fijar(todasLasClaves.filter((c) => this.activo(c) && !this.bloqueado(c)), false)
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
