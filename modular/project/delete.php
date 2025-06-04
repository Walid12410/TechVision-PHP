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

$conn->begin_transaction();

try {
    // Get current image URL
    $imageSql = "SELECT image_url FROM projects WHERE id = ?";
    $imageStmt = $conn->prepare($imageSql);
    $imageStmt->bind_param("i", $id);
    $imageStmt->execute();
    $result = $imageStmt->get_result();
    $project = $result->fetch_assoc();

    if (!$project) {
        throw new Exception("Project not found");
    }

    // Delete project members first
    $conn->query("DELETE FROM project_members WHERE project_id = $id");

    // Delete project details
    $conn->query("DELETE FROM project_details WHERE project_id = $id");
    
    // Delete project
    $sql = "DELETE FROM projects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Delete project image if exists
    if ($project['image_url'] && file_exists('../../' . $project['image_url'])) {
        unlink('../../' . $project['image_url']);
    }

    $conn->commit();
    http_response_code(200);
    echo json_encode(["message" => "Project deleted successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($imageStmt)) $imageStmt->close();
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>