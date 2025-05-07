<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid view ID"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

try {
    $sql = "UPDATE project_views 
            SET view_title = ?, 
                view_description = ?, 
                view_link = ? 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", 
        $data['view_title'],
        $data['view_description'],
        $data['view_link'],
        $id
    );
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Project view updated successfully"
            ]);
        } else {
            throw new Exception("Project view not found");
        }
    } else {
        throw new Exception("Failed to update project view");
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