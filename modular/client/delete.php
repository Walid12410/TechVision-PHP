<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid client ID"]);
    exit;
}

try {
    // Check for related projects
    $checkProjectsSql = "SELECT COUNT(*) as count FROM projects WHERE client_id = ?";
    $checkStmt = $conn->prepare($checkProjectsSql);
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $projectCount = $result->fetch_assoc()['count'];

    if ($projectCount > 0) {
        http_response_code(400);
        echo json_encode(["error" => "Cannot delete client with associated projects"]);
        exit;
    }

    $sql = "DELETE FROM clients WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "Client deleted successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Client not found"]);
        }
    } else {
        throw new Exception("Failed to delete client");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$checkStmt->close();
$stmt->close();
$conn->close();
?>