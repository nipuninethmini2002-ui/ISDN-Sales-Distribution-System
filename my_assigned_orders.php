<?php
session_start();
include 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'Delivery_Person'){
    header("Location: login.php");
    exit();
}

$delivery_user_id = $_SESSION['user_id'];

$query = $conn->prepare("SELECT delivery_person_id FROM delivery_person WHERE user_id=?");
$query->bind_param("i", $delivery_user_id);
$query->execute();
$result = $query->get_result();
$dp = $result->fetch_assoc();
$delivery_person_id = $dp['delivery_person_id'];

if(isset($_POST['update_status'])){
    $delivery_id = $_POST['delivery_id'];
    $status = $_POST['status'];

    $stmt_check = $conn->prepare("SELECT status FROM delivery WHERE delivery_id=? AND delivery_person_id=?");
    $stmt_check->bind_param("ii", $delivery_id, $delivery_person_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if($result_check->num_rows > 0){
        $row = $result_check->fetch_assoc();
        if($row['status'] != 'Delivered'){ 
            $stmt_update = $conn->prepare("UPDATE delivery SET status=?, last_update=NOW() WHERE delivery_id=? AND delivery_person_id=?");
            $stmt_update->bind_param("sii", $status, $delivery_id, $delivery_person_id);
            $stmt_update->execute();
        }
    }
    header("Location: my_assigned_orders.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT d.delivery_id, d.order_id, d.status AS delivery_status, d.schedule_date, d.current_lat, d.current_lng, d.last_update,
           o.status AS order_status
    FROM delivery d
    JOIN orders o ON d.order_id = o.order_id
    WHERE d.delivery_person_id = ?
    ORDER BY d.schedule_date ASC
");
$stmt->bind_param("i", $delivery_person_id);
$stmt->execute();
$assigned_orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Assigned Orders</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #4CAF50; color: #fff; }
        tr:nth-child(even){background-color: #f9f9f9;}
        .btn { background: #4CAF50; color: #fff; padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 5px; }
        .btn:hover { background: #45a049; }
        .view-btn { background: #2196F3; }
        .view-btn:hover { background: #1976D2; }
    </style>
</head>
<body>

<h2>My Assigned Orders</h2>

<?php if($assigned_orders->num_rows == 0): ?>
    <p>No orders assigned yet.</p>
<?php else: ?>
<table>
    <tr>
        <th>Delivery ID</th>
        <th>Order ID</th>
        <th>Order Status</th>
        <th>Delivery Status</th>
        <th>Schedule Date</th>
        <th>Current Lat</th>
        <th>Current Lng</th>
        <th>Last Update</th>
        <th>Actions</th>
    </tr>
    <?php while($row = $assigned_orders->fetch_assoc()){ ?>
    <tr>
        <td><?= $row['delivery_id'] ?></td>
        <td><?= $row['order_id'] ?></td>
        <td><?= $row['order_status'] ?></td>
        <td><?= $row['delivery_status'] ?></td>
        <td><?= $row['schedule_date'] ?></td>
        <td><?= $row['current_lat'] ?></td>
        <td><?= $row['current_lng'] ?></td>
        <td><?= $row['last_update'] ?></td>
        <td>
            <form method="post" style="margin-bottom:5px;">
                <input type="hidden" name="delivery_id" value="<?= $row['delivery_id'] ?>">
                <select name="status">
                    <option value="Pending" <?= $row['delivery_status']=='Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="On the way" <?= $row['delivery_status']=='On the way' ? 'selected' : '' ?>>On the way</option>
                    <option value="Delivered" <?= $row['delivery_status']=='Delivered' ? 'selected' : '' ?>>Delivered</option>
                </select>
                <button type="submit" name="update_status" class="btn">Update</button>
            </form>

            <form action="delivery_person_order_details.php" method="get">
                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                <button type="submit" class="btn view-btn">Start Delivery / Track</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>
<?php endif; ?>

</body>
</html>
