<?php

function geotracker_enqueue_scripts() {
    wp_enqueue_style('geotracker-styles', plugins_url('assets/css/geotracker-styles.css', __FILE__));
    wp_enqueue_script('geotracker-scripts', plugins_url('assets/js/geotracker-scripts.js', __FILE__), array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'geotracker_enqueue_scripts');
add_action('admin_enqueue_scripts', 'geotracker_enqueue_scripts');
