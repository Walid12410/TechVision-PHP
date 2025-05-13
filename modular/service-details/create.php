<?php
include "../../config/connection.php";
include "../../config/header.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['service_id']) || !isset($data['detail_description'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if service exists
    $checkSql = "SELECT id FROM services WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $data['service_id']);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Service not found");
    }

    // Insert service detail
    $sql = "INSERT INTO service_details (service_id, detail_description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $data['service_id'], $data['detail_description']);
    $stmt->execute();
    $detail_id = $conn->insert_id;

    $conn->commit();
    http_response_code(201);
    echo json_encode([
        "message" => "Service detail created successfully",
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

if (isset($checkStmt)) $checkStmt->close();
if (isset($stmt)) $stmt->close();
$conn->close();
?>