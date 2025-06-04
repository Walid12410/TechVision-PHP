<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['pricing_id']) || !isset($data['pricing_detail'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields"
    ]);
    exit;
}

try {
    // Check if pricing plan exists
    $checkSql = "SELECT id FROM pricing_plans WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $data['pricing_id']);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Pricing plan not found");
    }

    // Insert pricing detail
    $sql = "INSERT INTO pricing_details (pricing_id, pricing_detail) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", 
        $data['pricing_id'],
        $data['pricing_detail']
    );
    
    if ($stmt->execute()) {
        $detail_id = $conn->insert_id;
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Pricing detail created successfully",
            "id" => $detail_id
        ]);
    } else {
        throw new Exception("Failed to create pricing detail");
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