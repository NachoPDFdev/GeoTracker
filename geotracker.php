<?php
/*
Plugin Name: GEOTRACKER ALPHA 0.6
Description: PLUGIN ALPHA PARA GESTION GPS DE USUARIOS
Version: 0.6
Author: Daniel Videla Morales
*/

// Hook para inicializar el plugin
add_action('init', 'tlt_register_endpoints');
add_action('admin_menu', 'tlt_add_admin_menu');
add_action('init', 'tlt_add_tecnico_role');
add_action('wp_ajax_tlt_refresh_locations', 'tlt_refresh_locations');
add_action('wp_ajax_nopriv_tlt_update_location', 'tlt_update_location');
add_shortcode('tlt_toggle_location', 'tlt_toggle_location_shortcode');
add_shortcode('tlt_technicians_table', 'tlt_technicians_table_shortcode');
add_shortcode('tlt_show_db', 'tlt_show_db_shortcode');

// Registra el endpoint de la API REST para actualizar la ubicación
function tlt_register_endpoints() {
    register_rest_route('tlt/v1', '/update-location', array(
        'methods' => 'POST',
        'callback' => 'tlt_update_location',
        'permission_callback' => '__return_true',
    ));
}

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

// Añade un menú de administración
function tlt_add_admin_menu() {
    add_menu_page('Geolocalización', 'Geolocalización', 'manage_options', 'technician_location_tracker', 'tlt_admin_page', 'dashicons-location-alt');
}

// Página de administración
function tlt_admin_page() {
    // Asigna el rol de técnico a un usuario seleccionado
    if (isset($_POST['tlt_assign_tecnico'])) {
        $user_id = intval($_POST['user_id']);
        $user = get_user_by('id', $user_id);
        if ($user) {
            $user->add_role('tecnico');
            echo '<div class="notice notice-success is-dismissible"><p>El usuario ha sido asignado al rol de técnico.</p></div>';
        }
    }

    // Crea un nuevo usuario con el rol de técnico
    if (isset($_POST['tlt_create_tecnico'])) {
        $username = sanitize_user($_POST['tlt_username']);
        $email = sanitize_email($_POST['tlt_email']);
        $password = sanitize_text_field($_POST['tlt_password']);

        if (!username_exists($username) && !email_exists($email)) {
            $user_id = wp_create_user($username, $password, $email);
            $user = get_user_by('id', $user_id);
            if ($user) {
                $user->add_role('tecnico');
                echo '<div class="notice notice-success is-dismissible"><p>El técnico ha sido creado y asignado al rol de técnico.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>El nombre de usuario o correo electrónico ya existe.</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h1>Geolocalización de Técnicos</h1>
        <form method="post">
            <h2>Asignar Rol de Técnico</h2>
            <select name="user_id">
                <?php
                $users = get_users(array('role__not_in' => array('tecnico')));
                foreach ($users as $user) {
                    echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</option>';
                }
                ?>
            </select>
            <input type="submit" name="tlt_assign_tecnico" value="Asignar Rol de Técnico" class="button button-primary">
        </form>

        <form method="post">
            <h2>Crear Técnico</h2>
            <table class="form-table">
                <tr>
                    <th><label for="tlt_username">Nombre de Usuario</label></th>
                    <td><input name="tlt_username" type="text" id="tlt_username" value="" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="tlt_email">Correo Electrónico</label></th>
                    <td><input name="tlt_email" type="email" id="tlt_email" value="" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="tlt_password">Contraseña</label></th>
                    <td><input name="tlt_password" type="password" id="tlt_password" value="" class="regular-text" required></td>
                </tr>
            </table>
            <input type="submit" name="tlt_create_tecnico" value="Crear Técnico" class="button button-primary">
        </form>

        <h2>Técnicos</h2>
        <div id="techniciansTable"><?php echo tlt_render_technicians_table(); ?></div>
        <button id="refreshLocationsBtn" class="button button-secondary">Recargar Ubicaciones</button>
        <p>La tabla se actualizará cada 10 segundos.</p>
    </div>

    <script>
    function refreshLocations() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById('techniciansTable').innerHTML = xhr.responseText;

                // Mostrar el mensaje de actualización
                const updateMessage = document.getElementById('updateMessage');
                updateMessage.style.display = 'block';

                // Ocultar el mensaje después de 2 segundos
                setTimeout(() => {
                    updateMessage.style.display = 'none';
                }, 2000);
            }
        };
        xhr.send('action=tlt_refresh_locations');
    }

    document.getElementById('refreshLocationsBtn').addEventListener('click', refreshLocations);
    setInterval(refreshLocations, 10000); // Refresh every 10 seconds
</script>
<p id="updateMessage" style="display:none;">La tabla se actualizará cada 10 segundos.</p>
    <?php
}

// Renderiza la tabla de técnicos
function tlt_render_technicians_table() {
    ob_start();
    ?>
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th class="manage-column column-columnname" scope="col">Nombre</th>
                <th class="manage-column column-columnname" scope="col">Latitud</th>
                <th class="manage-column column-columnname" scope="col">Longitud</th>
                <th class="manage-column column-columnname" scope="col">Última Actualización</th>
                <th class="manage-column column-columnname" scope="col">Estado</th>
                <th class="manage-column column-columnname" scope="col">Ver Más</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Obtiene los usuarios con el rol 'tecnico'
            $tecnicos = get_users(array('role' => 'tecnico'));
            foreach ($tecnicos as $user) {
                $latitude = get_user_meta($user->ID, 'technician_latitude', true);
                $longitude = get_user_meta($user->ID, 'technician_longitude', true);
                $last_updated = get_user_meta($user->ID, 'location_last_updated', true);
                $is_active = ($latitude && $longitude) ? '<span style="color: green;">✓ Activo</span>' : '<span style="color: red;">✗ Inactivo</span>';
                $maps_link = ($latitude && $longitude) ? '<a href="https://www.google.com/maps?q=' . $latitude . ',' . $longitude . '" target="_blank">Ver Más</a>' : 'N/A';
                ?>
                <tr>
                    <td><?php echo esc_html($user->display_name); ?></td>
                    <td><?php echo esc_html($latitude ?: 'Ubicación deshabilitada'); ?></td>
                    <td><?php echo esc_html($longitude ?: 'Ubicación deshabilitada'); ?></td>
                    <td><?php echo esc_html($last_updated ?: 'N/A'); ?></td>
                    <td><?php echo $is_active; ?></td>
                    <td><?php echo $maps_link; ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
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

// Refresca las ubicaciones
function tlt_refresh_locations() {
    echo tlt_render_technicians_table();
    wp_die();
}

// Shortcode para el botón de activar/desactivar la ubicación
function tlt_toggle_location_shortcode() {
    if (!is_user_logged_in()) {
        return 'Debe estar registrado para ver esta opción.';
    }

    $user = wp_get_current_user();

    if (!in_array('tecnico', (array) $user->roles)) {
        return 'No tiene permisos para ver esta opción.';
    }

    ob_start();
    ?>
    <button id="toggleLocationBtn" class="button button-secondary">Prender/Apagar Mi Localización</button>
    <div id="locationResult">
        <p>Estado: <span id="trackingStatus">Apagado</span></p>
        <p>Latitud: <span id="latitude"></span></p>
        <p>Ciudad: <span id="city"></span></p>
        <p>Región: <span id="region"></span></p>
        <p><a id="mapsLink" href="" target="_blank">Ver en Google Maps</a></p>
    </div>

    <script>
        let tracking = false;
        let watchId;

        document.getElementById('toggleLocationBtn').addEventListener('click', function() {
            if (tracking) {
                navigator.geolocation.clearWatch(watchId);
                tracking = false;
                document.getElementById('trackingStatus').textContent = 'Apagado';
                document.getElementById('latitude').textContent = '';
                document.getElementById('city').textContent = '';
                document.getElementById('region').textContent = '';
                document.getElementById('mapsLink').href = '';
                sendNotification('Geolocalización apagada');
            } else {
                if (navigator.geolocation) {
                    watchId = navigator.geolocation.watchPosition(showPosition, showError, { enableHighAccuracy: true });
                    tracking = true;
                    document.getElementById('trackingStatus').textContent = 'Encendido';
                    sendNotification('Geolocalización encendida');
                } else {
                    alert("Geolocation is not supported by this browser.");
                }
            }
        });

        function showPosition(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;

            document.getElementById('latitude').textContent = latitude;
            document.getElementById('mapsLink').href = "https://www.google.com/maps?q=" + latitude + "," + longitude;

            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + latitude + '&lon=' + longitude)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('city').textContent = data.address.city || data.address.town || data.address.village || 'No disponible';
                    document.getElementById('region').textContent = data.address.state || 'No disponible';
                });

            // Envía la ubicación al servidor
            fetch('<?php echo rest_url('tlt/v1/update-location'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({
                    latitude: latitude,
                    longitude: longitude
                })
            })
            .then(response => response.json())
            .then(result => {
                console.log(result);
            });
        }

        function showError(error) {
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    alert("User denied the request for Geolocation.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("Location information is unavailable.");
                    break;
                case error.TIMEOUT:
                    alert("The request to get user location timed out.");
                    break;
                case error.UNKNOWN_ERROR:
                    alert("An unknown error occurred.");
                    break;
            }
        }

        function sendNotification(message) {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=tlt_send_notification&message=' + encodeURIComponent(message)
            });
        }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('tlt_toggle_location', 'tlt_toggle_location_shortcode');

// Función de notificación
function tlt_send_notification() {
    if (isset($_POST['message'])) {
        // Puedes manejar la lógica de la notificación aquí
        error_log('Notification: ' . sanitize_text_field($_POST['message']));
    }
    wp_die();
}
add_action('wp_ajax_tlt_send_notification', 'tlt_send_notification');

// Shortcode para mostrar la tabla de técnicos y actualizarla automáticamente
function tlt_technicians_table_shortcode() {
    ob_start();
    ?>
    <div id="liveTechniciansTable"><?php echo tlt_render_technicians_table(); ?></div>
    <script>
        setInterval(function() {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=tlt_refresh_locations')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('liveTechniciansTable').innerHTML = html;
                });
        }, 10000); // Refresca cada 10 segundos
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('tlt_technicians_table', 'tlt_technicians_table_shortcode');

// Shortcode para mostrar la base de datos
function tlt_show_db_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'usermeta';
    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE meta_key IN ('technician_latitude', 'technician_longitude', 'location_last_updated')", ARRAY_A);
    
    ob_start();
    ?>
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th class="manage-column column-columnname" scope="col">User ID</th>
                <th class="manage-column column-columnname" scope="col">Meta Key</th>
                <th class="manage-column column-columnname" scope="col">Meta Value</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($results as $row) {
                ?>
                <tr>
                    <td><?php echo esc_html($row['user_id']); ?></td>
                    <td><?php echo esc_html($row['meta_key']); ?></td>
                    <td><?php echo esc_html($row['meta_value']); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}
add_shortcode('tlt_show_db', 'tlt_show_db_shortcode');
?>
