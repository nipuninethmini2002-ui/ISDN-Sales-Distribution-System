<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(!in_array($_SESSION['role'], ['RDC_Staff','HO_Admin'])){
    die("Access denied. Only staff or admin can view this page.");
}

include 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Orders - ISDN</title>
    <style>
        body {font-family: Arial, sans-serif; background: #f4f4f4; margin:0; padding:0;}
        .header {background: #4CAF50; color: #fff; padding:15px; text-align:center;}
        .container {padding:20px; max-width:1000px; margin:auto;}
        .top-links {margin-bottom:15px;}
        .top-links button {padding:8px 16px; margin-right:10px; border:none; border-radius:5px; cursor:pointer; background:#4CAF50; color:#fff;}
        .top-links button:hover {background:#45a049;}
        table {width:100%; border-collapse:collapse; background:#fff; box-shadow:0 0 10px rgba(0,0,0,0.1); border-radius:5px; overflow:hidden;}
        table th, table td {padding:12px; text-align:left; border-bottom:1px solid #ddd;}
        table th {background:#4CAF50; color:#fff;}
    </style>
</head>
<body>

<div class="header">
    <h2>All Orders - ISDN</h2>
</div>

<div class="container">
    <div class="top-links">
        <button onclick="location.href='dashboard.php'">Dashboard</button>
        <button onclick="location.href='logout.php'">Logout</button>
    </div>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Order Date</th>
            <th>Status</th>
            <th>Items</th>
        </tr>

        <?php
        $sql_orders = "SELECT o.*, u.username FROM orders o 
                       JOIN users u ON o.customer_id = u.user_id
                       ORDER BY o.order_date DESC";
        $result_orders = $conn->query($sql_orders);

        if($result_orders->num_rows > 0){
            while($order = $result_orders->fetch_assoc()){
                echo "<tr>";
                echo "<td>".$order['order_id']."</td>";
                echo "<td>".$order['username']."</td>";
                echo "<td>".$order['order_date']."</td>";
                echo "<td>".$order['status']."</td>";
                echo "<td>";

                $sql_items = "SELECT oi.quantity, p.name 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.product_id 
                              WHERE oi.order_id=".$order['order_id'];
                $result_items = $conn->query($sql_items);

                while($item = $result_items->fetch_assoc()){
                    echo $item['name']." (Qty: ".$item['quantity'].")<br>";
                }

                echo "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No orders found.</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>
