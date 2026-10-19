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

## Decisiones (usuario, 2026-10-19)

1. PDF: vista imprimible (`/resoluciones/{id}/imprimir`, hoja A4 + `window.print()`), sin dompdf.
2. Solo campos editables; el texto legal queda fijo (para cambios de redacción: Personalizado). Se quitan los `contenteditable`.
3. Se elimina del todo el modo `completo` (formulario + vista previa).
4. Después de guardar: abrir el PDF en otra pestaña e ir a la lista de resoluciones.
