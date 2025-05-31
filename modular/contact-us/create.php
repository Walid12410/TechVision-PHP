<?php
include "../../config/connection.php";
include "../../config/header.php";

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['name']) || !isset($data['email']) || !isset($data['message'])
    || !isset($data['subject']) || !isset($data['phone_number'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Name, email and message are required"
    ]);
    exit;
}

try {
    $sql = "INSERT INTO contact_us (name, email, phone_number, subject, message) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", 
        $data['name'],
        $data['email'],
        $data['phone_number'] ,
        $data['subject'],
        $data['message']
    );

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Message sent successfully",
            "id" => $conn->insert_id
        ]);
    } else {
        throw new Exception("Failed to send message");
    }

} catch (Exception $e) {
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