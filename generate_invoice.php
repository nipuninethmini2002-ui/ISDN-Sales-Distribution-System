<?php
session_start();
include 'db.php';

if (!isset($_GET['invoice_id'])) {
    die("Invoice ID missing. Use: invoice_view.php?invoice_id=1");
}

$invoice_id = intval($_GET['invoice_id']);

$stmt = $conn->prepare("
    SELECT i.invoice_id, i.order_id, i.invoice_date, i.total_amount, i.status,
           c.customer_id, c.phone, c.address, c.customer_branch
    FROM invoices i
    LEFT JOIN orders o ON o.order_id = i.order_id
    LEFT JOIN customers c ON c.customer_id = o.customer_id
    WHERE i.invoice_id = ?
");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Invoice not found. Please check the Invoice ID.");
}

$invoice = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?= $invoice['invoice_id']; ?></title>
    <style>
        body{ font-family: 'Segoe UI'; background:#f4f6f8; }
        .container{ width:700px; margin:50px auto; padding:30px; background:#fff; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.15); }
        table{ width:100%; border-collapse:collapse; }
        table th{ text-align:left; background:#e8f5e9; padding:10px; width:35%; }
        table td{ padding:10px; background:#fafafa; }
        table, th, td{ border:1px solid #ddd; }
        .btn{ display:inline-block; margin-top:20px; padding:10px 20px; background:#1976D2; color:#fff; text-decoration:none; border-radius:6px; }
        .btn:hover{ background:#0d47a1; }
    </style>
</head>
<body>
<div class="container">
    <h2>Invoice Details</h2>
    <table>
        <tr><th>Invoice ID</th><td><?= $invoice['invoice_id']; ?></td></tr>
        <tr><th>Order ID</th><td><?= $invoice['order_id']; ?></td></tr>
        <tr><th>Customer ID</th><td><?= $invoice['customer_id'] ?? 'N/A'; ?></td></tr>
        <tr><th>Phone</th><td><?= $invoice['phone'] ?? 'N/A'; ?></td></tr>
        <tr><th>Address</th><td><?= $invoice['address'] ?? 'N/A'; ?></td></tr>
        <tr><th>Branch</th><td><?= $invoice['customer_branch'] ?? 'N/A'; ?></td></tr>
        <tr><th>Invoice Date</th><td><?= $invoice['invoice_date']; ?></td></tr>
        <tr><th>Total Amount</th><td>Rs. <?= number_format($invoice['total_amount'],2); ?></td></tr>
        <tr><th>Status</th><td><?= $invoice['status']; ?></td></tr>
    </table>

    <a href="payment.php" class="btn">⬅ Back to Payments</a>
</div>
</body>
</html>
