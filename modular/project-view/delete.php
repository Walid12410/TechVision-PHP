<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid view ID"]);
    exit;
}

try {
    // Get image URL before deleting
    $imageSql = "SELECT image_url FROM project_views WHERE id = ?";
    $imageStmt = $conn->prepare($imageSql);
    $imageStmt->bind_param("i", $id);
    $imageStmt->execute();
    $result = $imageStmt->get_result();
    $view = $result->fetch_assoc();

    if (!$view) {
        throw new Exception("Project view not found");
    }

    // Delete record
    $sql = "DELETE FROM project_views WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete associated image if exists
        if ($view['image_url'] && file_exists('../../' . $view['image_url'])) {
            unlink('../../' . $view['image_url']);
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Project view deleted successfully"
        ]);
    } else {
        throw new Exception("Failed to delete project view");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($imageStmt)) $imageStmt->close();
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>