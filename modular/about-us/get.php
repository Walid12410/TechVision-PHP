<?php
include "../../config/connection.php";
include "../../config/header.php";

try {
    $sql = "SELECT * FROM about_us LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $about = $result->fetch_assoc();
        echo json_encode($about);
    } else {
        echo json_encode(null);
    }
    
    http_response_code(200);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
?>