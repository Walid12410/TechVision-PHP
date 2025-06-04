<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid client ID"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$requiredFields = ['first_name', 'last_name', 'email', 'phone_number', 'country_of_origin'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing or empty field: $field"]);
        exit;
    }
}

try {
    // Check if client exists
    $checkSql = "SELECT id FROM clients WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Client not found"]);
        exit;
    }

    // ✅ Define the UPDATE SQL
    $sql = "UPDATE clients 
            SET first_name = ?, last_name = ?, email = ?, phone_number = ?, country_of_origin = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", 
        $data['first_name'], 
        $data['last_name'], 
        $data['email'],
        $data['phone_number'],
        $data['country_of_origin'],
        $id
    );
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Client updated successfully"]);
    } else {
        throw new Exception("Failed to update client: " . $stmt->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

// Always close your statements and connection
$checkStmt->close();
$stmt->close();
$conn->close();
?>
