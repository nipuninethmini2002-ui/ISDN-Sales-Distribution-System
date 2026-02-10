<?php
session_start();
include 'db.php';

// --- 1. Check if Delivery Person is logged in ---
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'Delivery_Person'){
    header("Location: login.php");
    exit();
}

$delivery_user_id = $_SESSION['user_id'];

// --- 2. Get delivery_person_id ---
$query = $conn->prepare("SELECT delivery_person_id FROM delivery_person WHERE user_id=?");
if(!$query){
    die("Prepare failed (delivery_person_id): " . $conn->error);
}
$query->bind_param("i", $delivery_user_id);
$query->execute();
$result = $query->get_result();
if($result->num_rows == 0){
    die("Delivery person not found.");
}
$dp = $result->fetch_assoc();
$delivery_person_id = $dp['delivery_person_id'];

// --- 3. Check order_id parameter ---
if(!isset($_GET['order_id'])){
    die("No order selected.");
}
$order_id = intval($_GET['order_id']);

// --- 4. Fetch order details for this delivery person ---
$stmt_order = $conn->prepare("
    SELECT o.order_id, o.status AS order_status,
           c.customer_id, c.address, c.customer_phone
    FROM delivery d
    JOIN orders o ON d.order_id = o.order_id
    JOIN customers c ON o.customer_id = c.customer_id
    WHERE d.order_id=? AND d.delivery_person_id=?
");
if(!$stmt_order){
    die("Prepare failed (order details): " . $conn->error);
}
$stmt_order->bind_param("ii", $order_id, $delivery_person_id);
$stmt_order->execute();
$order_result = $stmt_order->get_result();
if($order_result->num_rows == 0){
    die("Invalid order selected or not assigned to you.");
}
$order = $order_result->fetch_assoc();

// --- 5. Fetch order items ---
$stmt_items = $conn->prepare("
    SELECT oi.product_id, p.name AS product_name, oi.quantity
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id=?
");
if(!$stmt_items){
    die("Prepare failed (order items): " . $conn->error);
}
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$order_items_result = $stmt_items->get_result();
$order_items = $order_items_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right, #e0f7fa, #e1f5fe);
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #00796b;
        }
        table {
            width: 70%;
            margin: 0 auto 30px auto;
            border-collapse: collapse;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        th, td {
            padding: 12px 18px;
            text-align: left;
        }
        th {
            background-color: #00796b;
            color: #fff;
            font-size: 15px;
        }
        tr:nth-child(even) {
            background-color: #e0f2f1;
        }
        tr:hover {
            background-color: #b2dfdb;
        }
        ul {
            padding-left: 20px;
            margin: 0;
        }
        .btn {
            display: inline-block;
            background-color: #2196F3;
            color: #fff;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
            transition: 0.3s;
            text-align: center;
        }
        .btn:hover {
            background-color: #1565c0;
        }
        @media(max-width: 800px){
            table {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<h2>Order Details (Order ID: <?= $order['order_id'] ?>)</h2>

<table>
    <tr>
        <th>Customer ID</th>
        <td><?= $order['customer_id'] ?></td>
    </tr>
    <tr>
        <th>Address</th>
        <td><?= $order['address'] ?></td>
    </tr>
    <tr>
        <th>Phone</th>
        <td><?= $order['customer_phone'] ?></td>
    </tr>
    <tr>
        <th>Order Status</th>
        <td><?= $order['order_status'] ?></td>
    </tr>
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

</body>
</html>
