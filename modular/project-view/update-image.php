<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uploadDir = "../../images/project-views/";

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid view ID"]);
    exit;
}

try {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("No image file uploaded or invalid file");
    }

    // Get current image URL
    $currentSql = "SELECT image_url FROM project_views WHERE id = ?";
    $currentStmt = $conn->prepare($currentSql);
    $currentStmt->bind_param("i", $id);
    $currentStmt->execute();
    $result = $currentStmt->get_result();
    $view = $result->fetch_assoc();

    if (!$view) {
        throw new Exception("Project view not found");
    }

    // Handle new image upload
    $image = $_FILES['image'];
    $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Invalid file type. Only JPG, JPEG, and PNG are allowed.");
    }

    $fileName = uniqid() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;
    $imageUrl = 'images/project-views/' . $fileName;

    if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
        throw new Exception("Failed to upload image");
    }

    // Delete old image if exists
    if ($view['image_url'] && file_exists('../../' . $view['image_url'])) {
        unlink('../../' . $view['image_url']);
    }

    // Update image URL
    $sql = "UPDATE project_views SET image_url = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $imageUrl, $id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Project view image updated successfully",
            "image_url" => $imageUrl
        ]);
    } else {
        throw new Exception("Failed to update project view image");
    }

} catch (Exception $e) {
    if (isset($imageUrl) && file_exists($uploadDir . basename($imageUrl))) {
        unlink($uploadDir . basename($imageUrl));
    }
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($currentStmt)) $currentStmt->close();
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>