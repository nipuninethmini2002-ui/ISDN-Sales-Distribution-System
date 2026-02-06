<?php
session_start();
if(!isset($_SESSION['user_id']) || !isset($_SESSION['customer_id'])){
    header("Location: login.php");
    exit();
}

include 'db.php';
$customer_id = $_SESSION['customer_id']; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders - ISDN</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .header { background: #4CAF50; color: #fff; padding: 15px; text-align: center; }
        .container { padding: 20px; max-width: 900px; margin: auto; }
        .top-links { margin-bottom: 15px; }
        .top-links button { padding: 8px 16px; margin-right: 10px; border: none; border-radius: 5px; cursor: pointer; background: #4CAF50; color: #fff; }
        .top-links button:hover { background: #45a049; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 5px; overflow: hidden; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: top; }
        table th { background: #4CAF50; color: #fff; }
        a.assign-delivery { padding:5px 10px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:4px; display:inline-block; margin-top:5px; }
        a.assign-delivery:hover { background:#45a049; }
        .error { color:red; margin-bottom:10px; }
    </style>
</head>
<body>

<div class="header">
    <h2>My Orders - ISDN</h2>
</div>

<div class="container">
    <div class="top-links">
        <button onclick="location.href='dashboard.php'">Dashboard</button>
        <button onclick="location.href='products.php'">Products</button>
        <button onclick="location.href='logout.php'">Logout</button>
    </div>

    <?php
    if(isset($_GET['error']) && $_GET['error'] === 'invalid_order'){
        echo "<p class='error'>Invalid order selected. Please try again.</p>";
    }
    ?>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Order Date & Time</th>
            <th>Status</th>
            <th>Items</th>
        </tr>

        <?php
        $stmt_orders = $conn->prepare("SELECT order_id, order_date, status FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
        if(!$stmt_orders){
            die("Prepare failed: " . $conn->error);
        }

        $stmt_orders->bind_param("i", $customer_id);
        $stmt_orders->execute();
        $result_orders = $stmt_orders->get_result();

        if($result_orders->num_rows > 0){
            while($order = $result_orders->fetch_assoc()){
                echo "<tr>";
                echo "<td>".htmlspecialchars($order['order_id'])."</td>";

                if(!empty($order['order_date']) && $order['order_date'] != '0000-00-00 00:00:00'){
                    $dateObj = new DateTime($order['order_date']);
                    echo "<td>".$dateObj->format('Y-m-d H:i:s')."</td>";
                } else {
                    echo "<td>Not available</td>";
                }

                echo "<td>".htmlspecialchars($order['status'])."</td>";
                echo "<td>";

                $stmt_items = $conn->prepare("
                    SELECT p.name, oi.quantity
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.product_id
                    WHERE oi.order_id = ?");
                if(!$stmt_items){
                    die("Prepare failed: " . $conn->error);
                }

                $stmt_items->bind_param("i", $order['order_id']);
                $stmt_items->execute();
                $result_items = $stmt_items->get_result();

                if($result_items->num_rows > 0){
                    while($item = $result_items->fetch_assoc()){
                        echo htmlspecialchars($item['name'])." (Qty: ".htmlspecialchars($item['quantity']).")<br>";
                    }
                } else {
                    echo "No items found";
                }

                $stmt_items->close();

                echo "<a class='assign-delivery' href='delivery.php?order_id=".urlencode($order['order_id'])."'>Assign Delivery</a>";

                echo "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No orders found.</td></tr>";
        }

        $stmt_orders->close();
        $conn->close();
        ?>
    </table>
</div>

</body>
</html>
