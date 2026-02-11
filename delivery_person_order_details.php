<?php
session_start();
include 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'Delivery_Person'){
    header("Location: login.php");
    exit();
}

$delivery_user_id = $_SESSION['user_id'];

$query = $conn->prepare("SELECT delivery_person_id FROM delivery_person WHERE user_id=?");
$query->bind_param("i", $delivery_user_id);
$query->execute();
$result = $query->get_result();
$dp = $result->fetch_assoc();
$delivery_person_id = $dp['delivery_person_id'];

if(!isset($_GET['order_id'])){
    die("No order selected.");
}
$order_id = intval($_GET['order_id']);

$stmt_order = $conn->prepare("
    SELECT o.order_id, o.status AS order_status,
           c.customer_id, c.address, c.customer_phone
    FROM delivery d
    JOIN orders o ON d.order_id = o.order_id
    JOIN customers c ON o.customer_id = c.customer_id
    WHERE d.order_id=? AND d.delivery_person_id=?
");
$stmt_order->bind_param("ii", $order_id, $delivery_person_id);
$stmt_order->execute();
$order_result = $stmt_order->get_result();
if($order_result->num_rows == 0){
    die("Invalid order selected or not assigned to you.");
}
$order = $order_result->fetch_assoc();

$stmt_items = $conn->prepare("
    SELECT oi.product_id, p.name AS product_name, oi.quantity
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id=?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$order_items_result = $stmt_items->get_result();
$order_items = $order_items_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <style>
        body { font-family: Arial; padding:20px; background:#e0f7fa; }
        h2 { text-align:center; color:#00796b; }
        table { width:70%; margin:20px auto; border-collapse:collapse; background:#fff; border-radius:8px; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
        th, td { padding:10px; text-align:left; }
        th { background:#00796b; color:#fff; }
        tr:nth-child(even){background:#e0f2f1;}
        .btn { display:inline-block; padding:10px 15px; margin:10px; border-radius:6px; background:#2196F3; color:#fff; text-decoration:none; }
    </style>
</head>
<body>

<h2>Order Details (Order ID: <?= $order['order_id'] ?>)</h2>

<table>
    <tr><th>Customer ID</th><td><?= $order['customer_id'] ?></td></tr>
    <tr><th>Address</th><td><?= $order['address'] ?></td></tr>
    <tr><th>Phone</th><td><?= $order['customer_phone'] ?></td></tr>
    <tr><th>Order Status</th><td><?= $order['order_status'] ?></td></tr>
    <tr>
        <th>Order Items</th>
        <td>
            <ul>
            <?php foreach($order_items as $item): ?>
                <li><?= htmlspecialchars($item['product_name']) ?> (Qty: <?= $item['quantity'] ?>)</li>
            <?php endforeach; ?>
            </ul>
        </td>
    </tr>
</table>

<div style="text-align:center;">
    <a href="my_assigned_orders.php" class="btn">Back to My Orders</a>
</div>

<script>
const orderId = <?= $order_id ?>;

function sendLocation(lat, lng){
    fetch('update_location.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`order_id=${orderId}&current_lat=${lat}&current_lng=${lng}`
    });
}

function trackLocation(){
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(
            pos => sendLocation(pos.coords.latitude, pos.coords.longitude)
        );
    }
}

setInterval(trackLocation, 10000);
</script>

</body>
</html>
