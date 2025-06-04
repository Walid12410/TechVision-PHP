<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$type = isset($_GET['type']) ? $_GET['type'] : '';

try {
    switch($type) {
        case 'projects':
            $sql = "SELECT p.id, p.project_name, p.project_cost, p.start_date, p.end_date,
                    c.first_name as client_first_name, c.last_name as client_last_name
                    FROM projects p
                    LEFT JOIN clients c ON p.client_id = c.id
                    ORDER BY p.start_date DESC LIMIT 10";
            break;
            
        case 'members':
            $sql = "SELECT id, first_name, last_name, field_of_expertise 
                    FROM members ORDER BY id DESC LIMIT 10";
            break;
            
        case 'clients':
            $sql = "SELECT id, first_name, last_name, country_of_origin 
                    FROM clients ORDER BY id DESC LIMIT 10";
            break;
            
        case 'messages':
            $sql = "SELECT id, name, email, subject, created_at 
                    FROM contact_us ORDER BY created_at DESC LIMIT 10";
            break;
            
        default:
            throw new Exception("Invalid summary type");
    }

    $result = $conn->query($sql);
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $items
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}

?>