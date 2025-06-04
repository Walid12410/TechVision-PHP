<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['project_id']) || !isset($data['member_id']) || 
    !isset($data['cost']) || !isset($data['start_date'])) {
    http_response_code(400);
    echo json_encode(["error" => "Required fields missing"]);
    exit;
}

try {
    // Check if project exists
    $checkProjectSql = "SELECT id FROM projects WHERE id = ?";
    $checkProjectStmt = $conn->prepare($checkProjectSql);
    $checkProjectStmt->bind_param("i", $data['project_id']);
    $checkProjectStmt->execute();
    if ($checkProjectStmt->get_result()->num_rows === 0) {
        throw new Exception("Project not found");
    }

    // Check if member exists
    $checkMemberSql = "SELECT id FROM members WHERE id = ?";
    $checkMemberStmt = $conn->prepare($checkMemberSql);
    $checkMemberStmt->bind_param("i", $data['member_id']);
    $checkMemberStmt->execute();
    if ($checkMemberStmt->get_result()->num_rows === 0) {
        throw new Exception("Member not found");
    }

    // Insert project member with all fields
    $sql = "INSERT INTO project_members (
        project_id, member_id, cost, cost_received, start_date, end_date
    ) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $costReceived = $data['cost_received'] ?? 0;
    $endDate = $data['end_date'] ?? null;
    
    $stmt->bind_param("iiddss", 
        $data['project_id'],
        $data['member_id'],
        $data['cost'],
        $costReceived,
        $data['start_date'],
        $endDate
    );
    
    $stmt->execute();

    http_response_code(201);
    echo json_encode([
        "message" => "Project member assigned successfully",
        "id" => $conn->insert_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();

?>