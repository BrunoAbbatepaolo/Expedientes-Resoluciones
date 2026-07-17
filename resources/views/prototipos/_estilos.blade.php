{{-- Estilos comunes a los prototipos de resolución (antes duplicados en cada archivo) --}}
.documento-resolucion {
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    line-height: 1.5;
    color: #111827;
    width: 100%;
    margin: 0 auto;
    background: #f3f4f6;
    box-sizing: border-box;
    padding: 24px;
}
.documento-resolucion * {
    box-sizing: border-box;
}
.sheet {
    width: 1200px;
    min-height: auto;
    background: white;
    margin: auto;
    padding: 0;
    position: relative;
    overflow: visible;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* HEADER */
.header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
    padding: 20px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 8px 8px 0 0;
}
.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.logos-container {
    background: white;
    border-radius: 8px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.logo-ipv {
    width: 120px;
    height: auto;
}
.logo-gobierno {
    height: 50px;
    width: auto;
}
.brand {
    display: flex;
    flex-direction: column;
}
.brand-title {
    font-size: 16px;
    font-weight: 700;
    color: white;
    letter-spacing: 0.5px;
    line-height: 1.2;
}
.brand-subtitle {
    font-size: 9px;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    letter-spacing: 1px;
}
.header-right {
    display: flex;
    align-items: center;
}
.header-title {
    font-size: 20px;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* CONTENIDO */
.content {
    padding: 40px 50px;
}

/* DATOS PRINCIPALES */
.datos-principales {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}
.dato-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 14px 16px;
}
.dato-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 4px;
}
.dato-value {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    font-family: 'Times New Roman', Times, serif;
}
.dato-value input.sync-codigo {
    background: transparent;
    border: none;
    outline: none;
    font-size: inherit;
    font-weight: inherit;
    font-family: inherit;
    color: inherit;
    width: 80px;
    padding: 0;
    margin: 0;
}
.dato-value input.sync-codigo::placeholder {
    color: #9ca3af;
}
.referencia input.sync-codigo {
    background: transparent;
    border: none;
    outline: none;
    font-size: 13px;
    font-weight: 600;
    color: #1e40af;
    text-transform: uppercase;
    width: 80px;
    padding: 0;
    margin: 0;
}
.referencia input.sync-codigo::placeholder {
    color: #93c5fd;
}
.cuerpo input.sync-codigo {
    background: transparent;
    border: none;
    outline: none;
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    width: 80px;
    padding: 0;
    margin: 0;
}
.cuerpo input.sync-codigo::placeholder {
    color: #9ca3af;
}

/* CAUSANTE */
.causante {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-left: 4px solid #3b82f6;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 20px;
}
.causante-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 4px;
}
.causante-value {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

/* LUGAR Y FECHA */
.lugar-fecha {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 24px;
}

/* DESTINATARIO */
.destinatario {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 6px;
}

/* REFERENCIA */
.referencia {
    font-size: 13px;
    font-weight: 600;
    color: #1e40af;
    margin-bottom: 20px;
    text-transform: uppercase;
}

/* CUERPO */
.cuerpo {
    font-size: 14px;
    text-align: justify;
    line-height: 1.9;
    color: #374151;
    margin-bottom: 32px;
}
.cuerpo-valores {
    font-weight: 700;
    color: #111827;
}

/* FIRMAS */
.firmas {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    margin-top: 48px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}
.firma {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.firma-linea {
    width: 180px;
    border-bottom: 1px solid #111827;
    margin-bottom: 12px;
}
.firma-nombre {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    text-transform: uppercase;
}
.firma-cargo {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

/* EDITABLE */
.documento-resolucion [contenteditable="true"] {
    outline: 1px dashed #9ca3af;
    padding: 2px 4px;
    border-radius: 3px;
    transition: all 0.2s;
    min-width: 30px;
    display: inline-block;
}
.documento-resolucion [contenteditable="true"]:hover {
    outline: 2px dashed #6b7280;
    background-color: rgba(255, 255, 255, 0.5);
}
.documento-resolucion [contenteditable="true"]:focus {
    outline: 2px solid #3b82f6;
    background-color: rgba(59, 130, 246, 0.08);
}

/* RESPONSIVE */
@media (max-width: 1250px) {
    .sheet {
        width: 100%;
        max-width: 1200px;
    }
    .datos-principales {
        grid-template-columns: 1fr;
    }
    .firmas {
        grid-template-columns: 1fr;
        gap: 30px;
    }
}
