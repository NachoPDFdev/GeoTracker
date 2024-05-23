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

        document.getElementById('refreshLocationsBtn').addEventListener('click', refreshLocations);
        refreshLocations(); // Initial call to refresh locations
    }
});
