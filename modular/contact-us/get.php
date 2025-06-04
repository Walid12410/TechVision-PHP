<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(max(1, (int)$_GET['limit']), 50) : 10;
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM contact_us ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get total count
    $totalRows = $conn->query("SELECT COUNT(*) as total FROM contact_us")->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $limit);

    $messages = [];
    while($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone_number' => $row['phone_number'],
            'subject' => $row['subject'],
            'message' => $row['message'],
            'created_at' => $row['created_at']
        ];
    }

    $response = [
        "status" => "success",
        "data" => $messages,
        "pagination" => [
            "current_page" => $page,
            "total_pages" => $totalPages,
            "total_records" => (int)$totalRows,
            "limit" => $limit
        ]
    ];

    echo json_encode($response);
    http_response_code(200);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}

?>