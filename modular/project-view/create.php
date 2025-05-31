<?php
include "../../config/connection.php";
include "../../config/header.php";

$uploadDir = "../../images/project-views/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    $data = $_POST;
    $image = $_FILES['image'] ?? null;
    $imageUrl = null;

    // Handle image upload
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

        $imageUrl = 'images/project-views/' . $fileName;
    }

    $currentDateTime = date('Y-m-d H:i:s');

    $sql = "INSERT INTO project_views (view_title, view_description, image_url, create_time, view_link) 
            VALUES (?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", 
        $data['view_title'],
        $data['view_description'],
        $imageUrl,
        $currentDateTime,
        $data['view_link']
    );

    if ($stmt->execute()) {
        $view_id = $conn->insert_id;
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Project view created successfully",
            "id" => $view_id,
            "create_time" => $currentDateTime
        ]);
    } else {
        throw new Exception("Failed to create project view");
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