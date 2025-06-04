<?php
include "../../config/connection.php";
include "../../config/header.php";

$data = json_decode(file_get_contents("php://input"), true);

// Check required fields
if (!isset($data['name'], $data['email'], $data['message'], $data['subject'], $data['phone_number'], $data['recaptchaToken'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Name, email, message, subject, phone number and reCAPTCHA token are required"
    ]);
    exit;
}

// Your secret key (get this from Google reCAPTCHA admin console)
$recaptchaSecret = "YOUR_SECRET_KEY";

// Verify reCAPTCHA token by sending request to Google API
$recaptchaResponse = $data['recaptchaToken'];
$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

$verifyResponse = file_get_contents($verifyUrl . '?secret=' . urlencode($recaptchaSecret) . '&response=' . urlencode($recaptchaResponse));
$responseData = json_decode($verifyResponse, true);

if (!$responseData['success']) {
    // reCAPTCHA failed
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "reCAPTCHA verification failed. Please try again."
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
        $data['phone_number'],
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
