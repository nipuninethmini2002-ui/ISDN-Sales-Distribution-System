<?php
session_start();
include 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'HO_Admin'){
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Reports - ISDN</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        h2 { color: #4CAF50; }
        table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-top: 20px; }
        table th, table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        table th { background: #4CAF50; color: #fff; }
        a.button { padding: 8px 15px; background: #4CAF50; color: #fff; text-decoration: none; border-radius: 5px; }
        a.button:hover { background: #388e3c; }
    </style>
</head>
<body>
<h2>Admin Reports - ISDN</h2>

<p>Generate and view sales and order reports.</p>

<a href="generate_orders_report.php" class="button">Orders Report</a>
<a href="generate_sales_report.php" class="button">Sales Report</a>

<table>
    <tr>
        <th>Order ID</th>
        <th>Customer</th>
        <th>Date</th>
        <th>Status</th>
        <th>Total Items</th>
    </tr>
    <?php
    $result = $conn->query("SELECT o.order_id, c.username, o.order_date, o.status, 
                                    (SELECT SUM(quantity) FROM order_items WHERE order_id=o.order_id) AS total_items
                             FROM orders o
                             JOIN users c ON o.customer_id = c.user_id
                             ORDER BY o.order_date DESC");
    while($row = $result->fetch_assoc()){
        echo "<tr>
                <td>".$row['order_id']."</td>
                <td>".htmlspecialchars($row['username'])."</td>
                <td>".$row['order_date']."</td>
                <td>".$row['status']."</td>
                <td>".$row['total_items']."</td>
              </tr>";
    }
    ?>
</table>

</body>
</html>
