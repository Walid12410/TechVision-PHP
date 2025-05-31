<?php
include "../../config/connection.php";
include "../../config/header.php";

$member_id = isset($_GET['member_id']) ? (int)$_GET['member_id'] : 0;

if ($member_id === 0) {
    http_response_code(400);
    echo json_encode(["error" => "Member ID is required"]);
    exit;
}

$sql = "SELECT pm.*, 
        p.project_name, p.project_description, p.image_url as project_image,
        m.first_name, m.last_name, m.email, m.phone_number, m.field_of_expertise
        FROM project_members pm
        JOIN projects p ON pm.project_id = p.id
        JOIN members m ON pm.member_id = m.id
        WHERE pm.member_id = ?
        ORDER BY pm.start_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

$projectMembers = [];
while($row = $result->fetch_assoc()) {
    $projectMembers[] = [
        "id" => $row['id'],
        "project" => [
            "id" => $row['project_id'],
            "name" => $row['project_name'],
            "description" => $row['project_description'],
            "image_url" => $row['project_image']
        ],
        "member" => [
            "id" => $row['member_id'],
            "first_name" => $row['first_name'],
            "last_name" => $row['last_name'],
            "email" => $row['email'],
            "phone_number" => $row['phone_number'],
            "field_of_expertise" => $row['field_of_expertise']
        ],
        "cost" => $row['cost'],
        "cost_received" => $row['cost_received'],
        "start_date" => $row['start_date'],
        "end_date" => $row['end_date']
    ];
}

echo json_encode($projectMembers);
http_response_code(200);

$stmt->close();
$conn->close();
?>