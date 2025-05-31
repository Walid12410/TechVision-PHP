<?php
include "../../config/connection.php";
include "../../config/header.php";

$uploadDir = "../../images/service/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$data = $_POST;
$image = $_FILES['image'] ?? null;
$imageUrl = null;

$conn->begin_transaction();

try {
    // Handle image upload if present
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
        }

        $fileName = uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload image");
        }

        $imageUrl = 'images/service/' . $fileName;
    }

    // Insert service
    $sql = "INSERT INTO services (service_name, service_description, image_url) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $data['service_name'], $data['service_description'], $imageUrl);
    $stmt->execute();

    $conn->commit();
    http_response_code(201);
    echo json_encode([
        "message" => "Service created successfully"
    ]);

} catch (Exception $e) {
    $conn->rollback();
    if ($imageUrl && file_exists($uploadDir . basename($imageUrl))) {
        unlink($uploadDir . basename($imageUrl));
    }
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>