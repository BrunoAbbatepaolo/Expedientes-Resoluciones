# Plan: Resoluciones — Plantilla completa inline + Personalizado

Fecha: 2026-10-19 · Rama: a definir (`feature/resoluciones-plantilla-inline`)

## Diagnóstico del bug "Guardar no guarda y borra todo" (reproducido con test)

Modo `plantilla` (`crear-resolucion.blade.php:1463-1500`):

1. `verPlantillaCompleta()` (`CrearResolucion.php:199`) llama `resetFormulario()` y carga `$plantilla` con
   `plantillaCompletaHtml()`, renderizada con `getDatosEjemplo()` (marcadores `[MONTO]`, `[NÚMERO_EXPEDIENTE]`...).
2. Lo que la persona edita nunca llega a Livewire:
   - texto `contenteditable` = solo DOM;
   - en AplicarPagos, `codigo` / `fechaResolucion` / `numeroTramite` son estado Alpine local, no `datos.*`.
3. `guardarPlantilla()` → `guardarPersonalizado()` valida ese HTML viejo con marcadores → siempre falla
   ("Todavía hay datos sin completar: [MONTO]").
4. La respuesta re-renderiza el componente; el morph de Livewire repone el HTML del servidor → se pierden las ediciones.
   Además `generarNumeroTramite()` es aleatorio en cada render.
5. Segundo disparador del borrado: el campo Expediente de la barra (`wire:model.live.debounce`) re-renderiza el documento en cada pausa.
6. Modo `completo`: si guarda bien, `guardar()` llama `resetFormulario()` y no genera PDF.

No es falta de `preventDefault` (el botón es `type="button"`, sin form).

## Diseño propuesto

- Dos modos: `plantilla` (inline) y `personalizado` (Quill). Se elimina el formulario+vista previa (`completo`).
- **Única fuente de verdad: `$datos`.** Inputs dentro del documento con Alpine `$wire.entangle('datos')` (diferido);
  contenedor del documento con `wire:ignore` para que el morph no pise lo tipeado.
- Sincronización: todos los inputs del mismo campo leen/escriben `datos.<campo>` → se actualizan juntos (generaliza los 3 `x-model="codigo"` de AplicarPagos).
- Componente Blade reutilizable `<x-campo-plantilla campo="num_exp" placeholder="..." />`: en edición = input; al guardar/imprimir = texto plano escapado.
- El HTML guardado se renderiza **en el servidor** desde `$datos` (como hoy `guardar()`), nunca serializando el DOM del cliente.
- Validación por tipo: exigir solo los campos que aparecen en la plantilla de ese tipo.
- Autocompletar PDF/Word: mostrar en modo `plantilla`, `mount()`/`updatedDocumentoAnalizar()` pasan a `plantilla`, mapear `codigo` → `datos.codigo_pago`; refrescar el documento tras aplicar.
- Quitar artículos: requiere refresco explícito del documento (por `wire:ignore`).
- Guardar: no resetear estado si falla; si guarda bien → toast + abrir PDF + evitar doble guardado.
- Tests de `CrearResolucionTest` que usan `modo='completo'` se adaptan.

## Orden

1. AplicarPagos de punta a punta (componente, bind, guardado, PDF, autocompletar, tests).
2. Replicar a los otros 7 prototipos.
3. Limpiar código muerto del modo `completo` en `crear-resolucion.blade.php` (~1400 líneas de formularios duplicados).

## Estado (2026-10-19)

- [x] Fase 1: AplicarPagos de punta a punta (commit e514ad2).
- [x] Fase 2: los otros 7 prototipos con los mismos componentes.
- [x] Fase 3: borrado el modo `completo` (~1400 líneas) y CSS muerto de `contenteditable`.

Componentes en `resources/views/components/resolucion/`: `documento` (envoltorio + CSS del editor), `campo`
(input ⇄ texto, `mayusculas`, `formato`, `predeterminado`), `derivado` (número en letras vía
`CrearResolucion::enLetras`), `opcional` (cotitular; `vacio` = texto estándar), `segun` ("los Señores"/"el/la Señor/a"),
`tabla` (filas dinámicas de los Reconocimiento en `datos.tabla_*`).

## Decisiones (usuario, 2026-10-19)

1. PDF: vista imprimible (`/resoluciones/{id}/imprimir`, hoja A4 + `window.print()`), sin dompdf.
2. Solo campos editables; el texto legal queda fijo (para cambios de redacción: Personalizado). Se quitan los `contenteditable`.
3. Se elimina del todo el modo `completo` (formulario + vista previa).
4. Después de guardar: abrir el PDF en otra pestaña e ir a la lista de resoluciones.

## Segunda tanda (2026-10-19)

- [x] Firmantes: tabla `firmantes` + Administración → Firmantes; cada resolución guarda copia de nombre/cargo/dependencia.
      Futuro (pedido del usuario): firma digital por firmante con código de acceso al aplicarla.
- [x] Dictamen de Cancelaciones: en ~120 PDF reales (C:\Users\Bruno\Documents\Resoluciones-Prueba-PDF) ninguno usa 3136/23;
      cada expediente tiene el suyo → campo obligatorio, sin valor fijo.
- [x] Guía en el editor: "Faltan N datos obligatorios / Ir al siguiente" y campos en rojo al fallar el guardado.
- [x] Ficha del expediente: cada resolución abre su PDF (`Resolucion::urlPdf()`).
- [x] Lista de resoluciones rediseñada (búsqueda/filtros reales, dd/mm/aaaa, fila → PDF, móvil en tarjetas).
      fix: "Guardar cambios" del modal de edición no hacía nada.
- [x] Personalizado: el documento de la plantilla con todo el texto editable (contenteditable), marcadores resaltados,
      saneado con Purifier 'resolucion'; se imprime con los estilos del prototipo. Fix de acentos rotos. Quill eliminado.

## Pendiente / para decidir

- Quitar `quill` de package.json (ya no se importa).
- Push de la rama y PR (gh sin sesión).

## Fase 2 (2026-10-20): mejoras sobre la plantilla inline

Hecho:
- Rectificación: las dos tablas de adjudicatarios ("donde dice" / "debe decir") se completan y se pueden quitar (`x-resolucion.tabla ... quitable`).
- Número de resolución opcional (migración `numero_opcional_y_documento_fijo_en_resoluciones`); se asigna desde la lista ("Asignar número"). Las notas (Aplicar pagos, Reconocimientos) se muestran como "Nota".
- Documento fijo: `resoluciones.documento_html` guarda el documento armado al crear y al editar datos (`Resolucion::regenerarDocumento`); cambiar una plantilla no altera las viejas.
- Lista: paginación de a 20, exportar CSV (`resoluciones.exportar`, respeta búsqueda y filtro), historial por resolución (auditoría sin columnas grandes) y "Usar como base" (`?base=ID` en la pantalla de crear).
- Tipos nuevos: Cancelación Ley 8042, Cancelación por Fondo de Reserva, Oficio judicial (toma de razón) y Adjudicación, con autocompletado desde PDF. Manzana/lote aceptan letras ("B", "6A"); solo si son números se guardan en `cod_barrio`/`cod_casa`.
- Sugerencia del causante del expediente (mitiv) como titular. mitiv solo tiene el nombre del causante: no hay DNI, barrio ni manzana/lote para traer.
- Refactor: datos, reglas y nombres de cada tipo pasaron de `CrearResolucion` a `App\Services\Resoluciones\Tipos` (879 → ~650 líneas). Pint aplicado a todo el proyecto.
- Texto de los documentos: se quita el espacio antes de coma/punto que dejaban los datos, y `x-resolucion.opcional ... espacio` conserva el espacio que Blade recorta.

Pendiente / decisiones abiertas:
- Rescisión con defunción: no hay PDF de muestra, no se hizo.
- Firma digital por firmante con código de acceso (la tabla `firmantes` ya está lista).
- Avisar si ya existe una resolución del mismo tipo para el expediente.
