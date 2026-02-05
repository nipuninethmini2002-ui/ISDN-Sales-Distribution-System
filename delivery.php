<?php
session_start();
include 'db.php'; // Database connection

// Check if user logged in
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role']; // Admin, RDC_Staff, Customer, Delivery_Person

// Handle search/filter
$search_order = $_GET['search_order'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

// Base SQL
$sql = "SELECT d.delivery_id, d.order_id, d.status, d.schedule_date, d.delivery_person_id, o.customer_id
        FROM delivery d
        JOIN orders o ON d.order_id = o.order_id
        WHERE 1";

// Role-based access
$params = [];
$types = "";

if($role == 'Delivery_Person'){
    $sql .= " AND d.delivery_person_id = ?";
    $params[] = $user_id;
    $types .= "i";
} elseif($role == 'Customer'){
    $sql .= " AND o.customer_id = ?";
    $params[] = $user_id;
    $types .= "i";
} // Admin and RDC Staff see all deliveries

// Add search by order_id
if(!empty($search_order)){
    $sql .= " AND d.order_id LIKE ?";
    $params[] = "%$search_order%";
    $types .= "s";
}

// Add filter by status
if(!empty($filter_status)){
    $sql .= " AND d.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$sql .= " ORDER BY d.schedule_date DESC";

$stmt = $conn->prepare($sql);
if(!$stmt){
    die("SQL Prepare Error: " . $conn->error);
}

// Bind parameters dynamically
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
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right, #e0f7fa, #ffffff);
            min-height: 100vh;
            padding: 0;
            margin: 0;
        }
        .header {
            background: #4CAF50;
            color: #fff;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .header h1 {
            font-size: 28px;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            box-shadow: 0 12px 25px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 25px;
        }
        form {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
            background: #e8f5e9;
            padding: 15px;
            border-radius: 10px;
        }
        form input[type="text"], form select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 15px;
        }
        form button {
            background: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }
        form button:hover {
            background: #388E3C;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 15px;
        }
        th {
            background: #4CAF50;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        tr:nth-child(even) { background: #f1f8f1; }
        tr:hover { background: #c8e6c9; }
        .footer {
            text-align: center;
            padding: 15px;
            background: #4CAF50;
            color: #fff;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        @media (max-width: 600px){
            form {
                flex-direction: column;
            }
            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Delivery History</h1>
    </div>

    <div class="container">
        <!-- Search & Filter Form -->
        <form method="GET">
            <input type="text" name="search_order" placeholder="Search by Order ID" value="<?php echo htmlspecialchars($search_order); ?>">
            <select name="filter_status">
                <option value="">All Statuses</option>
                <option value="Pending" <?php if($filter_status=="Pending") echo "selected"; ?>>Pending</option>
                <option value="On the way" <?php if($filter_status=="On the way") echo "selected"; ?>>On the way</option>
                <option value="Delivered" <?php if($filter_status=="Delivered") echo "selected"; ?>>Delivered</option>
            </select>
            <button type="submit">Search / Filter</button>
        </form>

        <!-- Delivery Table -->
        <div class="table-container">
            <table>
                <tr>
                    <th>Delivery ID</th>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                    <th>Delivery Person ID</th>
                    <th>Status</th>
                    <th>Scheduled Date</th>
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
                } else {
                    echo "<tr><td colspan='6'>No delivery history found.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>

    <div class="footer">
        © 2026 ISDN System | Designed by N&R
    </div>
</body>
</html>
