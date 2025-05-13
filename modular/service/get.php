<?php
include "../../config/connection.php";
include "../../config/header.php";

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Get total count for pagination
$countSql = "SELECT COUNT(DISTINCT s.id) as total FROM services s";
$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Modified query to include detail IDs
$sql = "SELECT s.*, 
        GROUP_CONCAT(sd.id) as detail_ids,
        GROUP_CONCAT(sd.detail_description) as detail_descriptions 
        FROM services s 
        LEFT JOIN service_details sd ON s.id = sd.service_id 
        GROUP BY s.id, s.service_name, s.service_description, s.image_url 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$services = [];
while ($row = $result->fetch_assoc()) {
    // Get the detail IDs array
    $detailIds = $row['detail_ids'] ? explode(',', $row['detail_ids']) : [];
    $detailDescriptions = $row['detail_descriptions'] ? explode(',', $row['detail_descriptions']) : [];
    
    // Combine IDs with descriptions
    $details = [];
    for ($i = 0; $i < count($detailIds); $i++) {
        $details[] = [
            'id' => $detailIds[$i],
            'detail_description' => $detailDescriptions[$i]
        ];
    }
    
    $row['details'] = $details;
    unset($row['detail_ids']);
    unset($row['detail_descriptions']);
    $services[] = $row;
}

$response = [
    "data" => $services,
    "pagination" => [
        "current_page" => $page,
        "total_pages" => $totalPages,
        "total_records" => $totalRows,
        "limit" => $limit
    ]
];

echo json_encode($response);
http_response_code(200);

$stmt->close();
$conn->close();
