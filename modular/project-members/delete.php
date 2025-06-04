<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


try {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id === 0) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid project member ID"]);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // Check if project member exists
    $checkSql = "SELECT id FROM project_members WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        throw new Exception("Project member not found");
    }

    // Delete project member
    $sql = "DELETE FROM project_members WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $conn->commit();
        http_response_code(200);
        echo json_encode(["message" => "Project member removed successfully"]);
    } else {
        throw new Exception("Failed to remove project member");
    }

} catch (Exception $e) {
    if ($conn->connect_errno) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($checkStmt)) $checkStmt->close();
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>