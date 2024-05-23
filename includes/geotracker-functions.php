<?php
function geotracker_update_location(WP_REST_Request $request) {
    $user = wp_get_current_user();

    if (in_array('tecnico', (array) $user->roles)) {
        $latitude = sanitize_text_field($request->get_param('latitude'));
        $longitude = sanitize_text_field($request->get_param('longitude'));
        $timestamp = current_time('mysql');

        if ($latitude && $longitude) {
            update_user_meta($user->ID, 'technician_latitude', $latitude);
            update_user_meta($user->ID, 'technician_longitude', $longitude);
            update_user_meta($user->ID, 'location_last_updated', $timestamp);

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
