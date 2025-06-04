<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$uploadDir = "../../images/about/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    // Check if record exists first
    $existingRecord = $conn->query("SELECT id FROM about_us LIMIT 1")->fetch_assoc();

    if ($existingRecord) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "About us record already exists. Please use update endpoint instead."
        ]);
        exit;
    }

    $data = $_POST;
    $image = $_FILES['image'] ?? null;
    $imageUrl = null;

    // Handle image upload if present
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Invalid file type. Only JPG, JPEG, and PNG are allowed.");
        }

        $fileName = uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload image");
        }

        $imageUrl = 'images/about/' . $fileName;
    }

    // Validate required fields
    $requiredFields = ['title', 'subtitle', 'main_description', 'additional_description'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: " . $field);
        }
    }

    // Insert new record
    $sql = "INSERT INTO about_us (title, subtitle, main_description, additional_description, image_url) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", 
        $data['title'],
        $data['subtitle'],
        $data['main_description'],
        $data['additional_description'],
        $imageUrl
    );

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "About us section created successfully",
            "image_url" => $imageUrl
        ]);
    } else {
        throw new Exception("Failed to create about us section");
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