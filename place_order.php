<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || !isset($_SESSION['customer_id']) || !isset($_SESSION['branch'])){
    header("Location: login.php");
    exit();
}

include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/vendor/autoload.php';

$customer_id = $_SESSION['customer_id'];
$branch = $_SESSION['branch']; 

if(isset($_POST['cancel_order']) && isset($_POST['order_id'])){
    $order_id = (int)$_POST['order_id'];

    $stmt_item = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt_item->bind_param("i", $order_id);
    $stmt_item->execute();
    $stmt_item->close();

    $stmt_order = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt_order->bind_param("i", $order_id);
    $stmt_order->execute();
    $stmt_order->close();

    unset($_SESSION['recent_order']);

    header("Location: products.php");
    exit();
}

if(!isset($_POST['product_id']) || !isset($_POST['quantity'])){
    die("Invalid request. Please order from the products page.");
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

$stmt_prod = $conn->prepare("SELECT name, price, stock_quantity, description FROM products WHERE product_id = ?");
$stmt_prod->bind_param("i", $product_id);
$stmt_prod->execute();
$result_prod = $stmt_prod->get_result();

if($result_prod->num_rows === 0){
    die("Product not found.");
}

$product = $result_prod->fetch_assoc();

if($quantity > $product['stock_quantity']){
    die("Insufficient stock. Available quantity: ".$product['stock_quantity']);
}

$stmt_order = $conn->prepare("INSERT INTO orders (customer_id, branch, status, order_date) VALUES (?, ?, 'Pending', NOW())");
$stmt_order->bind_param("is", $customer_id, $branch);
$stmt_order->execute();
$order_id = $stmt_order->insert_id;

$stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
$stmt_item->bind_param("iii", $order_id, $product_id, $quantity);
$stmt_item->execute();

$stmt_prod->close();
$stmt_order->close();
$stmt_item->close();

$_SESSION['recent_order'] = [
    'order_id' => $order_id,
    'product_id' => $product_id,
    'quantity' => $quantity,
    'name' => $product['name'],
    'price' => $product['price'],
    'branch' => $branch 
];

$notification_msg = "Customer #$customer_id placed an order (#$order_id) for ".$product['name']." (Qty: $quantity).";
$stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message, type, is_read) VALUES (?, ?, ?, 0)");
$type = 'order';
$stmt_notif->bind_param("iss", $customer_id, $notification_msg, $type);
$stmt_notif->execute();
$stmt_notif->close();

$stmt_cust = $conn->prepare("
    SELECT u.email, u.username 
    FROM customers c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.customer_id = ?
");
$stmt_cust->bind_param("i", $customer_id);
$stmt_cust->execute();
$result_cust = $stmt_cust->get_result();
$customer = $result_cust->fetch_assoc();
$stmt_cust->close();

$email_to = $customer['email'] ?? '';
$username = $customer['username'] ?? '';

if(!empty($email_to)){
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'isdnsystem@gmail.com';
        $mail->Password   = 'webnqrkahihtfato';     
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPDebug  = 0; 

        $mail->setFrom('isdnsystem@gmail.com', 'ISDN System');
        $mail->addAddress($email_to, $username);

        $mail->isHTML(false);
        $mail->Subject = 'Your ISDN Order Confirmation (#'.$order_id.')';
        $mail->Body =
            "Hello $username,\n\n".
            "Thank you for your order at ISDN Sales Distribution System!\n\n".
            "Order Details:\n".
            "Order ID: $order_id\n".
            "Branch: $branch\n". 
            "Product: ".$product['name']."\n".
            "Description: ".$product['description']."\n".
            "Quantity: $quantity\n".
            "Price per item: Rs. ".number_format($product['price'],2)."\n".
            "Total: Rs. ".number_format($product['price'] * $quantity,2)."\n".
            "Order Date & Time: ".date("Y-m-d H:i:s")."\n\n".
            "We will notify you once your order is processed.\n\n".
            "For any queries, contact us:\n".
            "Email: isdnsystem@gmail.com\n".
            "Phone: +94 75 556 4330\n".
            "Address: ISDN Headquarters, Colombo, Sri Lanka\n\n".
            "Best Regards,\nISDN Team";

        $mail->send();
    } catch (Exception $e) {
        error_log("PHPMailer Error for order #$order_id: " . $mail->ErrorInfo);
        echo "Email could not be sent. Please check the logs.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Placed - ISDN</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .header { background: #4CAF50; color: #fff; padding: 15px; text-align: center; }
        .container { max-width: 700px; margin: 30px auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #4CAF50; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        table th { background: #4CAF50; color: #fff; }
        .btn { display: inline-block; padding: 10px 18px; margin-top: 20px; margin-right: 10px; background: #4CAF50; color: #fff; text-decoration: none; border-radius: 5px; transition: 0.3s; border: none; cursor: pointer; }
        .btn:hover { background: #45a049; }
        .cancel { background: #e74c3c; }
        .payment-option { margin-top: 15px; }
    </style>
</head>
<body>
<div class="header">
    <h2>Order Confirmation - ISDN</h2>
</div>

<div class="container">
    <h3>Order Placed Successfully!</h3>
    <p>Your order has been recorded. Please select a payment method to complete the order:</p>

    <table>
        <tr><th>Order ID</th><td><?php echo $order_id; ?></td></tr>
        <tr><th>Branch</th><td><?php echo htmlspecialchars($branch); ?></td></tr> 
        <tr><th>Product</th><td><?php echo htmlspecialchars($product['name']); ?></td></tr>
        <tr><th>Quantity</th><td><?php echo $quantity; ?></td></tr>
        <tr><th>Price (per item)</th><td>Rs. <?php echo number_format($product['price'],2); ?></td></tr>
        <tr><th>Total Price</th><td>Rs. <?php echo number_format($product['price'] * $quantity,2); ?></td></tr>
        <tr><th>Order Date & Time</th><td><?php echo date("Y-m-d H:i:s"); ?></td></tr>
    </table>

    <form method="post" action="process_payment.php" class="payment-option" style="display:inline-block;">
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
        <label><input type="radio" name="payment_method" value="Cash on Delivery" required> Cash on Delivery</label><br>
        <label><input type="radio" name="payment_method" value="Online Transfer" required> Online Transfer</label><br>
        <button type="submit" class="btn">Pay Now</button>
    </form>

    <form method="post" style="display:inline-block;">
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
        <button type="submit" name="cancel_order" class="btn cancel">Cancel Order</button>
    </form>
</div>
</body>
</html>
