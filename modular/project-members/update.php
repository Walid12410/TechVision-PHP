<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid project member ID"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

try {
    $sql = "UPDATE project_members SET 
            cost = ?,
            cost_received = ?,
            start_date = ?,
            end_date = ?
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddssi", 
        $data['cost'],
        $data['cost_received'],
        $data['start_date'],
        $data['end_date'],
        $id
    );
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "Project member updated successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Project member not found"]);
        }
    } else {
        throw new Exception("Failed to update project member");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>