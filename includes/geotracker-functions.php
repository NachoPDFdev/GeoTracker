<?php

// Registra el endpoint de la API REST para actualizar la ubicación
function tlt_register_endpoints() {
    register_rest_route('tlt/v1', '/update-location', array(
        'methods' => 'POST',
        'callback' => 'tlt_update_location',
        'permission_callback' => '__return_true',
    ));
}

// Añade el rol 'tecnico'
function tlt_add_tecnico_role() {
    add_role(
        'tecnico',
        __( 'Tecnico' ),
        array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        )
    );
}

add_action('init', 'tlt_register_endpoints');
add_action('init', 'tlt_add_tecnico_role');
