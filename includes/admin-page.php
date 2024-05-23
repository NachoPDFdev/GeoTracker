<?php
// Asegúrate de que los archivos necesarios están incluidos
require_once GEOTRACKER_PLUGIN_DIR . 'includes/geotracker-functions.php';

function geotracker_admin_page() {
    if (isset($_POST['tlt_assign_tecnico'])) {
        $user_id = intval($_POST['user_id']);
        $user = get_user_by('id', $user_id);
        if ($user) {
            $user->add_role('tecnico');
            echo '<div class="notice notice-success is-dismissible"><p>El usuario ha sido asignado al rol de técnico.</p></div>';
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
        <h2>Técnicos</h2>
        <div id="techniciansTable"><?php echo tlt_render_technicians_table(); ?></div>
        <button id="refreshLocationsBtn" class="button button-secondary">Recargar Ubicaciones</button>
    </div>

    <script>
        function refreshLocations() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById('techniciansTable').innerHTML = xhr.responseText;
                }
            };
            xhr.send('action=tlt_refresh_locations');
        }

        document.getElementById('refreshLocationsBtn').addEventListener('click', refreshLocations);
        setInterval(refreshLocations, 10000); // Refresh every 10 seconds
    </script>
    <?php
}
