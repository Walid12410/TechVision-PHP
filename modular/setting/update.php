<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


// Check if settings exist
$checkSql = "SELECT id FROM settings LIMIT 1";
$result = $conn->query($checkSql);

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "No settings found to update"]);
    exit;
}

$row = $result->fetch_assoc();
$settingId = $row['id'];

// Get PUT data
$data = json_decode(file_get_contents("php://input"), true);

$sql = "UPDATE settings SET 
        email = ?, 
        facebook_url = ?, 
        instagram_url = ?, 
        linkedin_url = ?, 
        tiktok_url = ?, 
        whatsapp_url = ?, 
        phone_number = ? 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssi", 
    $data['email'],
    $data['facebook_url'],
    $data['instagram_url'],
    $data['linkedin_url'],
    $data['tiktok_url'],
    $data['whatsapp_url'],
    $data['phone_number'],
    $settingId
);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["message" => "Settings updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update settings"]);
}

$stmt->close();
$conn->close();
?>