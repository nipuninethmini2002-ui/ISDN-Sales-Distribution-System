<!DOCTYPE html>
<html>
<head>
    <title>Delivery Dashboard</title>
</head>
<body>

<h2>Delivery Dashboard</h2>
<p>Your location will update automatically.</p>

<script>
// -----------------------------
// Step 2: Define your order ID
// -----------------------------
const orderId = 123; // Update this dynamically if needed

// -----------------------------
// Step 3: Function to send location to server
// -----------------------------
function updateLocation(lat, lng) {
    fetch('update_location.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${orderId}&current_lat=${lat}&current_lng=${lng}`
    })
    .then(response => response.text())
    .then(data => console.log(data))
    .catch(err => console.error(err));
}

// -----------------------------
// Step 4: Use browser geolocation
// -----------------------------
function sendCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                updateLocation(lat, lng);
            },
            error => console.error("GPS error: ", error)
        );
    } else {
        console.error("Geolocation not supported.");
    }
}

// -----------------------------
// Step 5: Auto update every 10 seconds
// -----------------------------
setInterval(sendCurrentLocation, 10000); // 10000 ms = 10 seconds

</script>

</body>
</html>
