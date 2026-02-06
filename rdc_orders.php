<?php
session_start();
include 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'RDC_Staff'){
    header("Location: login.php");
    exit();
}

$staff_branch = $_SESSION['branch'] ?? '';

$filter_branch = $_GET['branch_filter'] ?? 'All';
?>
<!DOCTYPE html>
<html>
<head>
    <title>RDC Orders</title>
    <style>
        body { font-family: Arial; background:#eef5ff; padding:20px; }
        h2 { text-align:center; margin-bottom:20px; }
        select, button { padding:8px 12px; margin:10px 0; border-radius:6px; border:1px solid #ccc; }
        table { width:100%; border-collapse: collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 5px 10px rgba(0,0,0,0.1); }
        th, td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
        th { background:#4CAF50; color:#fff; }
        tr:hover { background:#d9f2d9; }
        .empty { text-align:center; color:#777; padding:20px; }
        .filter-form { text-align:center; margin-bottom:20px; }
    </style>
</head>
<body>

<h2>Orders - Branch: <?php echo htmlspecialchars($filter_branch); ?></h2>

<div class="filter-form">
    <form method="GET" action="">
        <label>Filter by Branch:</label>
        <select name="branch_filter">
            <option value="All" <?php if($filter_branch=="All") echo "selected"; ?>>All Branches</option>
            <option value="Colombo" <?php if($filter_branch=="Colombo") echo "selected"; ?>>Colombo</option>
            <option value="Kandy" <?php if($filter_branch=="Kandy") echo "selected"; ?>>Kandy</option>
            <option value="Galle" <?php if($filter_branch=="Galle") echo "selected"; ?>>Galle</option>
        </select>
        <button type="submit">Show Orders</button>
    </form>
</div>

<?php
$sql = "SELECT o.order_id, u.username, c.customer_branch AS branch, o.order_date, o.status,
               IFNULL((SELECT SUM(quantity) FROM order_items WHERE order_id=o.order_id),0) AS total_items
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        JOIN users u ON c.user_id = u.user_id";

$params = [];
$types = "";

if($filter_branch != "All"){
    $sql .= " WHERE c.customer_branch = ?";
    $types = "s";
    $params[] = $filter_branch;
}

$sql .= " ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
if(!$stmt) die("SQL Prepare failed: ".$conn->error);

if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    echo "<table>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total Items</th>
            </tr>";
    while($row = $result->fetch_assoc()){
        echo "<tr>
                <td>".htmlspecialchars($row['order_id'])."</td>
                <td>".htmlspecialchars($row['username'])."</td>
                <td>".htmlspecialchars($row['branch'])."</td>
                <td>".htmlspecialchars($row['order_date'])."</td>
                <td>".htmlspecialchars($row['status'])."</td>
                <td>".htmlspecialchars($row['total_items'])."</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<div class='empty'>No orders available for this selection</div>";
}

$stmt->close();
?>
</body>
</html>
