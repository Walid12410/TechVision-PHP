<?php
require 'auth_middleware.php';

header('Content-Type: application/json');

$user = verifyToken(true); // or verifyToken(true) for admin-only

echo json_encode(["message" => "Authenticated", "user" => $user]);


?>