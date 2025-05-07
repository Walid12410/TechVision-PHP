<?php
include "../../config/connection.php";
include "../../config/header.php";

$sql = "SELECT * FROM settings LIMIT 1";
$result = $conn->query($sql);

$settings = $result->fetch_assoc();

if ($settings) {
    echo json_encode($settings);
    http_response_code(200);
} else {
    echo json_encode(null);
    http_response_code(404);
}

$conn->close();
?>