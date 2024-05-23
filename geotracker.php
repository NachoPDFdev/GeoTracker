<?php
/*
Plugin Name: GEOTRACKER ALPHA V1.5
Description: PLUGIN ALPHA PARA GESTION GPS DE USUARIOS
Version: 1.5
Author: Daniel Videla Morales
*/

// Definir constantes
define('GEOTRACKER_VERSION', '1.5');
define('GEOTRACKER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GEOTRACKER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once GEOTRACKER_PLUGIN_DIR . 'includes/class-geotracker.php';
require_once GEOTRACKER_PLUGIN_DIR . 'includes/geotracker-functions.php';
require_once GEOTRACKER_PLUGIN_DIR . 'includes/geotracker-shortcodes.php';
require_once GEOTRACKER_PLUGIN_DIR . 'includes/geotracker-ajax.php';

// Inicializar el plugin
register_activation_hook(__FILE__, array('GeoTracker', 'activate'));
register_deactivation_hook(__FILE__, array('GeoTracker', 'deactivate'));

add_action('plugins_loaded', array('GeoTracker', 'init'));

// Incluir scripts y estilos
add_action('wp_enqueue_scripts', 'geotracker_enqueue_scripts');
function geotracker_enqueue_scripts() {
    wp_enqueue_style('geotracker-styles', GEOTRACKER_PLUGIN_URL . 'assets/css/geotracker-styles.css', array(), GEOTRACKER_VERSION);
    wp_enqueue_script('geotracker-scripts', GEOTRACKER_PLUGIN_URL . 'assets/js/geotracker-scripts.js', array('jquery'), GEOTRACKER_VERSION, true);

    wp_localize_script('geotracker-scripts', 'geotracker', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'rest_url' => rest_url('geotracker/v1/update-location'),
        'nonce' => wp_create_nonce('wp_rest')
    ));
}
