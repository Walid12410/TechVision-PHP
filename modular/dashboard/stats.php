<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


try {
    // Get counts from different tables
    $stats = [
        'total_projects' => $conn->query("SELECT COUNT(*) as count FROM projects")->fetch_assoc()['count'],
        'total_members' => $conn->query("SELECT COUNT(*) as count FROM members")->fetch_assoc()['count'],
        'total_clients' => $conn->query("SELECT COUNT(*) as count FROM clients")->fetch_assoc()['count'],
        'total_services' => $conn->query("SELECT COUNT(*) as count FROM services")->fetch_assoc()['count'],
        'messages_count' => $conn->query("SELECT COUNT(*) as count FROM contact_us")->fetch_assoc()['count']
    ];

    // Get recent projects
    $recentProjects = [];
    $projectSql = "SELECT p.*, c.first_name, c.last_name 
                  FROM projects p 
                  LEFT JOIN clients c ON p.client_id = c.id 
                  ORDER BY p.start_date DESC LIMIT 5";
    $result = $conn->query($projectSql);
    while ($row = $result->fetch_assoc()) {
        $recentProjects[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "stats" => $stats,
        "recent_projects" => $recentProjects
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>