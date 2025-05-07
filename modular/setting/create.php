<?php
include "../../config/connection.php";
include "../../config/header.php";

// Check if settings already exist
$checkSql = "SELECT COUNT(*) as count FROM settings";
$result = $conn->query($checkSql);
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    http_response_code(400);
    echo json_encode(["error" => "Settings already exist. Use update instead."]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

$sql = "INSERT INTO settings (email, facebook_url, instagram_url, linkedin_url, twitter_url, whatsapp_url, phone_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", 
    $data['email'],
    $data['facebook_url'],
    $data['instagram_url'],
    $data['linkedin_url'],
    $data['twitter_url'],
    $data['whatsapp_url'],
    $data['phone_number']
);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["message" => "Settings created successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to create settings"]);
}

$stmt->close();
$conn->close();
?>