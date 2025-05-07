<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uploadDir = "../../images/projects/";

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid project ID"]);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["error" => "No image file uploaded or invalid file"]);
    exit;
}

$conn->begin_transaction();

try {
    // Get current project data
    $currentSql = "SELECT image_url FROM projects WHERE id = ?";
    $currentStmt = $conn->prepare($currentSql);
    $currentStmt->bind_param("i", $id);
    $currentStmt->execute();
    $result = $currentStmt->get_result();
    $project = $result->fetch_assoc();

    if (!$project) {
        throw new Exception("Project not found");
    }

    // Handle new image upload
    $image = $_FILES['image'];
    $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Invalid file type. Only JPG, JPEG, and PNG are allowed.");
    }

    // Generate unique filename
    $fileName = uniqid() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;
    $imageUrl = 'images/projects/' . $fileName;

    if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
        throw new Exception("Failed to upload image");
    }

    // Delete old image if exists
    if ($project['image_url'] && file_exists('../../' . $project['image_url'])) {
        unlink('../../' . $project['image_url']);
    }

    // Update project image_url
    $sql = "UPDATE projects SET image_url = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $imageUrl, $id);
    
    if ($stmt->execute()) {
        $conn->commit();
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Project image updated successfully",
            "image_url" => $imageUrl
        ]);
    } else {
        throw new Exception("Failed to update project image");
    }

} catch (Exception $e) {
    $conn->rollback();
    // Delete newly uploaded image if exists
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