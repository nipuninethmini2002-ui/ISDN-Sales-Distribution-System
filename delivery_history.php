<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

$search_order  = $_GET['search_order'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

$sql = "
SELECT 
    d.delivery_id,
    d.order_id,
    d.status,
    d.schedule_date,
    d.delivery_person_id,
    o.customer_id
FROM delivery d
INNER JOIN orders o ON d.order_id = o.order_id
WHERE 1=1
";

$params = [];
$types  = "";


if($role === 'Delivery_Person'){

    $map = $conn->prepare("
        SELECT delivery_person_id 
        FROM delivery_person 
        WHERE user_id = ?
    ");
    $map->bind_param("i", $user_id);
    $map->execute();
    $map_result = $map->get_result();

    if($map_result->num_rows === 0){
        die("No delivery person account linked to this user.");
    }

    $delivery_person_id = $map_result->fetch_assoc()['delivery_person_id'];
    $map->close();

    $sql .= " AND d.delivery_person_id = ?";
    $params[] = $delivery_person_id;
    $types   .= "i";
}

elseif($role === 'Customer'){
    $sql .= " AND o.customer_id = ?";
    $params[] = $user_id;
    $types   .= "i";
}

if(!empty($search_order)){
    $sql .= " AND d.order_id LIKE ?";
    $params[] = "%$search_order%";
    $types   .= "s";
}

if(!empty($filter_status)){
    $sql .= " AND d.status LIKE ?";
    $params[] = "%$filter_status%";
    $types   .= "s";
}

$sql .= " ORDER BY d.schedule_date DESC";

$stmt = $conn->prepare($sql);
if(!$stmt){
    die("SQL Error: " . $conn->error);
}

if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delivery History</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body{
            font-family:'Roboto',sans-serif;
            background:linear-gradient(to right,#e0f7fa,#ffffff);
            margin:0;
        }
        .header,.footer{
            background:#4CAF50;
            color:#fff;
            text-align:center;
            padding:18px;
        }
        .container{
            max-width:1100px;
            margin:30px auto;
            background:#fff;
            padding:20px;
            border-radius:15px;
            box-shadow:0 10px 25px rgba(0,0,0,.2);
        }
        h2{
            text-align:center;
            color:#4CAF50;
        }
        form{
            display:flex;
            gap:12px;
            justify-content:center;
            margin-bottom:20px;
            flex-wrap:wrap;
            background:#e0f2f1;
            padding:15px;
            border-radius:10px;
        }
        input,select,button{
            padding:8px 14px;
            border-radius:6px;
            border:1px solid #ccc;
        }
        button{
            background:#4CAF50;
            color:#fff;
            border:none;
            cursor:pointer;
        }
        table{
            width:100%;
            border-collapse:collapse;
            min-width:800px;
        }
        th,td{
            border:1px solid #ccc;
            padding:12px;
            text-align:center;
        }
        th{
            background:#4CAF50;
            color:#fff;
        }
        tr:nth-child(even){background:#f2f2f2;}
        tr:hover{background:#b2dfdb;}
    </style>
</head>
<body>

<div class="header">
    <h1>Delivery History</h1>
</div>

<div class="container">
<h2>Delivery Records</h2>

<form method="GET">
    <input type="text" name="search_order" placeholder="Order ID"
           value="<?= htmlspecialchars($search_order) ?>">

    <select name="filter_status">
        <option value="">All Status</option>
        <option value="Pending" <?= $filter_status=="Pending"?'selected':'' ?>>Pending</option>
        <option value="On" <?= $filter_status=="On"?'selected':'' ?>>On the way</option>
        <option value="Delivered" <?= $filter_status=="Delivered"?'selected':'' ?>>Delivered</option>
    </select>

    <button type="submit">Search / Filter</button>
</form>

<div style="overflow-x:auto">
<table>
<tr>
    <th>Delivery ID</th>
    <th>Order ID</th>
    <th>Customer ID</th>
    <th>Delivery Person ID</th>
    <th>Status</th>
    <th>Schedule Date</th>
</tr>

<?php
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<tr>
            <td>{$row['delivery_id']}</td>
            <td>{$row['order_id']}</td>
            <td>{$row['customer_id']}</td>
            <td>{$row['delivery_person_id']}</td>
            <td>{$row['status']}</td>
            <td>{$row['schedule_date']}</td>
        </tr>";
    }
}else{
    echo "<tr><td colspan='6'>No delivery history found</td></tr>";
}
?>
</table>
</div>
</div>

<div class="footer">
    © 2026 ISDN Sales Distribution System
</div>

</body>
</html>
