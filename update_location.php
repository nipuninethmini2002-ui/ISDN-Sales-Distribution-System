<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Delivery_Person' || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$delivery_user_id = $_SESSION['user_id'];

$stmt_dp = $conn->prepare("SELECT delivery_person_id FROM delivery_person WHERE user_id=?");
$stmt_dp->bind_param("i", $delivery_user_id);
$stmt_dp->execute();
$result_dp = $stmt_dp->get_result();
if ($result_dp->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Delivery person not found']);
    exit();
}
$dp = $result_dp->fetch_assoc();
$delivery_person_id = $dp['delivery_person_id'];

if (!isset($_POST['order_id'], $_POST['current_lat'], $_POST['current_lng'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit();
}

$order_id = intval($_POST['order_id']);
$lat = floatval($_POST['current_lat']);
$lng = floatval($_POST['current_lng']);

$stmt_check = $conn->prepare("SELECT delivery_id FROM delivery WHERE order_id=? AND delivery_person_id=?");
$stmt_check->bind_param("ii", $order_id, $delivery_person_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Delivery not assigned to you']);
    exit();
}

$delivery = $result_check->fetch_assoc();
$delivery_id = $delivery['delivery_id'];

$stmt_update = $conn->prepare("UPDATE delivery SET current_lat=?, current_lng=?, last_update=NOW() WHERE delivery_id=?");
$stmt_update->bind_param("ddi", $lat, $lng, $delivery_id);

if ($stmt_update->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Location updated']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'DB error']);
}

$stmt_dp->close();
$stmt_check->close();
$stmt_update->close();
$conn->close();
