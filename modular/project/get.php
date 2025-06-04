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

    // Get projects with client information only
    $sql = "SELECT p.*, 
            c.first_name as client_first_name, c.last_name as client_last_name
            FROM projects p
            LEFT JOIN clients c ON p.client_id = c.id
            ORDER BY p.id DESC LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get total count using optimized query
    $totalRows = $conn->query("SELECT COUNT(*) as total FROM projects")->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $limit);

    $projects = [];
    while($row = $result->fetch_assoc()) {
        $projects[] = [
            'id' => (int)$row['id'],
            'project_name' => $row['project_name'],
            'project_description' => $row['project_description'],
            'image_url' => $row['image_url'],
            'project_cost' => (float)$row['project_cost'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'client' => [
                'id' => (int)$row['client_id'],
                'name' => $row['client_first_name'] . ' ' . $row['client_last_name']
            ]
        ];
    }

    $response = [
        "status" => "success",
        "data" => $projects,
        "pagination" => [
            "current_page" => $page,
            "total_pages" => $totalPages,
            "total_records" => (int)$totalRows,
            "limit" => $limit
        ]
    ];

    http_response_code(200);
    echo json_encode($response);

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