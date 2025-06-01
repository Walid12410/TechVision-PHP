<?php
// Clear token cookie by setting expiry to past
setcookie('token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'domain' => '', // set your domain if needed
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

echo json_encode(['success' => 'Logged out']);
?>
