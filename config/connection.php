<?php
// db.php
$host = 'localhost';
$db = 'tech-vision';
$user = 'root'; // Use your DB username
$pass = '1234'; // Use your DB password

$conn = new mysqli($host, $user, $pass, $db);

// Check if the connection is successful
if ($conn->connect_error) {
    http_response_code(500); // Internal server error
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}
?>
