// Editor del modo "Personalizado" de CrearResolucion (x-data="quillInit()").
// Alpine llama init()/destroy() solo: no hace falta x-init ni contadores globales,
// y el editor se vuelve a crear bien después de wire:navigate o de cambiar de modo.
window.quillInit = function () {
    let quill = null

    return {
        async init() {
            // Carga diferida: Quill solo se descarga en la pantalla que lo usa.
            const { default: Quill } = await import('quill')
            const editor = this.$el.querySelector('[data-quill-editor]') ?? this.$el

            quill = new Quill(editor, {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ align: ['justify', 'center', 'right', ''] }],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            [{ header: [2, 3, false] }],
                            [{ indent: '-1' }, { indent: '+1' }],
                            ['blockquote'],
                            ['clean'],
                        ],
                        handlers: {
                            align(value) {
                                quill.format('align', value === '' ? false : value)
                            },
                        },
                    },
                },
            })

            const inicial = this.$wire.get('plantilla')
            if (inicial) {
                quill.clipboard.dangerouslyPasteHTML(aHtml(inicial), 'silent')
            }

            // Sin request por cada tecla: el valor viaja con la próxima acción
            // (ej. "Guardar resolución").
            quill.on('text-change', () => {
                this.$wire.set('plantilla', quill.root.innerHTML, false)
            })
        },

        destroy() {
            quill = null
        },
    }
}

// La plantilla base llega como texto plano con saltos de línea
// (CrearResolucion::convertirHTMLaTexto); se pasa a párrafos.
function aHtml(contenido) {
    if (/<[a-z][\s\S]*>/i.test(contenido)) {
        return contenido
    }

    const div = document.createElement('div')

    return contenido
        .split(/\n+/)
        .map((linea) => {
            div.textContent = linea
            return `<p>${div.innerHTML}</p>`
        })
        .join('')
}
