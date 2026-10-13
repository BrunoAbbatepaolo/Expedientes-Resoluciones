# Plan: adjuntar PDF y autocompletar resoluciones desde un documento

Pedido (2026-10-13): poder agregar un PDF al crear una resolución, y que al subir un documento se detecte el tipo y se carguen los datos. Decisión: **sugerir y que el usuario confirme** (precarga el formulario; nunca guarda solo).

## Alcance

1. **Adjuntar PDF en el modelo completo.** Antes solo existía en el modo «Plantilla completa» de 3 tipos. Se extrae a un parcial reutilizable (`resources/views/livewire/partials/adjuntos-resolucion.blade.php`).
2. **Leer el documento**: `App\Services\Resoluciones\LectorDocumento`.
   - PDF con texto (digital): librería PHP pura `smalot/pdfparser` (no requiere instalar nada en el servidor).
   - Word `.docx`: se lee con `ZipArchive` (extensión `php-zip`). Si el servidor no la tiene se avisa con un mensaje claro.
   - PDF escaneado (sin texto): no se puede leer sin OCR (Tesseract); se avisa y se carga a mano. Queda como mejora futura.
3. **Detectar tipo y extraer datos**: `App\Services\Resoluciones\AnalizadorResolucion`. Reglas determinísticas (frases clave por tipo + expresiones regulares), basadas en la redacción de `resources/views/prototipos/*`. Solo devuelve campos que existen en `CrearResolucion::$datos`.
4. **Pantalla** (`CrearResolucion`): tarjeta «Autocompletar desde un PDF o Word» en el selector de modo y en el modelo completo.
   - Si el tipo detectado es el de la pantalla: precarga los campos y adjunta el archivo; avisa cuántos completó.
   - Si es otro tipo: muestra «Parece una resolución de tipo X» con botón para pasar a ese formulario (los datos viajan por sesión; el archivo se vuelve a adjuntar a mano).
   - Si no lo reconoce o no tiene texto: avisa y se completa a mano.

## Fuera de alcance / pendiente

- OCR para escaneados.
- Extraer tablas (cuotas) de Reconocimiento de cuota; solo se cargan los datos de cabecera.
- Los datos extraídos son una **sugerencia**: la persona los revisa antes de guardar.

## Pruebas

`tests/Feature/AnalizadorResolucionTest.php` (detección y extracción sobre texto de cada plantilla) y casos en `CrearResolucionTest.php` (subida, sugerencia de otro tipo, archivo ilegible).
