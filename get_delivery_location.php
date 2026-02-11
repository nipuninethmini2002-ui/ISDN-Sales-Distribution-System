<?php
include 'db.php';

if (!isset($_GET['order_id'])) {
    echo json_encode(['status'=>'error','message'=>'Order ID missing']);
    exit();
}

$order_id = intval($_GET['order_id']);

$stmt = $conn->prepare("SELECT current_lat, current_lng, last_update FROM delivery WHERE order_id=? LIMIT 1");
if (!$stmt) {
    echo json_encode(['status'=>'error','message'=>'Prepare failed: '.$conn->error]);
    exit();
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status'=>'success',
        'current_lat'=>$row['current_lat'],
        'current_lng'=>$row['current_lng'],
        'last_update'=>$row['last_update']
    ]);
} else {
    echo json_encode([
        'status'=>'error',
        'message'=>'No delivery found for this order'
    ]);
}

$stmt->close();
$conn->close();
?>
