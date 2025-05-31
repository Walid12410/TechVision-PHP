<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid detail ID"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['pricing_detail'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing pricing detail"
    ]);
    exit;
}

try {
    // Check if detail exists
    $checkSql = "SELECT id FROM pricing_details WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Pricing detail not found");
    }

    // Update detail
    $sql = "UPDATE pricing_details SET pricing_detail = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", 
        $data['pricing_detail'],
        $id
    );
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Pricing detail updated successfully"
        ]);
    } else {
        throw new Exception("Failed to update pricing detail");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($checkStmt)) $checkStmt->close();
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>