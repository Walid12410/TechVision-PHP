<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid feature ID"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['feature_text'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing feature text"]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if feature exists
    $checkSql = "SELECT id FROM service_features WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Service feature not found");
    }

    // Update feature text
    $sql = "UPDATE service_features SET feature_text = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $data['feature_text'], $id);
    $stmt->execute();

    $conn->commit();
    http_response_code(200);
    echo json_encode(["message" => "Service feature updated successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

if (isset($checkStmt)) $checkStmt->close();
if (isset($stmt)) $stmt->close();
$conn->close();
?>