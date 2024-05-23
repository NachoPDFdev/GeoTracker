<?php
function geotracker_toggle_location_shortcode() {
    if (!is_user_logged_in() || !current_user_can('tecnico')) {
        return '';
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

    <script>
        let tracking = false;
        let watchId;

        document.getElementById('toggleLocationBtn').addEventListener('click', function() {
            if (tracking) {
                navigator.geolocation.clearWatch(watchId);
                tracking = false;
                document.getElementById('toggleLocationBtn').classList.remove('active');
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
                    document.getElementById('toggleLocationBtn').classList.add('active');
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
            fetch('<?php echo rest_url('geotracker/v1/update-location'); ?>', {
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
                body: 'action=geotracker_send_notification&message=' + encodeURIComponent(message)
            });
        }

        setInterval(refreshLocations, 10000); // Refresh every 10 seconds
        let countdown = 10;
        setInterval(function() {
            countdown--;
            if (countdown <= 0) {
                countdown = 10;
            }
            document.getElementById('countdown').textContent = 'La tabla se actualizará en ' + countdown + ' segundos.';
        }, 1000);
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('tlt_toggle_location', 'geotracker_toggle_location_shortcode');
