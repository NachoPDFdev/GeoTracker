<?php
function tlt_toggle_location_shortcode() {
    if (!is_user_logged_in() || !current_user_can('tecnico')) {
        return '';
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
        let countdown = 10;
        let countdownInterval = setInterval(updateCountdown, 1000);

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

        function updateCountdown() {
            countdown--;
            if (countdown >= 0) {
                document.getElementById('countdown').innerText = 'La tabla se actualizará en ' + countdown + ' segundos.';
            }
            if (countdown === 0) {
                countdown = 10;
            }
        }

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
        }, 10000); // Refresh every 10 seconds
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('tlt_technicians_table', 'tlt_technicians_table_shortcode');

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
