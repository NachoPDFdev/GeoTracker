jQuery(document).ready(function($) {
    let tracking = false;
    let watchId;
    
    $('#toggleLocationBtn').on('click', function() {
        if (tracking) {
            navigator.geolocation.clearWatch(watchId);
            tracking = false;
            $('#toggleLocationBtn').removeClass('active');
            $('#trackingStatus').text('Apagado');
            $('#latitude').text('');
            $('#city').text('');
            $('#region').text('');
            $('#mapsLink').attr('href', '');
            sendNotification('Geolocalización apagada');
        } else {
            if (navigator.geolocation) {
                watchId = navigator.geolocation.watchPosition(showPosition, showError, { enableHighAccuracy: true });
                tracking = true;
                $('#toggleLocationBtn').addClass('active');
                $('#trackingStatus').text('Encendido');
                sendNotification('Geolocalización encendida');
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }
    });

    function showPosition(position) {
        var latitude = position.coords.latitude;
        var longitude = position.coords.longitude;

        $('#latitude').text(latitude);
        $('#mapsLink').attr('href', "https://www.google.com/maps?q=" + latitude + "," + longitude);

        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + latitude + '&lon=' + longitude')
            .then(response => response.json())
            .then(data => {
                $('#city').text(data.address.city || data.address.town || data.address.village || 'No disponible');
                $('#region').text(data.address.state || 'No disponible');
            });

        fetch(geotracker.rest_url, {
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
        $.post({
            url: geotracker.ajax_url,
            data: {
                action: 'geotracker_send_notification',
                message: message
            }
        });
    }

    setInterval(refreshLocations, 10000); // Refresh every 10 seconds
    let countdown = 10;
    setInterval(function() {
        countdown--;
        if (countdown <= 0) {
            countdown = 10;
        }
        $('#countdown').text('La tabla se actualizará en ' + countdown + ' segundos.');
    }, 1000);
});
