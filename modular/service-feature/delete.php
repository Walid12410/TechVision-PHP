<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid feature ID"]);
    exit;
}

$sql = "DELETE FROM service_features WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Feature not found"]);
} else {
    http_response_code(200);
    echo json_encode(["message" => "Feature deleted successfully"]);
}

$stmt->close();
$conn->close();
?>