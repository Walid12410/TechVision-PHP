<?php
include "../../config/connection.php";
include "../../config/header.php";

$uploadDir = "../../images/projects/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$conn->begin_transaction();

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

        $imageUrl = 'images/projects/' . $fileName;
    }

    // Insert project
    $sql = "INSERT INTO projects (client_id, project_name, project_description, 
            image_url, project_cost, start_date, end_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssdss", 
        $data['client_id'],
        $data['project_name'],
        $data['project_description'],
        $imageUrl,
        $data['project_cost'],
        $data['start_date'],
        $data['end_date']
    );
    $stmt->execute();
    $project_id = $conn->insert_id;

    // Insert project details if provided
    if (isset($data['details']) && !empty($data['details'])) {
        $details = json_decode($data['details'], true);
        if (is_array($details)) {
            $detailsSql = "INSERT INTO project_details (project_id, detail_description) VALUES (?, ?)";
            $detailsStmt = $conn->prepare($detailsSql);
            
            foreach ($details as $detail) {
                $detailsStmt->bind_param("is", $project_id, $detail['detail_description']);
                $detailsStmt->execute();
            }
            $detailsStmt->close();
        }
    }

    $conn->commit();
    http_response_code(201);
    echo json_encode([
        "message" => "Project created successfully",
        "id" => $project_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    if (isset($imageUrl) && file_exists($uploadDir . basename($imageUrl))) {
        unlink($uploadDir . basename($imageUrl));
    }
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>