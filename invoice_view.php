<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role'])){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['invoice_id'])){
    die("Invoice ID missing in URL.");
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
if(!$stmt){
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Invoice not found.");
}

$invoice = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?= htmlspecialchars($invoice['invoice_id']); ?></title>
    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f4f6f8;
        }
        .container{
            width:700px;
            margin:50px auto;
            background:#fff;
            padding:25px;
            border-radius:10px;
            box-shadow:0 0 15px rgba(0,0,0,0.15);
        }
        h2{
            text-align:center;
            color:#2e7d32;
        }
        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }
        th, td{
            border:1px solid #ddd;
            padding:10px;
            text-align:center;
        }
        th{
            background:#4CAF50;
            color:white;
        }
        .btn{
            display:block;
            width:200px;
            margin:20px auto;
            padding:10px;
            text-align:center;
            background:#1976D2;
            color:white;
            text-decoration:none;
            border-radius:6px;
        }
        .btn:hover{
            background:#0d47a1;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Invoice Details</h2>

    <table>
        <tr>
            <th>Invoice ID</th>
            <th>Order ID</th>
            <th>Invoice Date</th>
            <th>Total Amount</th>
            <th>Status</th>
        </tr>
        <tr>
            <td><?= htmlspecialchars($invoice['invoice_id']); ?></td>
            <td><?= htmlspecialchars($invoice['order_id']); ?></td>
            <td><?= htmlspecialchars($invoice['invoice_date']); ?></td>
            <td>Rs. <?= number_format($invoice['total_amount'],2); ?></td>
            <td><?= htmlspecialchars($invoice['status']); ?></td>
        </tr>
    </table>

    <a href="payment.php" class="btn">⬅ Back to Payment</a>

</div>

</body>
</html>
