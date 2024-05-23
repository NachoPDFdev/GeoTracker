<?php
// Funciones AJAX para el plugin

function tlt_refresh_locations() {
    echo tlt_render_technicians_table();
    wp_die();
}
add_action('wp_ajax_tlt_refresh_locations', 'tlt_refresh_locations');

function tlt_update_location(WP_REST_Request $request) {
    $user = wp_get_current_user();

    if (in_array('tecnico', (array) $user->roles)) {
        $latitude = sanitize_text_field($request->get_param('latitude'));
        $longitude = sanitize_text_field($request->get_param('longitude'));
        $timestamp = current_time('mysql');

        if ($latitude && $longitude) {
            update_user_meta($user->ID, 'technician_latitude', $latitude);
            update_user_meta($user->ID, 'technician_longitude', $longitude);
            update_user_meta($user->ID, 'location_last_updated', $timestamp);

            // Log to debug.log
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Location updated for user {$user->ID} - Latitude: $latitude, Longitude: $longitude");
            }

            return new WP_REST_Response('Location updated successfully', 200);
        } else {
            return new WP_REST_Response('Invalid data', 400);
        }
    } else {
        return new WP_REST_Response('Unauthorized', 401);
    }
}
add_action('rest_api_init', function() {
    register_rest_route('tlt/v1', '/update-location', array(
        'methods' => 'POST',
        'callback' => 'tlt_update_location',
        'permission_callback' => '__return_true',
    ));
});
