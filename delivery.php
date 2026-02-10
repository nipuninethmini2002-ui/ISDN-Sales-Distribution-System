<?php
session_start();
include 'db.php';

// --- 1. Check if customer is logged in ---
if(!isset($_SESSION['customer_id'])){
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Track Orders</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        body { font-family: Arial; background: #f4f4f4; }
        .container { max-width: 900px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #4CAF50; color: white; }
        button { padding: 6px 12px; cursor: pointer; }
        #mapContainer { margin-top: 20px; display: none; }
        #map { height: 450px; }
    </style>
</head>

<body>
<div class="container">
    <h2>Track Orders</h2>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Status</th>
            <th>Track</th>
        </tr>

        <?php
        // --- 2. Fetch only logged-in customer's orders ---
        $stmt = $conn->prepare(
            "SELECT order_id, order_date, status
             FROM orders
             WHERE customer_id = ?
             ORDER BY order_date DESC"
        );
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                echo "<tr>";
                echo "<td>{$row['order_id']}</td>";
                echo "<td>{$row['order_date']}</td>";
                echo "<td>{$row['status']}</td>";
                // Track button
                echo "<td><button onclick='showMap({$row['order_id']})'>Track</button></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No orders found</td></tr>";
        }

        $stmt->close();
        ?>
    </table>

    <div id="mapContainer">
        <h3>Delivery Location</h3>
        <div id="map"></div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map, marker, currentOrderId, timer;

function showMap(orderId){
    currentOrderId = orderId;
    document.getElementById('mapContainer').style.display = 'block';

    if(!map){
        map = L.map('map').setView([6.9271, 79.8612], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
        marker = L.marker([6.9271, 79.8612]).addTo(map);
    }

    updateLocation();
    clearInterval(timer);
    timer = setInterval(updateLocation, 10000); // update every 10 sec
}

function updateLocation(){
    fetch('get_delivery_location.php?order_id=' + currentOrderId)
        .then(res => res.json())
        .then(data => {
            if(data.latitude && data.longitude){
                let lat = parseFloat(data.latitude);
                let lng = parseFloat(data.longitude);
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 15);
            }
        })
        .catch(err => console.error(err));
}
</script>
</body>
</html>
