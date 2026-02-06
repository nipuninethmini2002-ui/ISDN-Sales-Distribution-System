<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Delivery_Person') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['location_update_error'] = "Invalid request method";
    header("Location: dashboard.php");
    exit();
}

$delivery_person_id = $_SESSION['delivery_person_id'] ?? null;
$order_id           = $_POST['order_id'] ?? '';
$current_lat        = $_POST['current_lat'] ?? '';
$current_lng        = $_POST['current_lng'] ?? '';


$fallbackLat = 6.9271;  
$fallbackLng = 79.8612;

if ($current_lat === '' || $current_lng === '') {
    $current_lat = $fallbackLat;
    $current_lng = $fallbackLng;
}

if (!$delivery_person_id) {
    $_SESSION['location_update_error'] = "Session error. Please login again.";
    header("Location: dashboard.php");
    exit();
}

if (empty($order_id)) {
    $_SESSION['location_update_error'] = "Please select an order before updating GPS.";
    header("Location: dashboard.php");
    exit();
}

$check = $conn->prepare("
    SELECT delivery_id 
    FROM delivery 
    WHERE order_id = ?
      AND delivery_person_id = ?
      AND status = 'on the way'
");

$check->bind_param("ii", $order_id, $delivery_person_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $_SESSION['location_update_error'] =
        "GPS update allowed only for orders with status 'On the Way'.";
    header("Location: dashboard.php");
    exit();
}

$check->close();

$stmt = $conn->prepare("
    UPDATE delivery
    SET current_lat = ?, 
        current_lng = ?, 
        last_update = NOW()
    WHERE order_id = ?
      AND delivery_person_id = ?
");

$stmt->bind_param("ddii", $current_lat, $current_lng, $order_id, $delivery_person_id);

if ($stmt->execute()) {
    $_SESSION['location_update_success'] =
        "GPS location updated successfully for Order #$order_id";
    header("Location: dashboard.php");
    exit();
} else {
    $_SESSION['location_update_error'] = "Failed to update GPS location.";
    header("Location: dashboard.php");
    exit();
}

$stmt->close();
$conn->close();
?>
