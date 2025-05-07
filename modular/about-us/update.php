<?php
include "../../config/connection.php";
include "../../config/header.php";

try {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate required fields
    $requiredFields = ['title', 'subtitle', 'main_description', 'additional_description'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: " . $field);
        }
    }

    // Get current record
    $currentRecord = $conn->query("SELECT id FROM about_us LIMIT 1")->fetch_assoc();
    if (!$currentRecord) {
        throw new Exception("About us record not found");
    }

    // Update fields
    $sql = "UPDATE about_us SET 
            title = ?,
            subtitle = ?,
            main_description = ?,
            additional_description = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", 
        $data['title'],
        $data['subtitle'],
        $data['main_description'],
        $data['additional_description'],
        $currentRecord['id']
    );
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "About us content updated successfully"
        ]);
    } else {
        throw new Exception("Failed to update about us content");
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