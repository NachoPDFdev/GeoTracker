<?php
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
            $tecnicos = get_users(array('role' => 'tecnico'));
            foreach ($tecnicos as $user) {
                $latitude = get_user_meta($user->ID, 'technician_latitude', true);
                $longitude = get_user_meta($user->ID, 'technician_longitude', true);
                $last_updated = get_user_meta($user->ID, 'location_last_updated', true);
                $is_active = ($latitude && $longitude) ? '<span style="color: green;">✓ Activo</span>' : '<span style="color: red;">✗ Inactivo</span>';
                $more_link = ($latitude && $longitude) ? '<a href="https://www.google.com/maps?q=' . $latitude . ',' . $longitude . '" target="_blank">Ver Más</a>' : 'N/A';
                ?>
                <tr>
                    <td><?php echo esc_html($user->display_name); ?></td>
                    <td><?php echo esc_html($latitude ?: 'Ubicación deshabilitada'); ?></td>
                    <td><?php echo esc_html($longitude ?: 'Ubicación deshabilitada'); ?></td>
                    <td><?php echo esc_html($last_updated ?: 'N/A'); ?></td>
                    <td><?php echo $is_active; ?></td>
                    <td><?php echo $more_link; ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}
