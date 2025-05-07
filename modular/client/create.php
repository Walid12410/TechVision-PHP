<?php
include "../../config/connection.php";
include "../../config/header.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['first_name']) || !isset($data['last_name']) || !isset($data['country_of_origin'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try {
    $sql = "INSERT INTO clients (first_name, last_name, country_of_origin) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", 
        $data['first_name'], 
        $data['last_name'], 
        $data['country_of_origin']
    );
    
    if ($stmt->execute()) {
        $client_id = $conn->insert_id;
        http_response_code(201);
        echo json_encode([
            "message" => "Client created successfully",
            "id" => $client_id
        ]);
    } else {
        throw new Exception("Failed to create client");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>