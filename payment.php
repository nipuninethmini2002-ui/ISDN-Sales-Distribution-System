<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$payments = [];

if($role === 'Customer'){
    if(!isset($_SESSION['customer_id'])){
        die("Customer ID not set in session.");
    }
    $customer_id = $_SESSION['customer_id'];

    $stmt = $conn->prepare("
        SELECT p.payment_id, p.invoice_id, p.amount, p.payment_method, p.payment_date
        FROM payments p
        JOIN invoices i ON i.invoice_id = p.invoice_id
        JOIN orders o ON o.order_id = i.order_id
        WHERE o.customer_id = ?
        ORDER BY p.payment_date DESC
    ");
    if(!$stmt){
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $payments[] = $row;
    }
    $stmt->close();
} else {
    $result = $conn->query("
        SELECT p.payment_id, p.invoice_id, p.amount, p.payment_method, p.payment_date
        FROM payments p
        ORDER BY p.payment_date DESC
    ");
    if(!$result){
        die("Query failed: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Management</title>
    <style>
        body{
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:linear-gradient(to right,#e8f5e9,#c8e6c9);
            margin:0;
            min-height:100vh;
        }
        .header{
            background:#4CAF50;
            color:#fff;
            padding:20px;
            text-align:center;
        }
        .container{
            width:90%;
            max-width:1000px;
            margin:30px auto;
            background:#fff;
            padding:30px;
            border-radius:15px;
            box-shadow:0 12px 25px rgba(0,0,0,0.15);
        }
        h2{
            text-align:center;
            color:#2e7d32;
            margin-bottom:20px;
        }
        table{
            width:100%;
            border-collapse:collapse;
        }
        th{
            background:#4CAF50;
            color:#fff;
            padding:12px;
        }
        td{
            padding:10px;
            text-align:center;
            border-bottom:1px solid #ddd;
        }
        tr:hover{
            background:#f1f8e9;
        }
        a.btn{
            display:inline-block;
            padding:6px 12px;
            background:#1976D2;
            color:#fff;
            border-radius:6px;
            text-decoration:none;
        }
        a.btn:hover{
            background:#0d47a1;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>ISDN Payment Management</h1>
</div>

<div class="container">
<h2><?= ($role === 'Customer') ? 'My Payments' : 'All Payments'; ?></h2>

<table>
<tr>
    <th>Payment ID</th>
    <th>Invoice ID</th>
    <th>Amount</th>
    <th>Payment Method</th>
    <th>Payment Date</th>
</tr>

<?php
if($role === 'Customer'){
    foreach($payments as $row){ ?>
    <tr>
        <td><?= htmlspecialchars($row['payment_id']); ?></td>
        <td>
            <a href="generate_invoice.php?invoice_id=<?= htmlspecialchars($row['invoice_id']); ?>" class="btn">
                View Invoice #<?= htmlspecialchars($row['invoice_id']); ?>
            </a>
        </td>
        <td>Rs. <?= number_format($row['amount'],2); ?></td>
        <td><?= htmlspecialchars($row['payment_method']); ?></td>
        <td><?= htmlspecialchars($row['payment_date']); ?></td>
    </tr>
<?php }
} else {
    while($row = $result->fetch_assoc()){ ?>
    <tr>
        <td><?= htmlspecialchars($row['payment_id']); ?></td>
        <td>
            <a href="generate_invoice.php?invoice_id=<?= htmlspecialchars($row['invoice_id']); ?>" class="btn">
                View Invoice #<?= htmlspecialchars($row['invoice_id']); ?>
            </a>
        </td>
        <td>Rs. <?= number_format($row['amount'],2); ?></td>
        <td><?= htmlspecialchars($row['payment_method']); ?></td>
        <td><?= htmlspecialchars($row['payment_date']); ?></td>
    </tr>
<?php }
} ?>
</table>
</div>

</body>
</html>
