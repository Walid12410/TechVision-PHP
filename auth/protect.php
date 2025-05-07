<?php
include "jwt.php";

// Check if token exists in cookie
if (!isset($_COOKIE['auth_token'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized: No token provided"]);
    exit;
}

$token = $_COOKIE['auth_token'];
$decoded = validateJWT($token, "your_super_secret_key");

if (!$decoded) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid or expired token"]);
    exit;
}

// Check if the user is an admin
if ($decoded['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Access denied: Admins only"]);
    exit;
}

// You can access the user's details now
// $decoded['id'], $decoded['role'], etc.
?>
