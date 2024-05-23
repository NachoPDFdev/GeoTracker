document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('refreshLocationsBtn')) {
        let refreshInterval = 10;
        let countdownInterval;

        function refreshLocations() {
            clearInterval(countdownInterval); // Clear the previous countdown
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById('techniciansTable').innerHTML = xhr.responseText;
                    startCountdown(); // Start a new countdown
                }
            };
            xhr.send('action=tlt_refresh_locations');
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

        function checkTechnicianStatus() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    let technicians = JSON.parse(xhr.responseText);
                    technicians.forEach(function(technician) {
                        let lastUpdated = new Date(technician.last_updated).getTime();
                        let now = new Date().getTime();
                        let diff = now - lastUpdated;
                        let statusCell = document.querySelector(`#technician-${technician.id} .status`);

                        if (diff > refreshInterval * 1000) {
                            statusCell.innerHTML = '<span style="color: red;">✗ Desconectado</span>';
                        } else {
                            statusCell.innerHTML = '<span style="color: green;">✓ Conectado</span>';
                        }
                    });
                }
            };
            xhr.send('action=tlt_check_status');
        }

        document.getElementById('refreshLocationsBtn').addEventListener('click', refreshLocations);
        refreshLocations(); // Initial call to refresh locations
        setInterval(checkTechnicianStatus, 10000); // Check status every 10 seconds
    }

    // Funcionalidad para el botón de localización
    const toggleLocationBtn = document.getElementById('toggleLocationBtn');
    if (toggleLocationBtn) {
        let tracking = false;
        let watchId;

        toggleLocationBtn.addEventListener('click', function() {
            if (tracking) {
                navigator.geolocation.clearWatch(watchId);
                tracking = false;
                toggleLocationBtn.classList.remove('active');
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
                    toggleLocationBtn.classList.add('active');
                    document.getElementById('trackingStatus').textContent = 'Encendido';
                    sendNotification('Geolocalización encendida');
                } else {
                    alert("Geolocation is not supported by this browser.");
                }
            }
        });

        function showPosition(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            document.getElementById('latitude').textContent = latitude;
            document.getElementById('mapsLink').href = "https://www.google.com/maps?q=" + latitude + "," + longitude;

            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + latitude + '&lon=' + longitude)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('city').textContent = data.address.city || data.address.town || data.address.village || 'No disponible';
                    document.getElementById('region').textContent = data.address.state || 'No disponible';
                });

            // Envía la ubicación al servidor
            fetch(geotracker.rest_url + 'tlt/v1/update-location', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': geotracker.nonce
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
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=tlt_send_notification&message=' + encodeURIComponent(message)
            });
        }
    }
});
