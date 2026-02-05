<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Fetch all invoices
$sql = "SELECT invoice_id, order_id, invoice_date, total_amount, status 
        FROM invoices 
        ORDER BY invoice_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoices</title>
    <style>
        body{
            font-family: Arial;
            background:#f4f6f8;
        }
        table{
            width:90%;
            margin:40px auto;
            border-collapse:collapse;
            background:#fff;
        }
        th, td{
            border:1px solid #ccc;
            padding:10px;
            text-align:center;
        }
        th{
            background:#1976D2;
            color:white;
        }
        .btn{
            padding:6px 12px;
            background:#2e7d32;
            color:white;
            text-decoration:none;
            border-radius:4px;
        }
        .btn:hover{
            background:#1b5e20;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Invoice List</h2>

<table>
    <tr>
        <th>Invoice ID</th>
        <th>Order ID</th>
        <th>Invoice Date</th>
        <th>Total Amount</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['invoice_id']; ?></td>
                <td><?= $row['order_id']; ?></td>
                <td><?= $row['invoice_date']; ?></td>
                <td>Rs. <?= number_format($row['total_amount'],2); ?></td>
                <td><?= $row['status']; ?></td>
                <td>
                    <a class="btn" 
                       href="invoice_view.php?invoice_id=<?= $row['invoice_id']; ?>">
                       View
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="6">No invoices found</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
