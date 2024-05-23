<?php

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
    <div id="toggleLocationBtn">Prender/Apagar Mi Localización</div>
    <div id="locationResult">
        <p>Estado: <span id="trackingStatus">Apagado</span></p>
        <p>Latitud: <span id="latitude"></span></p>
        <p>Ciudad: <span id="city"></span></p>
        <p>Región: <span id="region"></span></p>
        <p><a id="mapsLink" href="" target="_blank">Ver en Google Maps</a></p>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('tlt_toggle_location', 'tlt_toggle_location_shortcode');

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
