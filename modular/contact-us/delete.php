<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid message ID"
    ]);
    exit;
}

try {
    $sql = "DELETE FROM contact_us WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Message deleted successfully"
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Message not found"
            ]);
        }
    } else {
        throw new Exception("Failed to delete message");
    }

} catch (Exception $e) {
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