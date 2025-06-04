<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uploadDir = "../../images/service/";

// Create directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid service ID"]);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["error" => "No image file uploaded or invalid file"]);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get current service data
    $currentSql = "SELECT image_url FROM services WHERE id = ?";
    $currentStmt = $conn->prepare($currentSql);
    $currentStmt->bind_param("i", $id);
    $currentStmt->execute();
    $result = $currentStmt->get_result();
    $currentService = $result->fetch_assoc();

    if (!$currentService) {
        throw new Exception("Service not found");
    }

    // Handle new image upload
    $image = $_FILES['image'];
    $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
    }

    // Generate unique filename
    $fileName = uniqid() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;
    $imageUrl = 'images/service/' . $fileName;

    if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
        throw new Exception("Failed to upload image");
    }

    // Delete old image if exists
    if ($currentService['image_url'] && file_exists('../../' . $currentService['image_url'])) {
        unlink('../../' . $currentService['image_url']);
    }

    // Update service image_url
    $sql = "UPDATE services SET image_url = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $imageUrl, $id);
    $stmt->execute();

    $conn->commit();
    http_response_code(200);
    echo json_encode([
        "message" => "Service image updated successfully",
        "image_url" => $imageUrl
    ]);

} catch (Exception $e) {
    $conn->rollback();
    // Delete newly uploaded image if exists
    if (isset($imageUrl) && file_exists($uploadDir . basename($imageUrl))) {
        unlink($uploadDir . basename($imageUrl));
    }
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($currentStmt)) $currentStmt->close();
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>