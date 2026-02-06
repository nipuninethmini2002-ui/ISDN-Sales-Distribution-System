<?php
session_start();
if(!isset($_SESSION['user_id']) || !isset($_SESSION['customer_id'])){
    header("Location: login.php");
    exit();
}

include 'db.php';

$payment_msg = '';
if(isset($_SESSION['payment_success'])){
    $payment_msg = $_SESSION['payment_success'];
    unset($_SESSION['payment_success']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products - ISDN</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; margin:0; padding:0; }
        .header { background:#4CAF50; color:#fff; padding:15px; text-align:center; }
        .container { padding:20px; max-width:900px; margin:auto; }
        .message { padding:10px; margin-bottom:15px; border-radius:5px; color:#fff; background:#27ae60; }
        table { width:100%; border-collapse: collapse; background:#fff; box-shadow:0 0 10px rgba(0,0,0,0.1); border-radius:5px; overflow:hidden; }
        table th, table td { padding:12px; text-align:left; border-bottom:1px solid #ddd; }
        table th { background:#4CAF50; color:#fff; }
        input[type=number] { width:60px; padding:5px; }
        input[type=submit] { padding:6px 12px; background:#4CAF50; border:none; color:#fff; border-radius:4px; cursor:pointer; }
        input[type=submit]:hover { background:#45a049; }
        .top-links { margin-bottom:15px; }
        .top-links button { padding:8px 16px; margin-right:10px; border:none; border-radius:5px; cursor:pointer; background:#4CAF50; color:#fff; }
        .top-links button:hover { background:#45a049; }
        .low-stock { color:orange; font-weight:bold; }
        .out-stock { color:red; font-weight:bold; }
    </style>
</head>
<body>

<div class="header">
    <h2>Products - ISDN</h2>
</div>

<div class="container">

    <?php if($payment_msg): ?>
        <div class="message"><?= htmlspecialchars($payment_msg) ?></div>
    <?php endif; ?>

    <div class="top-links">
        <button onclick="location.href='dashboard.php'">Dashboard</button>
        <button onclick="location.href='order_list.php'">My Orders</button>
        <button onclick="location.href='logout.php'">Logout</button>
    </div>

    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Stock</th><th>Order</th>
        </tr>

        <?php
        $result = $conn->query("SELECT * FROM products");
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $stock = (int)$row['stock_quantity'];
                $stock_msg = $stock == 0 ? "<span class='out-stock'>Out of Stock</span>" :
                             ($stock <=5 ? "<span class='low-stock'>$stock</span>" : $stock);
                $disabled = $stock == 0 ? 'disabled' : '';
                echo "<tr>
                    <td>{$row['product_id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['price']}</td>
                    <td>$stock_msg</td>
                    <td>
                        <form method='post' action='place_order.php'>
                            <input type='hidden' name='product_id' value='{$row['product_id']}'>
                            <input type='number' name='quantity' value='1' min='1' max='$stock' $disabled required>
                            <input type='submit' value='Order' $disabled>
                        </form>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No products found</td></tr>";
        }
        ?>
    </table>
</div>
</body>
</html>
