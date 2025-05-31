<?php
include "../../config/connection.php";
include "../../config/header.php";

$data = json_decode(file_get_contents("php://input"), true);

// Required field check
$required = ['first_name', 'last_name', 'email', 'phone_number', 'country_of_origin'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing or empty field: $field"]);
        exit;
    }
}

// Email format validation
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email format"]);
    exit;
}

try {
    $sql = "INSERT INTO clients (first_name, last_name, email, phone_number, country_of_origin) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", 
        $data['first_name'], 
        $data['last_name'], 
        $data['email'], 
        $data['phone_number'], 
        $data['country_of_origin']
    );
    
    if ($stmt->execute()) {
        $client_id = $conn->insert_id;
        http_response_code(201);
        echo json_encode([
            "message" => "Client created successfully",
        ]);
    } else {
        throw new Exception("Failed to create client: " . $stmt->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
