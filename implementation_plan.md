# Modernización UI del SiRex (Inspirado en React Bits)

Este plan detalla los cambios para incorporar efectos visuales premium (Text Reveals, Glow Cards, Breathing Backgrounds y Elastic Buttons) utilizando las tecnologías existentes en el proyecto (HTML, Tailwind CSS v4, y Alpine.js), respetando la directiva de no usar Flux UI.

## Modificaciones Propuestas

---

### `resources/css/app.css`
Añadiremos variables CSS dinámicas y keyframes para las micro-interacciones globales y el fondo animado.

#### [MODIFY] [app.css](file:///c:/Users/Bruno/Documents/Claude/Expedientes-Resoluciones/resources/css/app.css)
- **Breathing Background**: Modificar `.sirex-shell` para usar un pseudo-elemento `::before` animado con `@keyframes` que mueva y escale sutilmente los gradientes del fondo, dándole vida a la pantalla sin afectar el scroll ni la legibilidad.
- **Magnetic Glow Card**: Extender `.sirex-glass-card` con un efecto radial que reacciona a las variables CSS `--mouse-x` y `--mouse-y`. Este efecto ilumina los bordes y el fondo de las tarjetas del dashboard al pasar el mouse.
- **Elastic Buttons**: Modificar estilos de botones/badges con un leve `transform: scale(0.97)` en estado `:active` para que se sientan "táctiles".

### `resources/views/livewire/dashboard-panel.blade.php`
Aplicaremos las animaciones y lógicas de Alpine.js en la pantalla de bienvenida.

#### [MODIFY] [dashboard-panel.blade.php](file:///c:/Users/Bruno/Documents/Claude/Expedientes-Resoluciones/resources/views/livewire/dashboard-panel.blade.php)
- **Text Reveal**: Envolver el título ("¡Hola, Nombre!") y los subtítulos con `x-data="{ show: false }"` e iniciar un fade-in direccional con desenfoque (`blur-sm translate-y-4 -> blur-none translate-y-0`) para replicar el "Blur Text" de React Bits.
- **Mouse Tracking para Glow Effect**: Agregar eventos `@mousemove` de Alpine.js en cada `.sirex-glass-card` para calcular la posición relativa del cursor y pasarlo a las variables `--mouse-x` y `--mouse-y` definidas en CSS.

### `resources/views/components/layouts/app/sidebar.blade.php`
Asegurar que el contenedor principal soporte la nueva capa animada.

#### [MODIFY] [sidebar.blade.php](file:///c:/Users/Bruno/Documents/Claude/Expedientes-Resoluciones/resources/views/components/layouts/app/sidebar.blade.php)
- Quitar el `sirex-shell` de clases en el `<body>` y ponerlo en un `<div class="fixed inset-0 z-[-1] sirex-shell"></div>` para aislar el fondo dinámico del flujo del documento y evitar recalcular layout general al animar.

### Limpieza de Código Muerto
#### [DELETE] `resources/views/flux/icon/`
- Como lo descubrimos en el análisis anterior, esta carpeta contiene archivos huérfanos que ya no se usan tras la eliminación de Flux UI. Eliminaremos esta carpeta entera.

## Plan de Verificación

### Pruebas Manuales
1. Al cargar el Dashboard, los textos de bienvenida deben tener una transición suave entrando de abajo hacia arriba perdiendo un desenfoque.
2. Al pasar el mouse por las tarjetas del Dashboard, un brillo dorado (modo claro) o blanco (modo oscuro) debe seguir de cerca la punta del cursor iluminando sutilmente la tarjeta y sus bordes.
3. El fondo general debe "respirar" (hacer zoom sutil lentamente).
4. Verificar que no se rompe la vista en móviles al hacer los cambios estructurales del fondo.
