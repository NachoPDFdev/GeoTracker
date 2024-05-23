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
