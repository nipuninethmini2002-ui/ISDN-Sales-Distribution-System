<?php
session_start();
include 'db.php';

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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        .container { max-width: 900px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #4CAF50; color: white; }
        button { padding: 6px 12px; cursor: pointer; }
        #mapContainer { margin-top: 20px; display: none; }
        #map { height: 450px; border-radius: 10px; }
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
        $stmt = $conn->prepare("
            SELECT o.order_id, o.order_date, o.status, d.delivery_person_id
            FROM orders o
            LEFT JOIN delivery d ON o.order_id = d.order_id
            WHERE o.customer_id=? 
            ORDER BY o.order_date DESC
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                echo "<tr>";
                echo "<td>{$row['order_id']}</td>";
                echo "<td>{$row['order_date']}</td>";
                echo "<td>{$row['status']}</td>";

                if($row['delivery_person_id']){
                    echo "<td><button onclick='showMap({$row['order_id']})'>Track</button></td>";
                } else {
                    echo "<td><button onclick='alert(\"Delivery person not assigned yet!\")'>Track</button></td>";
                }

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
    timer = setInterval(updateLocation, 10000); 
}

function updateLocation(){
    fetch('get_delivery_location.php?order_id=' + currentOrderId)
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success'){
                let lat = parseFloat(data.current_lat);
                let lng = parseFloat(data.current_lng);
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 15);
            } else {
                console.warn(data.message);
            }
        })
        .catch(err => console.error(err));
}
</script>
</body>
</html>
