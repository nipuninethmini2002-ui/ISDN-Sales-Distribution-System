<?php
session_start();
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/vendor/autoload.php';

/* =====================
   SESSION CHECK
===================== */
if (!isset($_SESSION['user_id'], $_SESSION['recent_order'])) {
    header("Location: products.php");
    exit();
}

/* =====================
   PAYMENT METHOD CHECK
===================== */
if (empty($_POST['payment_method'])) {
    die("Invalid payment request");
}

/* =====================
   SESSION DATA
===================== */
$user_id = (int)$_SESSION['user_id']; // users.user_id
$order   = $_SESSION['recent_order'];

$order_id   = (int)$order['order_id'];
$product_id = (int)$order['product_id'];
$quantity   = (int)$order['quantity'];
$price      = (float)$order['price'];
$amount     = $price * $quantity;
$method     = trim($_POST['payment_method']);

$conn->begin_transaction();

try {

    /* =====================
       1️⃣ CREATE INVOICE
    ===================== */
    $stmt = $conn->prepare(
        "INSERT INTO invoices (order_id, invoice_date, total_amount, status)
         VALUES (?, NOW(), ?, 'Paid')"
    );
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("id", $order_id, $amount);
    $stmt->execute();
    $invoice_id = $stmt->insert_id;
    $stmt->close();

    /* =====================
       2️⃣ INSERT PAYMENT
    ===================== */
    $stmt = $conn->prepare(
        "INSERT INTO payments (invoice_id, amount, payment_method, payment_date)
         VALUES (?, ?, ?, NOW())"
    );
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("ids", $invoice_id, $amount, $method);
    $stmt->execute();
    $stmt->close();

    /* =====================
       3️⃣ UPDATE ORDER
    ===================== */
    $stmt = $conn->prepare(
        "UPDATE orders SET status='Paid' WHERE order_id=?"
    );
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    /* =====================
       4️⃣ UPDATE STOCK
    ===================== */
    $stmt = $conn->prepare(
        "UPDATE products 
         SET stock_quantity = stock_quantity - ?
         WHERE product_id=?"
    );
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();
    $stmt->close();

    /* =====================
       5️⃣ FETCH USER
    ===================== */
    $stmt = $conn->prepare(
        "SELECT username, email FROM users WHERE user_id=?"
    );
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        throw new Exception("User not found");
    }

    /* =====================
       6️⃣ INSERT NOTIFICATION ✅
    ===================== */
    $message = "Payment successful for Order #$order_id (Rs. " .
               number_format($amount, 2) . ")";

    $type = 'payment';
    $is_read = 0;

    $stmt = $conn->prepare(
        "INSERT INTO notifications (`user_id`, `message`, `type`, `is_read`, `created_at`)
         VALUES (?, ?, ?, ?, NOW())"
    );
    if (!$stmt) {
        throw new Exception("Notification prepare failed: " . $conn->error);
    }

    $stmt->bind_param("issi", $user_id, $message, $type, $is_read);
    $stmt->execute();
    $stmt->close();

    /* =====================
       7️⃣ SEND EMAIL
    ===================== */
    if (!empty($user['email'])) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'isdnsystem@gmail.com';
            $mail->Password   = 'webnqrkahihtfato';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('isdnsystem@gmail.com', 'ISDN System');
            $mail->addAddress($user['email'], $user['username']);

            $mail->Subject = "Payment Confirmation - Order #$order_id";
            $mail->Body =
                "Hello {$user['username']},\n\n" .
                "Payment successful.\n\n" .
                "Order ID: $order_id\n" .
                "Invoice ID: $invoice_id\n" .
                "Amount: Rs. " . number_format($amount, 2) . "\n\n" .
                "Thank you.\nISDN Team";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
        }
    }

    $conn->commit();
    unset($_SESSION['recent_order']);

    header("Location: generate_invoice.php?invoice_id=$invoice_id");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Payment failed: " . $e->getMessage());
}
?>
