<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid project ID"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

try {
    // Check if project exists
    $checkSql = "SELECT id FROM projects WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Project not found");
    }

    // Update project fields
    $sql = "UPDATE projects SET 
            project_name = ?,
            project_description = ?,
            project_cost = ?,
            start_date = ?,
            end_date = ?,
            client_id = ?,
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdssiis", 
        $data['project_name'],
        $data['project_description'],
        $data['project_cost'],
        $data['start_date'],
        $data['end_date'],
        $data['client_id'],
        $id
    );
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Project updated successfully"
        ]);
    } else {
        throw new Exception("Failed to update project");
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