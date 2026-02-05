<?php
session_start();
include 'db.php';

// ===== PHPMailer =====
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/vendor/autoload.php';

// Only admin or HO_Admin can access
if(!isset($_SESSION['role']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'HO_Admin')){
    header("Location: login.php");
    exit();
}

// --- Update order status if submitted ---
if(isset($_POST['order_id']) && isset($_POST['status'])){
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];

    // 1️⃣ Fetch old status first
    $stmt_old = $conn->prepare("SELECT status FROM orders WHERE order_id=?");
    $stmt_old->bind_param("i", $order_id);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    $old_status_row = $result_old->fetch_assoc();
    $old_status = $old_status_row['status'] ?? '';
    $stmt_old->close();

    // 2️⃣ Update order table
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();

    // 3️⃣ Fetch customer info
    $stmt_cust = $conn->prepare("
        SELECT u.user_id, u.email, u.username
        FROM orders o
        JOIN users u ON o.customer_id = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt_cust->bind_param("i", $order_id);
    $stmt_cust->execute();
    $result_cust = $stmt_cust->get_result();
    $customer = $result_cust->fetch_assoc();
    $stmt_cust->close();

    if($customer){
        $customer_id = $customer['user_id'];
        $customer_email = $customer['email'];
        $customer_name = $customer['username'];

        // 4️⃣ Insert notification with dynamic type
        $notif_msg = "Your order #$order_id status has been updated from '$old_status' to '$new_status'.";
        $stmt_notif = $conn->prepare("
            INSERT INTO notifications (user_id, message, type, is_read, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        // type = current status
        $stmt_notif->bind_param("iss", $customer_id, $notif_msg, $new_status);
        if(!$stmt_notif->execute()){
            error_log("Notification insert failed for order #$order_id: " . $stmt_notif->error);
        }
        $stmt_notif->close();

        // 5️⃣ Send email
        if(!empty($customer_email)){
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'isdnsystem@gmail.com'; // change
                $mail->Password   = 'webnqrkahihtfato';    // your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('isdnsystem@gmail.com', 'ISDN System');
                $mail->addAddress($customer_email, $customer_name);

                $mail->isHTML(false);
                $mail->Subject = "Order #$order_id Status Update";
                $mail->Body = "Hello $customer_name,\n\n".
                              "Your order #$order_id status has been updated from '$old_status' to '$new_status'.\n".
                              "You can check your order status in your account.\n\n".
                              "Thank you,\nISDN Team";

                $mail->send();
            } catch (Exception $e) {
                error_log("PHPMailer Error for order #$order_id: " . $mail->ErrorInfo);
            }
        }
    }

    // Refresh page
    header("Location: admin_orders.php");
    exit();
}

// --- Fetch all orders with customer info ---
$stmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.status, u.username
    FROM orders o
    LEFT JOIN users u ON o.customer_id = u.user_id
    ORDER BY o.order_date DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Orders</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table th, table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        table th { background: #4CAF50; color: #fff; }
        select, button { padding: 5px 8px; margin-top: 2px; }
        button { background: #4CAF50; color: #fff; border: none; cursor: pointer; border-radius: 4px; }
        button:hover { background: #388e3c; }
    </style>
</head>
<body>

<h2>Admin Orders Management</h2>

<table>
<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Order Date</th>
    <th>Status</th>
    <th>Update Status</th>
</tr>

<?php if($result->num_rows > 0): ?>
    <?php while($order = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo $order['order_id']; ?></td>
        <td><?php echo htmlspecialchars($order['username'] ?? "Unknown Customer"); ?></td>
        <td><?php echo date("Y-m-d H:i", strtotime($order['order_date'])); ?></td>
        <td><?php echo $order['status']; ?></td>
        <td>
            <form method="post" action="">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                <select name="status">
                    <option value="Placed" <?php if($order['status']=='Placed') echo 'selected'; ?>>Placed</option>
                    <option value="Processing" <?php if($order['status']=='Processing') echo 'selected'; ?>>Processing</option>
                    <option value="Delivered" <?php if($order['status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                    <option value="Cancelled" <?php if($order['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
                <button type="submit">Update</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="5">No orders found.</td></tr>
<?php endif; ?>

</table>

<?php $stmt->close(); $conn->close(); ?>
</body>
</html>
