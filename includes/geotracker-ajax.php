<?php
function geotracker_send_notification() {
    if (isset($_POST['message'])) {
        error_log('Notification: ' . sanitize_text_field($_POST['message']));
    }
    wp_die();
}
add_action('wp_ajax_geotracker_send_notification', 'geotracker_send_notification');

function geotracker_refresh_locations() {
    echo tlt_render_technicians_table();
    wp_die();
}
add_action('wp_ajax_geotracker_refresh_locations', 'geotracker_refresh_locations');
