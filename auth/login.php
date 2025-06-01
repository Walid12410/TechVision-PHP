<?php
require '../config/connection.php';
require './jwt_helper.php';

$data = json_decode(file_get_contents('php://input'), true);

$email = $conn->real_escape_string($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password required']);
    exit;
}

$result = $conn->query("SELECT * FROM users WHERE email='$email' LIMIT 1");

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

// Prepare payload (exclude password)
$payload = [
    'id' => $user['id'],
    'email' => $user['email'],
    'first_name' => $user['first_name'],
    'last_name' => $user['last_name'],
    'role' => $user['role']
];

// Create JWT token
$token = jwt_encode($payload, 3600*24); // valid 1 day

// Set token in httpOnly secure cookie
setcookie('token', $token, [
    'expires' => time() + 3600*24, // 1 day
    'path' => '/',
    'domain' => '', // set your domain if needed
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Return user data (except password)
unset($user['password']);
echo json_encode(['user' => $user]);
?>
