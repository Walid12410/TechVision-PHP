<?php
include "../config/connection.php";
include "../config/header.php";

$data = json_decode(file_get_contents("php://input"));

$first_name = $data->first_name;
$last_name = $data->last_name;
$email = $data->email;
$password = password_hash($data->password, PASSWORD_BCRYPT);
$phone = $data->phone_number;
$role = 'user'; // default role

try {
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone_number, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$first_name, $last_name, $email, $password, $phone, $role]);

    echo json_encode(["message" => "User registered successfully"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "User already exists or database error"]);
}

?>