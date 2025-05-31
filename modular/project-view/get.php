<?php
include "../../config/connection.php";
include "../../config/header.php";

try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(max(1, (int)$_GET['limit']), 50) : 10;
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM project_views ORDER BY create_time DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $totalRows = $conn->query("SELECT COUNT(*) as total FROM project_views")->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $limit);

    $views = [];
    while($row = $result->fetch_assoc()) {
        $views[] = [
            'id' => (int)$row['id'],
            'view_title' => $row['view_title'],
            'view_description' => $row['view_description'],
            'image_url' => $row['image_url'],
            'create_time' => $row['create_time'],
            'view_link' => $row['view_link']
        ];
    }

    $response = [
        "status" => "success",
        "data" => $views,
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