<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role'])){
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
$role = trim($_SESSION['role']);

switch(strtolower($role)){
    case 'customer': $role = 'Customer'; break;
    case 'delivery person':
    case 'delivery_person': $role = 'Delivery_Person'; break;
    case 'rdc staff':
    case 'rdc_staff': $role = 'RDC_Staff'; break;
    case 'ho admin':
    case 'ho_admin': $role = 'HO_Admin'; break;
    default: $role = 'Customer';
}

$delivery_person_id = $_SESSION['delivery_person_id'] ?? null;
$orders = [];

if($role === 'Delivery_Person' && $delivery_person_id){
    $stmt = $conn->prepare("
        SELECT order_id, status
        FROM delivery
        WHERE delivery_person_id = ?
        AND status IN ('pending','on the way')
        ORDER BY order_id DESC
    ");
    $stmt->bind_param("i", $delivery_person_id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$order_ids = array_column($orders, 'order_id');
?>

<!DOCTYPE html>
<html>
<head>
    <title>ISDN Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right, <img src="https://x5siwvse0svtj0yw5pfe.ultatel.com/wp-content/uploads/2022/05/What-is-ISDN-featured-image.jpg">);
            min-height: 100vh;
        }
        .header {
            background: #00695c;
            color: #fff;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .header img.logo {
            width: 70px;
            border-radius: 12px;
            vertical-align: middle;
            margin-right: 15px;
        }
        .header h1 {
            display: inline-block;
            font-size: 30px;
            vertical-align: middle;
        }
        .nav-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px 10px;
            background: #004d40;
        }
        .nav-buttons button {
            background: #26a69a;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 16px; 
            margin: 10px;
            font-size: 15px; 
            cursor: pointer;
            transition: 0.3s;
            min-width: 140px; 
        }
        .nav-buttons button:hover {
            background: #00796b;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
        .nav-buttons button.logout {
            background: #e53935;
        }
        .nav-buttons button.logout:hover {
            background: #b71c1c;
        }
        .main-content {
            text-align: center;
            padding: 60px 20px;
        }
        .main-content img.company-image {
            width: 300px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }
        .main-content h2 {
            font-size: 28px;
            color: #004d40;
            margin-bottom: 20px;
        }
        .main-content p.description {
            max-width: 750px;
            margin: 0 auto;
            font-size: 18px;
            line-height: 1.6;
            color: #333;
        }
        .footer {
            text-align: center;
            padding: 15px;
            background: #004d40;
            color: #fff;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .gps-info {
            color: #004d40;
            font-weight: bold;
            margin: 20px 0 0;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTAn86MfzjoogXI0jNgrcoCu6Dc1ze28tMXEw&s" class="logo" alt="ISDN Logo">
    <h1>ISDN Sales Distribution System</h1>
</div>

<div class="nav-buttons">
<?php if($role === 'HO_Admin'): ?>
    <button onclick="location.href='inventory.php'">Inventory Management</button>
    <button onclick="location.href='payment.php'">Payment Management</button>
    <button onclick="location.href='admin_products.php'">All Products</button>
    <button onclick="location.href='admin_orders.php'">All Orders</button>
    <button onclick="location.href='admin_users.php'">Manage Users</button>
    <button onclick="location.href='assigned_delivery.php'">Assign Delivery</button>
    <button onclick="location.href='delivery_history.php'">Delivery History</button>

<?php elseif($role === 'RDC_Staff'): ?>
    <button onclick="location.href='inventory.php'">Inventory Management</button>
    <button onclick="location.href='payment.php'">Payment Processing</button>
    <button onclick="location.href='rdc_manage.php'">Manage Products</button>
    <button onclick="location.href='rdc_orders.php'">View Orders</button>
    <button onclick="location.href='rdc_reports.php'">Reports</button>
    <button onclick="location.href='assigned_delivery.php'">Assign Delivery</button>
    <button onclick="location.href='delivery_history.php'">Delivery History</button>

<?php elseif($role === 'Customer'): ?>
    <button onclick="location.href='products.php'">View Products</button>
    <button onclick="location.href='order_list.php'">My Orders</button>
    <button onclick="location.href='payment.php'">View Payment Status</button>
    <button onclick="location.href='delivery.php'">Track Delivery</button>
    <button onclick="location.href='delivery_history.php'">Delivery History</button>

<?php elseif($role === 'Delivery_Person'): ?>
    <button onclick="location.href='my_assigned_orders.php'">My Assigned Orders</button>
    <button onclick="location.href='delivery_history.php'">Delivery History</button>
<?php endif; ?>

    <button class="logout" onclick="location.href='logout.php'">Logout</button>
</div>

<div class="main-content">
    <h2>Welcome, <?= $username ?>!</h2>
    <p class="description">
        ISDN Sales Distribution System is your trusted partner in delivering quality products efficiently and securely.
        Our platform empowers admins, RDC staff, customers, and delivery personnel to manage, track, and process orders
        seamlessly. With real-time updates and GPS-enabled delivery tracking, we ensure timely deliveries and
        complete transparency for all stakeholders. Join us and experience streamlined distribution like never before!
    </p>

<?php if($role === 'Delivery_Person' && count($orders) > 0): ?>
    <p class="gps-info">Auto GPS tracking is enabled for your pending/on-the-way orders.</p>
    <script>
        const fallbackLat = 6.9271;
        const fallbackLng = 79.8612;
        const orders = <?= json_encode($order_ids); ?>;

        function sendGPS(lat, lng, orderId){
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_location.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(`order_id=${orderId}&current_lat=${lat}&current_lng=${lng}`);
        }

        function updateAllOrdersGPS(){
            if(navigator.geolocation){
                navigator.geolocation.getCurrentPosition(function(pos){
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    orders.forEach(orderId => sendGPS(lat, lng, orderId));
                }, function(err){
                    orders.forEach(orderId => sendGPS(fallbackLat, fallbackLng, orderId));
                });
            } else {
                orders.forEach(orderId => sendGPS(fallbackLat, fallbackLng, orderId));
            }
        }

        setInterval(updateAllOrdersGPS, 10000);
        updateAllOrdersGPS();
    </script>
<?php endif; ?>
</div>

<div class="footer">
© 2026 ISDN System | Designed by N&R
</div>

</body>
</html>
