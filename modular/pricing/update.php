<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid plan ID"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$conn->begin_transaction();

try {
    // Update pricing plan
    $sql = "UPDATE pricing_plans SET 
            plan_title = ?,
            plan_description = ?,
            price = ?,
            billing_period = ?
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsi", 
        $data['plan_title'],
        $data['plan_description'],
        $data['price'],
        $data['billing_period'],
        $id
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update pricing plan");
    }

    // Update details if provided
    if (isset($data['details'])) {
        // Delete existing details
        $conn->query("DELETE FROM pricing_details WHERE pricing_id = $id");
        
        // Insert new details
        if (!empty($data['details'])) {
            $detailsSql = "INSERT INTO pricing_details (pricing_id, pricing_detail) VALUES (?, ?)";
            $detailsStmt = $conn->prepare($detailsSql);
            
            foreach ($data['details'] as $detail) {
                $detailsStmt->bind_param("is", $id, $detail);
                if (!$detailsStmt->execute()) {
                    throw new Exception("Failed to update pricing details");
                }
            }
            $detailsStmt->close();
        }
    }

    $conn->commit();
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Pricing plan updated successfully"
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