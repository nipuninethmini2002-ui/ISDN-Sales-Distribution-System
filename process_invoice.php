<?php
session_start();
include 'db.php';

// --------------------
// SESSION CHECK
// --------------------
if (!isset($_SESSION['recent_order'])) {
    die("Invalid access");
}

$order = $_SESSION['recent_order'];

$order_id = $order['order_id'];
$total_amount = $order['total_amount'];
$invoice_date = date("Y-m-d");
$invoice_status = "Paid"; // or Pending

// --------------------
// START TRANSACTION
// --------------------
$conn->begin_transaction();

try {

    // --------------------
    // 1. INSERT INVOICE
    // --------------------
    $invoice_sql = "INSERT INTO invoice 
        (order_id, invoice_date, total_amount, status)
        VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($invoice_sql);
    $stmt->bind_param("isds", $order_id, $invoice_date, $total_amount, $invoice_status);
    $stmt->execute();

    $invoice_id = $stmt->insert_id;

    // --------------------
    // 2. INSERT NOTIFICATION
    // --------------------
    $message = "Invoice #$invoice_id generated successfully for Order #$order_id";

    $notify_sql = "INSERT INTO notification
        (message, status, created_at)
        VALUES (?, 'Unread', NOW())";

    $stmt2 = $conn->prepare($notify_sql);
    $stmt2->bind_param("s", $message);
    $stmt2->execute();

    // --------------------
    // COMMIT
    // --------------------
    $conn->commit();

    // --------------------
    // REDIRECT TO INVOICE PAGE
    // --------------------
    header("Location: invoice_view.php?invoice_id=$invoice_id");
    exit();

} catch (Exception $e) {

    // --------------------
    // ROLLBACK IF ERROR
    // --------------------
    $conn->rollback();
    echo "Invoice generate karaddi error ekak una.";
}
?>
