<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid member ID"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

try {
    $sql = "UPDATE members SET 
            first_name = ?, 
            last_name = ?, 
            email = ?, 
            phone_number = ?,
            field_of_expertise = ? 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", 
        $data['first_name'], 
        $data['last_name'], 
        $data['email'], 
        $data['phone_number'],
        $data['field_of_expertise'],
        $id
    );
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "Member updated successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Member not found"]);
        }
    } else {
        throw new Exception("Failed to update member");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>