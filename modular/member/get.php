<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM members";
$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Get members with pagination
$sql = "SELECT * FROM members ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while($row = $result->fetch_assoc()) {
    $members[] = $row;
}

$response = [
    "data" => $members,
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
?>