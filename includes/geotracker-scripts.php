<?php
// Archivo para registrar y encolar scripts y estilos del plugin
function geotracker_enqueue_scripts() {
    // Encolar estilo del plugin
    wp_enqueue_style('geotracker-style', GEOTRACKER_PLUGIN_URL . 'assets/css/geotracker.css', array(), GEOTRACKER_VERSION);
    
    // Encolar script del plugin
    wp_enqueue_script('geotracker-script', GEOTRACKER_PLUGIN_URL . 'assets/js/geotracker.js', array('jquery'), GEOTRACKER_VERSION, true);

    // Localizar script para enviar datos de AJAX
    wp_localize_script('geotracker-script', 'geotracker_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('geotracker_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'geotracker_enqueue_scripts');
