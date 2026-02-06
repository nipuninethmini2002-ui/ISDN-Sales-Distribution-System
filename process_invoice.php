<?php
session_start();
include 'db.php';

if (!isset($_SESSION['customer_id']) || !isset($_SESSION['recent_order'])) {
    die("Invalid access");
}

$customer_id = $_SESSION['customer_id'];
$order = $_SESSION['recent_order'];

$order_id = $order['order_id'];
$total_amount = $order['total_amount'];
$invoice_date = date("Y-m-d");
$status = "Paid"; 
$conn->begin_transaction();

try {

    $invoice_sql = "INSERT INTO invoice 
        (order_id, invoice_date, total_amount, status) 
        VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($invoice_sql);
    $stmt->bind_param("isds", $order_id, $invoice_date, $total_amount, $status);
    $stmt->execute();

    $invoice_id = $stmt->insert_id;

    $message = "Your invoice #$invoice_id has been generated for Order #$order_id";

    $notify_sql = "INSERT INTO notifications 
        (customer_id, message, status, created_at) 
        VALUES (?, ?, 'Unread', NOW())";

    $stmt2 = $conn->prepare($notify_sql);
    $stmt2->bind_param("is", $customer_id, $message);
    $stmt2->execute();

    $conn->commit();

    header("Location: invoice_view.php?invoice_id=$invoice_id");
    exit();

} catch (Exception $e) {

    $conn->rollback();
    echo "Error occurred while generating invoice.";
}
?>
