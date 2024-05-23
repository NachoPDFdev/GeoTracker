# GeoTracker

**GeoTracker** es un plugin de WordPress diseñado para localizar a tus empleados en tiempo real. Este plugin permite a los técnicos activar o desactivar su ubicación y permite a los administradores ver la ubicación de todos los técnicos en un panel de administración.

## Características

- Activación y desactivación de geolocalización para técnicos.
- Visualización en tiempo real de la ubicación de los técnicos.
- Asignación de roles de técnico a usuarios existentes.
- Visualización de la última ubicación conocida y estado de cada técnico.
- Integración con Google Maps para visualizar ubicaciones.

## Requisitos

- WordPress 5.0 o superior.
- PHP 7.2 o superior.

## Instalación

1. **Descargar**: Descarga el archivo `geotracker.zip`.
2. **Subir**: Ve a tu panel de administración de WordPress, navega a `Plugins` > `Añadir nuevo` y haz clic en `Subir plugin`. Selecciona el archivo `geotracker.zip` y haz clic en `Instalar ahora`.
3. **Activar**: Una vez instalado, haz clic en `Activar`.

## Uso

### Shortcodes

El plugin proporciona varios shortcodes para facilitar su uso:

1. **Activar/Desactivar Geolocalización (solo para técnicos)**:
    ```html
    [tlt_toggle_location]
    ```
    Este shortcode muestra un botón que permite a los técnicos activar o desactivar su geolocalización. Solo visible para usuarios con el rol `técnico`.

2. **Tabla de Técnicos**:
    ```html
    [tlt_technicians_table]
    ```
    Este shortcode muestra una tabla con la información de todos los técnicos, incluyendo su estado, última ubicación conocida y un enlace a Google Maps para ver más detalles.

3. **Mostrar Base de Datos**:
    ```html
    [tlt_show_db]
    ```
    Este shortcode muestra una tabla con los datos de geolocalización almacenados en la base de datos.

### Configuración

1. **Asignar Rol de Técnico**: 
    - Ve a `Geolocalización` en el menú de administración de WordPress.
    - Selecciona un usuario de la lista desplegable y haz clic en `Asignar Rol de Técnico`.

2. **Recargar Ubicaciones**:
    - En la misma página de administración, haz clic en el botón `Recargar Ubicaciones` para actualizar manualmente la lista de técnicos y su ubicación.

### Personalización

Puedes personalizar el aspecto del botón de geolocalización editando el archivo CSS ubicado en `assets/css/geotracker-styles.css`.

### Código

#### Archivo Principal

`geotracker.php`

```php
<?php
/*
Plugin Name: GeoTracker
Description: GeoTracker es un plugin de WordPress diseñado para localizar a tus empleados en tiempo real.
Version: 1.0
Author: Tu Nombre
*/

// Definir constantes
define('GEOTRACKER_VERSION', '1.0');
define('GEOTRACKER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GEOTRACKER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once GEOTRACKER_PLUGIN_DIR . 'includes/class-geotracker.php';
require_once GEOTRACKER_PLUGIN_DIR . 'includes/geotracker-functions.php';
require_once GEOTRACKER_PLUGIN_DIR . 'includes/geotracker-shortcodes.php';
require_once GEOTRACKER_PLUGIN_DIR . 'includes/geotracker-scripts.php';
require_once GEOTRACKER_PLUGIN_DIR . 'includes/geotracker-ajax.php';

// Inicializar el plugin
register_activation_hook(__FILE__, array('GeoTracker', 'activate'));
register_deactivation_hook(__FILE__, array('GeoTracker', 'deactivate'));

add_action('plugins_loaded', array('GeoTracker', 'init'));
