<?php
include "../../config/connection.php";
include "../../config/header.php";

try {
    $sql = "DELETE FROM contact_us";
    
    if ($conn->query($sql)) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "All messages deleted successfully"
        ]);
    } else {
        throw new Exception("Failed to delete messages");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}

?>