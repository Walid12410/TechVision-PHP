<?php
include "../../config/connection.php";
include "../../config/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid project ID"]);
    exit;
}

try {
    // Get project with client information only
    $sql = "SELECT p.*, 
            c.first_name as client_first_name, c.last_name as client_last_name
            FROM projects p
            LEFT JOIN clients c ON p.client_id = c.id
            WHERE p.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();

    if (!$project) {
        http_response_code(404);
        echo json_encode(["error" => "Project not found"]);
        exit;
    }

    // Get project details
    $detailsSql = "SELECT * FROM project_details WHERE project_id = ?";
    $detailsStmt = $conn->prepare($detailsSql);
    $detailsStmt->bind_param("i", $id);
    $detailsStmt->execute();
    $detailsResult = $detailsStmt->get_result();

    $details = [];
    while ($row = $detailsResult->fetch_assoc()) {
        $details[] = [
            'id' => (int)$row['id'],
            'detail_description' => $row['detail_description']
        ];
    }

    $response = [
        'id' => (int)$project['id'],
        'project_name' => $project['project_name'],
        'project_description' => $project['project_description'],
        'image_url' => $project['image_url'],
        'project_cost' => (float)$project['project_cost'],
        'start_date' => $project['start_date'],
        'end_date' => $project['end_date'],
        'client' => [
            'id' => (int)$project['client_id'],
            'first_name' => $project['client_first_name'],
            'last_name' => $project['client_last_name']
        ],
        'details' => $details
    ];

    echo json_encode($response);
    http_response_code(200);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($detailsStmt)) $detailsStmt->close();
    if (isset($conn)) $conn->close();
}
?>