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
    <p id="countdown">La tabla se actualizará en 10 segundos.</p>
</div>

<script>
    let refreshInterval = 10;
    let countdownInterval;

    function refreshLocations() {
        clearInterval(countdownInterval); // Clear the previous countdown
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById('techniciansTable').innerHTML = xhr.responseText;
                startCountdown(); // Start a new countdown
            }
        };
        xhr.send('action=geotracker_refresh_locations');
    }

    function startCountdown() {
        let remainingTime = refreshInterval;
        document.getElementById('countdown').textContent = `La tabla se actualizará en ${remainingTime} segundos.`;
        countdownInterval = setInterval(function() {
            remainingTime--;
            if (remainingTime <= 0) {
                refreshLocations(); // Refresh the table and reset the countdown
            } else {
                document.getElementById('countdown').textContent = `La tabla se actualizará en ${remainingTime} segundos.`;
            }
        }, 1000);
    }

    document.getElementById('refreshLocationsBtn').addEventListener('click', refreshLocations);
    refreshLocations(); // Initial call to refresh locations
</script>
