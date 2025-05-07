<?php
include "../config/connection.php";
include "jwt.php";

$data = json_decode(file_get_contents("php://input"), true);

// Validate credentials (assuming 'email' and 'password' exist in the database)
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing email or password"]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

// Query database to find user
$sql = "SELECT id, password, role FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid email or password"]);
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid email or password"]);
    exit;
}

// Generate JWT token
$jwt = generateJWT($user['id'], $user['role']);

// Set token in cookie
setcookie("auth_token", $jwt, time() + 3600, "/", "", true, true);  // Expire in 1 hour

http_response_code(200);
echo json_encode(["message" => "Login successful"]);
