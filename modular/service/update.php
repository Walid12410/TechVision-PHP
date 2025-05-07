<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid service ID"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Start transaction
$conn->begin_transaction();

try {
    // Check if service exists
    $checkSql = "SELECT id FROM services WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Service not found");
    }

    // Update service fields
    $sql = "UPDATE services SET service_name = ?, service_description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $data['service_name'], $data['service_description'], $id);
    $stmt->execute();

    // Update service details if provided
    if (isset($data['details'])) {
        // Delete existing details
        $deleteDetailsSql = "DELETE FROM service_details WHERE service_id = ?";
        $deleteStmt = $conn->prepare($deleteDetailsSql);
        $deleteStmt->bind_param("i", $id);
        $deleteStmt->execute();

        // Insert new details
        if (!empty($data['details'])) {
            $detailsSql = "INSERT INTO service_details (service_id, detail_description) VALUES (?, ?)";
            $detailsStmt = $conn->prepare($detailsSql);
            
            foreach ($data['details'] as $detail) {
                $detailsStmt->bind_param("is", $id, $detail['detail_description']);
                $detailsStmt->execute();
            }
            $detailsStmt->close();
        }
        $deleteStmt->close();
    }

    $conn->commit();
    http_response_code(200);
    echo json_encode(["message" => "Service updated successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

if (isset($checkStmt)) $checkStmt->close();
if (isset($stmt)) $stmt->close();
$conn->close();
?>