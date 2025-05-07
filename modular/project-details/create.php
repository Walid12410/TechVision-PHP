<?php
include "../../config/connection.php";
include "../../config/header.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['project_id']) || !isset($data['detail_description'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try {
    // Check if project exists
    $checkSql = "SELECT id FROM projects WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $data['project_id']);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Project not found");
    }

    // Insert project detail
    $sql = "INSERT INTO project_details (project_id, detail_description) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", 
        $data['project_id'],
        $data['detail_description']
    );
    
    if ($stmt->execute()) {
        $detail_id = $conn->insert_id;
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Project detail created successfully",
            "id" => $detail_id
        ]);
    } else {
        throw new Exception("Failed to create project detail");
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