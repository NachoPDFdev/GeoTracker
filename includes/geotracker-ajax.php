<?php

add_action('wp_ajax_tlt_refresh_locations', 'tlt_refresh_locations');
add_action('wp_ajax_nopriv_tlt_update_location', 'tlt_update_location');

// Actualiza la ubicación del técnico
function tlt_update_location(WP_REST_Request $request) {
    $user = wp_get_current_user();

    // Verifica si el usuario tiene el rol 'tecnico'
    if (in_array('tecnico', (array) $user->roles)) {
        $latitude = sanitize_text_field($request->get_param('latitude'));
        $longitude = sanitize_text_field($request->get_param('longitude'));
        $timestamp = current_time('mysql');

        if ($latitude && $longitude) {
            // Actualiza los metadatos del usuario con la latitud, longitud y la última actualización
            update_user_meta($user->ID, 'technician_latitude', $latitude);
            update_user_meta($user->ID, 'technician_longitude', $longitude);
            update_user_meta($user->ID, 'location_last_updated', $timestamp);

            // Registra en debug.log
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

// Refresca las ubicaciones
function tlt_refresh_locations() {
    echo tlt_render_technicians_table();
    wp_die();
}
