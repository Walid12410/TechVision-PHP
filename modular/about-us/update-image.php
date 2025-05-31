<?php
include "../../config/connection.php";
include "../../config/header.php";

$uploadDir = "../../images/about/";

try {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("No image file uploaded or invalid file");
    }

    // Get current record
    $currentRecord = $conn->query("SELECT id, image_url FROM about_us LIMIT 1")->fetch_assoc();
    if (!$currentRecord) {
        throw new Exception("About us record not found");
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
    $imageUrl = 'images/about/' . $fileName;

    if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
        throw new Exception("Failed to upload image");
    }

    // Delete old image if exists
    if ($currentRecord['image_url'] && file_exists('../../' . $currentRecord['image_url'])) {
        unlink('../../' . $currentRecord['image_url']);
    }

    // Update image URL
    $sql = "UPDATE about_us SET image_url = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $imageUrl, $currentRecord['id']);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "About us image updated successfully",
            "image_url" => $imageUrl
        ]);
    } else {
        throw new Exception("Failed to update image");
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
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>