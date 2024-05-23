<?php
/*
Plugin Name: GEOTRACKER ALPHA 
Description: PLUGIN ALPHA PARA GESTION GPS DE USUARIOS
Version: 1.5
Author: Daniel Videla Morales
*/

// Definir constantes
define('GEOTRACKER_VERSION', '1.5');
define('GEOTRACKER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GEOTRACKER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once GEOTRACKER_PLUGIN_DIR . 'includes/functions.php';

// Cargar scripts y estilos
function geotracker_enqueue_assets() {
    wp_enqueue_style('geotracker-styles', GEOTRACKER_PLUGIN_URL . 'assets/css/styles.css');
    wp_enqueue_script('geotracker-scripts', GEOTRACKER_PLUGIN_URL . 'assets/js/scripts.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'geotracker_enqueue_assets');
add_action('admin_enqueue_scripts', 'geotracker_enqueue_assets');

// Hooks de activación y desactivación
register_activation_hook(__FILE__, 'geotracker_activate');
register_deactivation_hook(__FILE__, 'geotracker_deactivate');

// Hook para inicializar el plugin
add_action('init', 'tlt_register_endpoints');
add_action('admin_menu', 'tlt_add_admin_menu');
add_action('init', 'tlt_add_tecnico_role');
add_action('wp_ajax_tlt_refresh_locations', 'tlt_refresh_locations');
add_action('wp_ajax_nopriv_tlt_update_location', 'tlt_update_location');
add_shortcode('tlt_toggle_location', 'tlt_toggle_location_shortcode');
add_shortcode('tlt_technicians_table', 'tlt_technicians_table_shortcode');
add_shortcode('tlt_show_db', 'tlt_show_db_shortcode');
