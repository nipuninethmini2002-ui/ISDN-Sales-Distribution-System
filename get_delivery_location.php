<?php
header('Content-Type: application/json');
include 'db.php';

if(!isset($_GET['order_id'])){
    echo json_encode([]);
    exit();
}

$order_id = intval($_GET['order_id']);

$sql = "SELECT current_lat, current_lng 
        FROM delivery 
        WHERE order_id = ? 
        ORDER BY last_update DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);

if($stmt === false){
    echo json_encode([
        "error" => $conn->error
    ]);
    exit();
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    echo json_encode([
        "latitude" => $row['current_lat'],
        "longitude" => $row['current_lng']
    ]);
} else {
    echo json_encode([]);
}

$stmt->close();
$conn->close();
?>
