<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid service ID"]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete service details first
    $conn->query("DELETE FROM service_details WHERE service_id = $id");
    
    // Delete service
    $sql = "DELETE FROM services WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception("Service not found");
    }

    $conn->commit();
    http_response_code(200);
    echo json_encode(["message" => "Service deleted successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(404);
    echo json_encode(["error" => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>