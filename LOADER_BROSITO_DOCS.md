# 🎨 Loader Animado de Conexión - Documentación

## Descripción
Sistema de loader animado reutilizable que alterna entre dos animaciones cada 3 segundos:
- **Spinner circular** (3 anillos giratorios)
- **Ola ondeante** (9 barras animadas)

Incluye partículas flotantes de fondo y pasos de progreso animados.

## Colores
- **Azul principal**: `#3b82f6` (blue-500)
- **Azul oscuro**: `#1e40af` (blue-800)
- **Azul muy oscuro**: `#1e3a8a` (blue-900)
- **Azul claro**: `#60a5fa` (blue-400)
- **Negro/Gris oscuro**: Fondo con gradiente

## Archivos
- `public/css/admin-connection-loader.css` - Estilos del loader
- `public/js/admin-connection-loader.js` - Lógica del loader

## Uso Básico

### 1. Incluir archivos en tu vista Blade

```blade
<link rel="stylesheet" href="{{ asset('css/admin-connection-loader.css') }}">
<script src="{{ asset('js/admin-connection-loader.js') }}"></script>
```

### 2. Mostrar el loader

```javascript
// Opción 1: Función global simple
showConnectionLoader();

// Opción 2: Instancia global
window.connectionLoader.show();
```

### 3. Ocultar el loader

```javascript
// Opción 1: Función global simple
hideConnectionLoader();

// Opción 2: Instancia global
window.connectionLoader.hide();
```

## Ejemplos de Uso

### Ejemplo 1: Al enviar un formulario

```blade
<form onsubmit="showConnectionLoader(); return true;">
    @csrf
    <button type="submit">Procesar</button>
</form>
```

### Ejemplo 2: Con confirmación

```javascript
function handleSubmit(event) {
    if (!confirm('¿Estás seguro?')) {
        event.preventDefault();
        return false;
    }
    
    showConnectionLoader();
    return true;
}
```

```blade
<form onsubmit="return handleSubmit(event)">
    <!-- contenido -->
</form>
```

### Ejemplo 3: Con AJAX

```javascript
// Mostrar antes de la petición
showConnectionLoader();

fetch('/api/endpoint', {
    method: 'POST',
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(data => {
    // Ocultar cuando termine
    hideConnectionLoader();
    console.log('Éxito:', data);
})
.catch(error => {
    // Ocultar en caso de error
    hideConnectionLoader();
    console.error('Error:', error);
});
```

### Ejemplo 4: Ocultar automáticamente después de redirección

```blade
@if(session('success') || session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            hideConnectionLoader();
        });
    </script>
@endif
```

## Personalización

### Cambiar los pasos de progreso

Edita el array `steps` en `admin-connection-loader.js`:

```javascript
this.steps = [
    'Paso 1...',
    'Paso 2...',
    'Paso 3...',
    // Agrega más pasos
];
```

### Cambiar velocidad de alternancia

Modifica el intervalo en `show()`:

```javascript
// Cambiar de 3000ms (3s) a otro valor
this.animationInterval = setInterval(() => {
    this.toggleAnimation();
}, 3000); // <-- Cambiar aquí
```

### Cambiar velocidad de pasos

Modifica el intervalo de pasos:

```javascript
// Cambiar de 2000ms (2s) a otro valor
this.stepInterval = setInterval(() => {
    this.updateStep();
}, 2000); // <-- Cambiar aquí
```

## Características

✅ **Reutilizable** - Usa en cualquier vista
✅ **Responsive** - Se adapta a cualquier pantalla
✅ **Animaciones suaves** - Transiciones fluidas
✅ **Auto-limpieza** - Se elimina del DOM al ocultarse
✅ **Sin dependencias** - JavaScript vanilla puro
✅ **Pasos dinámicos** - Muestra progreso visual
✅ **Partículas flotantes** - Efecto visual atractivo

## Animaciones Incluidas

### 1. Spinner Circular
- 3 anillos concéntricos
- Rotación en direcciones alternas
- Velocidades diferentes para efecto 3D

### 2. Ola Ondeante
- 9 barras verticales
- Animación de ola sincronizada
- Gradiente de color dinámico

### 3. Partículas de Fondo
- 9 partículas flotantes
- Movimiento ascendente continuo
- Opacidad y timing variados

## Implementación Actual

Actualmente implementado en:
- `resources/views/admin/solicitudes/pendientes-conexion.blade.php`

Se activa cuando el administrador hace clic en "🔌 Conectar" para procesar una solicitud de integración.

## Notas Técnicas

- **Z-index**: 9999 (asegura que esté sobre todo)
- **Overlay**: Fondo oscuro con gradiente
- **Transiciones**: 0.3s para fade in/out
- **Intervalos**: Se limpian automáticamente al ocultar
- **Memoria**: Se elimina del DOM al ocultar para evitar fugas

## Compatibilidad

✅ Chrome/Edge (últimas versiones)
✅ Firefox (últimas versiones)
✅ Safari (últimas versiones)
✅ Navegadores modernos con soporte CSS3

## Troubleshooting

### El loader no aparece
- Verifica que los archivos CSS y JS estén incluidos
- Revisa la consola del navegador por errores
- Asegúrate de llamar `showConnectionLoader()`

### El loader no desaparece
- Llama explícitamente `hideConnectionLoader()`
- Verifica que no haya errores JavaScript
- Revisa que la redirección incluya el script de ocultación

### Animaciones no se ven suaves
- Verifica que el CSS esté cargando correctamente
- Revisa el rendimiento del navegador
- Asegúrate de no tener múltiples instancias activas

## Autor
Creado para el sistema de Integración Shopify + Lioren
