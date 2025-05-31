<?php
require '../connection.php';
require '../header.php';
require 'vendor/autoload.php';

use Firebase\JWT\JWT;


$key = "your_secret_key"; // change this to something secure
$issuedAt = time();
$expire = $issuedAt + (60 * 60 * 24); // 1 day

$data = json_decode(file_get_contents("php://input"));
$email = $data->email;
$password = $data->password;

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expire,
        'data' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ];
    
    $jwt = JWT::encode($payload, $key, 'HS256');

    setcookie("token", $jwt, $expire, "/", "", false, true); // httpOnly cookie

    echo json_encode(["message" => "Login successful"]);
} else {
    echo json_encode(["error" => "Invalid credentials"]);
}

?>