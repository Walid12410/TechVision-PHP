<?php
include "../config/connection.php";
include "../config/header.php";

try {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'email', 'password', 'role'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            throw new Exception("Missing required field: " . $field);
        }
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Validate password strength
    if (strlen($data['password']) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }

    // Check if email already exists
    $checkSql = "SELECT id FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $data['email']);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows > 0) {
        throw new Exception("Email already registered");
    }

    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

    // Insert user
    $sql = "INSERT INTO users (first_name, last_name, role, email, password, phone_number) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", 
        $data['first_name'],
        $data['last_name'],
        $data['role'],
        $data['email'],
        $hashedPassword,
        $data['phone_number'] ?? null
    );
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Remove password from response
        $user = [
            "id" => $user_id,
            "first_name" => $data['first_name'],
            "last_name" => $data['last_name'],
            "email" => $data['email'],
            "role" => $data['role'],
            "phone_number" => $data['phone_number'] ?? null
        ];

        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "User registered successfully",
            "data" => $user
        ]);
    } else {
        throw new Exception("Failed to register user");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($checkStmt)) $checkStmt->close();
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>