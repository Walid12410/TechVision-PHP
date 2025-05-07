<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid detail ID"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['detail_description'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing detail description"]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if detail exists
    $checkSql = "SELECT id FROM service_details WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Service detail not found");
    }

    // Update service detail
    $sql = "UPDATE service_details SET detail_description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $data['detail_description'], $id);
    $stmt->execute();

    $conn->commit();
    http_response_code(200);
    echo json_encode(["message" => "Service detail updated successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

if (isset($checkStmt)) $checkStmt->close();
if (isset($stmt)) $stmt->close();
$conn->close();
?>