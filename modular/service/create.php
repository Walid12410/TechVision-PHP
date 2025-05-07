<?php
include "../../config/connection.php";
include "../../config/header.php";

$uploadDir = "../../images/";
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

        $imageUrl = 'images/' . $fileName;
    }

    // Insert service
    $sql = "INSERT INTO services (service_name, service_description, image_url) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $data['service_name'], $data['service_description'], $imageUrl);
    $stmt->execute();
    $service_id = $conn->insert_id;

    // Insert service details if provided
    if (isset($data['details']) && !empty($data['details'])) {
        $details = json_decode($data['details'], true);
        if (is_array($details)) {
            $detailsSql = "INSERT INTO service_details (service_id, detail_description) VALUES (?, ?)";
            $detailsStmt = $conn->prepare($detailsSql);
            
            foreach ($details as $detail) {
                $detailsStmt->bind_param("is", $service_id, $detail['detail_description']);
                $detailsStmt->execute();
            }
            $detailsStmt->close();
        }
    }

    $conn->commit();
    http_response_code(201);
    echo json_encode([
        "message" => "Service created successfully",
        "id" => $service_id,
        "image_url" => $imageUrl
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