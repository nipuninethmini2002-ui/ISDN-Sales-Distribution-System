<?php
include 'db.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['delivery_id'], $data['latitude'], $data['longitude'])){
    $stmt = $conn->prepare(
        "UPDATE delivery 
         SET current_lat = ?, current_lng = ?, last_update = NOW() 
         WHERE delivery_id = ?"
    );
    $stmt->bind_param("ddi", $data['latitude'], $data['longitude'], $data['delivery_id']);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
}

$conn->close();
?>
