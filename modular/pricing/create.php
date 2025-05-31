<?php
include "../../config/connection.php";
include "../../config/header.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['plan_title']) || !isset($data['plan_description']) || !isset($data['price']) || !isset($data['billing_period'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields"
    ]);
    exit;
}

$conn->begin_transaction();

try {
    // Insert pricing plan
    $sql = "INSERT INTO pricing_plans (plan_title, plan_description, price, billing_period) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssds", 
        $data['plan_title'],
        $data['plan_description'],
        $data['price'],
        $data['billing_period']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create pricing plan");
    }
    
    $conn->commit();
    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Pricing plan created successfully",
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>