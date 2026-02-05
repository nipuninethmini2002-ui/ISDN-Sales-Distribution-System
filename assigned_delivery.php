<?php
session_start();
include 'db.php';

// RDC Staff සහ HO Admin only
if(!isset($_SESSION['role']) || 
   ($_SESSION['role'] != 'RDC_Staff' && $_SESSION['role'] != 'HO_Admin')){
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$branch = $_SESSION['branch'] ?? '';

// Handle Assign Delivery
if(isset($_POST['assign_delivery'])){
    $order_id = $_POST['order_id'] ?? '';
    $delivery_person_id = $_POST['delivery_person_id'] ?? '';
    $schedule_date = $_POST['schedule_date'] ?? '';

    if(empty($order_id) || empty($delivery_person_id) || empty($schedule_date)){
        $error = "All fields are required!";
    } else {
        // ✅ Check if delivery already exists for this order
        $check = $conn->prepare("SELECT * FROM delivery WHERE order_id=?");
        $check->bind_param("i", $order_id);
        $check->execute();
        $result_check = $check->get_result();

        if($result_check->num_rows === 0){
            // Insert delivery only if not already assigned
            $stmt = $conn->prepare("
                INSERT INTO delivery (order_id, delivery_person_id, schedule_date, status)
                VALUES (?, ?, ?, 'Pending')
            ");
            if(!$stmt){
                die("Prepare failed (insert delivery): " . $conn->error);
            }
            $stmt->bind_param("iis", $order_id, $delivery_person_id, $schedule_date);
            if($stmt->execute()){
                $success = "Order assigned successfully!";
            } else {
                $error = "Failed to assign order! " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "This order already has a delivery assigned.";
        }
        $check->close();
    }
}

// Get Pending Orders
if($role == 'RDC_Staff'){
    $stmt_orders = $conn->prepare("
        SELECT order_id 
        FROM orders 
        WHERE status='Paid' AND branch=? 
          AND order_id NOT IN (SELECT order_id FROM delivery)
        ORDER BY order_id DESC
    ");
    if(!$stmt_orders) die("Prepare failed (orders): " . $conn->error);
    $stmt_orders->bind_param("s", $branch);
} else { // HO Admin
    $stmt_orders = $conn->prepare("
        SELECT order_id 
        FROM orders 
        WHERE status='Paid' 
          AND order_id NOT IN (SELECT order_id FROM delivery)
        ORDER BY order_id DESC
    ");
    if(!$stmt_orders) die("Prepare failed (orders): " . $conn->error);
}
$stmt_orders->execute();
$orders = $stmt_orders->get_result();
$stmt_orders->close();

// Get Active Delivery Persons
if($role == 'RDC_Staff'){
    $stmt_dp = $conn->prepare("SELECT delivery_person_id, customer_name FROM delivery_person WHERE status='Active' AND branch=?");
    if(!$stmt_dp) die("Prepare failed (delivery persons): " . $conn->error);
    $stmt_dp->bind_param("s", $branch);
} else {
    $stmt_dp = $conn->prepare("SELECT delivery_person_id, customer_name FROM delivery_person WHERE status='Active'");
    if(!$stmt_dp) die("Prepare failed (delivery persons): " . $conn->error);
}
$stmt_dp->execute();
$delivery_persons = $stmt_dp->get_result();
$stmt_dp->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Delivery</title>
    <style>
        body { font-family: Arial; background: #94bda5; padding: 20px; }
        .container { max-width: 550px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        label { font-weight: bold; display: block; margin-top: 10px; }
        select, input[type="date"] { width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
        button { width: 100%; padding: 10px; margin-top: 20px; background: #4CAF50; color: #fff; border: none; font-weight: bold; border-radius: 5px; cursor: pointer; }
        button:hover { background: #45a049; }
        .success { color: green; text-align: center; margin-bottom: 10px; }
        .error { color: red; text-align: center; margin-bottom: 10px; }
        .back { text-align: center; margin-top: 15px; }
        .back a { text-decoration: none; color: #4CAF50; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>Assign Order to Delivery Person</h2>

    <?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="post">

        <label>Order ID</label>
        <select name="order_id" required>
            <option value="">-- Select Pending Order --</option>
            <?php if($orders->num_rows > 0): ?>
                <?php while($o = $orders->fetch_assoc()): ?>
                    <option value="<?php echo $o['order_id']; ?>">Order #<?php echo $o['order_id']; ?></option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">No pending orders</option>
            <?php endif; ?>
        </select>

        <label>Delivery Person</label>
        <select name="delivery_person_id" required>
            <option value="">-- Select Delivery Person --</option>
            <?php if($delivery_persons->num_rows > 0): ?>
                <?php while($d = $delivery_persons->fetch_assoc()): ?>
                    <option value="<?php echo $d['delivery_person_id']; ?>">
                        <?php echo htmlspecialchars($d['customer_name']); ?> (ID <?php echo $d['delivery_person_id']; ?>)
                    </option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">No active delivery person</option>
            <?php endif; ?>
        </select>

        <label>Schedule Date</label>
        <input type="date" name="schedule_date" required>

        <button type="submit" name="assign_delivery">Assign Delivery</button>
    </form>

    <div class="back">
    <a href="dashboard.php">← Back to Dashboard</a>
</div>

</div>

</body>
</html>
