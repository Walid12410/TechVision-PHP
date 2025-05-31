<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid service ID"]);
    exit;
}

// Get service with its details using JOIN
$sql = "SELECT s.*, sd.id as detail_id, sd.detail_description 
        FROM services s 
        LEFT JOIN service_details sd ON s.id = sd.service_id 
        WHERE s.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Service not found"]);
    exit;
}

$service = null;
$details = [];

while($row = $result->fetch_assoc()) {
    if ($service === null) {
        $service = [
            "id" => $row['id'],
            "service_name" => $row['service_name'],
            "service_description" => $row['service_description'],
            "image_url" => $row['image_url']
        ];
    }
    
    if ($row['detail_id'] !== null) {
        $details[] = [
            "id" => $row['detail_id'],
            "detail_description" => $row['detail_description']
        ];
    }
}

$service['details'] = $details;

echo json_encode($service);
http_response_code(200);

$stmt->close();
$conn->close();
?>