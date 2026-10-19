{{--
    Envoltorio de un prototipo de resolución. Pasa 'editable' y 'datos' a los <x-resolucion.campo> de adentro (@aware).
    Editable: los campos son inputs ligados a $wire.datos; si no, texto plano (lo que se guarda e imprime).
--}}
@props(['editable' => false, 'datos' => []])
@if ($editable)
    <style>
        .campo-plantilla {
            font: inherit; color: inherit; text-transform: inherit; letter-spacing: inherit;
            background: rgba(0, 81, 158, .07); border: 0; border-bottom: 1.5px dashed rgba(0, 81, 158, .55);
            border-radius: 3px; padding: 0 3px; margin: 0 1px; min-width: 3ch; max-width: 100%; outline: none;
        }
        .campo-plantilla:hover { background: rgba(0, 81, 158, .12); }
        .campo-plantilla:focus { background: #fff8e1; border-bottom-color: #ffb81a; box-shadow: 0 0 0 2px rgba(255, 184, 26, .35); }
        .campo-plantilla::placeholder { color: #b45309; opacity: .85; }
        .campo-plantilla[type="date"] { width: 11.5em; }
        .campo-mayusculas { text-transform: uppercase; }
        .campo-mayusculas::placeholder { text-transform: none; }
        .campo-derivado { color: #475569; border-bottom: 1px dotted #94a3b8; cursor: help; }
        .campo-opcional-vacio, .campo-opcional-vacio .campo-plantilla { opacity: .55; }
        .campo-firmante { cursor: pointer; font-weight: inherit; text-align: center; max-width: 100%; }
        .campo-plantilla.campo-con-error { background: #fee2e2; border-bottom-color: #dc2626; box-shadow: 0 0 0 2px rgba(220, 38, 38, .35); }
        .campo-plantilla.campo-con-error::placeholder { color: #b91c1c; }
        /* El ancho sigue al contenido: nativo donde existe; si no, el estimado en ch del componente. */
        @supports (field-sizing: content) {
            .campo-plantilla:not([type="date"]) { field-sizing: content; width: auto !important; min-width: 4ch; }
        }
        /* En pantalla la hoja se adapta al panel (los prototipos la fijan en 1200px). */
        .documento-resolucion .sheet { width: 100%; max-width: 1200px; }
        @media (max-width: 640px) {
            .documento-resolucion { padding: 0 !important; }
            .documento-resolucion .header { flex-wrap: wrap; gap: 12px; padding: 16px; }
            .documento-resolucion .content { padding: 20px 16px; }
            .documento-resolucion .dato-value { font-size: 17px; }
            .campo-plantilla { min-width: 0; }
        }
    </style>
@endif
{{ $slot }}
