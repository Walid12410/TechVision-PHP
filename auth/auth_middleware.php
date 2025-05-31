<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "your_secret_key";

function verifyToken($requireAdmin = false) {
    if (!isset($_COOKIE['token'])) {
        http_response_code(401);
        echo json_encode(['message' => 'Access denied. No token.']);
        exit;
    }

    $token = $_COOKIE['token'];
    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $user = $decoded->data;

        if ($requireAdmin && $user->role !== 'admin') {
            http_response_code(403);
            echo json_encode(['message' => 'Admin only']);
            exit;
        }

        return $user;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid token']);
        exit;
    }
}

?>