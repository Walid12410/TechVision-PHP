<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['first_name']) || !isset($data['last_name']) || 
    !isset($data['email']) || !isset($data['phone_number']) || 
    !isset($data['field_of_expertise'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try {
    $sql = "INSERT INTO members (first_name, last_name, email, phone_number, field_of_expertise) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", 
        $data['first_name'], 
        $data['last_name'], 
        $data['email'], 
        $data['phone_number'],
        $data['field_of_expertise']
    );
    
    if ($stmt->execute()) {
        $member_id = $conn->insert_id;
        http_response_code(201);
        echo json_encode([
            "message" => "Member created successfully",
        ]);
    } else {
        throw new Exception("Failed to create member");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>