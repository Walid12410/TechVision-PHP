<?php
include "../../config/connection.php";
include "../../config/header.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['service_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Service ID is required"]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if service exists
    $checkServiceSql = "SELECT id FROM services WHERE id = ?";
    $checkServiceStmt = $conn->prepare($checkServiceSql);
    $checkServiceStmt->bind_param("i", $data['service_id']);
    $checkServiceStmt->execute();
    if ($checkServiceStmt->get_result()->num_rows === 0) {
        throw new Exception("Service not found");
    }

    // Check number of existing features
    $countSql = "SELECT COUNT(*) as count FROM service_features WHERE service_id = ?";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("i", $data['service_id']);
    $countStmt->execute();
    $count = $countStmt->get_result()->fetch_assoc()['count'];

    if ($count >= 3) {
        throw new Exception("Maximum number of features (3) reached for this service");
    }

    // Insert feature
    $sql = "INSERT INTO service_features (service_id) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['service_id']);
    $stmt->execute();
    $feature_id = $conn->insert_id;

    $conn->commit();
    http_response_code(201);
    echo json_encode([
        "message" => "Service feature created successfully",
        "id" => $feature_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?>