<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid plan ID"
    ]);
    exit;
}

$conn->begin_transaction();

try {
    // Delete details first (foreign key constraint)
    $conn->query("DELETE FROM pricing_details WHERE pricing_id = $id");
    
    // Delete pricing plan
    $sql = "DELETE FROM pricing_plans WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $conn->commit();
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Pricing plan deleted successfully"
            ]);
        } else {
            throw new Exception("Pricing plan not found");
        }
    } else {
        throw new Exception("Failed to delete pricing plan");
    }

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