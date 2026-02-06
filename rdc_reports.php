<?php
session_start();
include 'db.php'; 

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'RDC_Staff'){
    header("Location: login.php");
    exit();
}

$staff_branch = $_SESSION['branch'] ?? '';
$filter_branch = $_GET['branch_filter'] ?? $staff_branch;
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

$branches = [];
$branch_result = $conn->query("SELECT DISTINCT customer_branch FROM customers ORDER BY customer_branch");
while($b = $branch_result->fetch_assoc()){
    $branches[] = $b['customer_branch'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>RDC Orders Report</title>
    <style>
        body { font-family: Arial; background:#eef5ff; padding:20px; }
        h2 { text-align:center; margin-bottom:20px; }
        select, button, input[type=date] { padding:8px 12px; margin:5px 5px 10px 0; border-radius:6px; border:1px solid #ccc; }
        table { width:100%; border-collapse: collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 5px 10px rgba(0,0,0,0.1); }
        th, td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
        th { background:#4CAF50; color:#fff; position:sticky; top:0; z-index:1; }
        tr:hover { background:#d9f2d9; }
        .empty { text-align:center; color:#777; padding:20px; }
        .filter-form { text-align:center; margin-bottom:20px; }
    </style>
</head>
<body>

<h2>RDC Orders Report - Branch: <?php echo htmlspecialchars($filter_branch); ?></h2>

<div class="filter-form">
    <form method="GET" action="">
        <label>Branch:</label>
        <select name="branch_filter">
            <option value="All" <?php if($filter_branch=="All") echo "selected"; ?>>All Branches</option>
            <?php foreach($branches as $b): ?>
                <option value="<?php echo htmlspecialchars($b); ?>" <?php if($filter_branch==$b) echo "selected"; ?>><?php echo htmlspecialchars($b); ?></option>
            <?php endforeach; ?>
        </select>

        <label>From:</label>
        <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">

        <label>To:</label>
        <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">

        <label>Status:</label>
        <select name="status_filter">
            <option value="">All</option>
            <option value="Placed" <?php if($status_filter=="Placed") echo "selected"; ?>>Placed</option>
            <option value="Delivered" <?php if($status_filter=="Delivered") echo "selected"; ?>>Delivered</option>
            <option value="Cancelled" <?php if($status_filter=="Cancelled") echo "selected"; ?>>Cancelled</option>
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
$conditions = [];

if($filter_branch != "All"){
    $conditions[] = "c.customer_branch = ?";
    $types .= "s";
    $params[] = $filter_branch;
}

if(!empty($from_date)){
    $conditions[] = "o.order_date >= ?";
    $types .= "s";
    $params[] = $from_date;
}
if(!empty($to_date)){
    $conditions[] = "o.order_date <= ?";
    $types .= "s";
    $params[] = $to_date;
}

if(!empty($status_filter)){
    $conditions[] = "o.status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

if(count($conditions) > 0){
    $sql .= " WHERE " . implode(" AND ", $conditions);
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
$conn->close();
?>
</body>
</html>
