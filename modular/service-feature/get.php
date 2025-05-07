<?php
include "../../config/connection.php";
include "../../config/header.php";

$sql = "SELECT sf.*, s.service_name, s.service_description, s.image_url 
        FROM service_features sf
        JOIN services s ON sf.service_id = s.id
        ORDER BY sf.service_id";

$result = $conn->query($sql);

$features = [];
while($row = $result->fetch_assoc()) {
    $features[] = [
        "id" => $row['id'],
        "service_id" => $row['service_id'],
        "feature_text" => $row['feature_text'],
        "service" => [
            "name" => $row['service_name'],
            "description" => $row['service_description'],
            "image_url" => $row['image_url']
        ]
    ];
}

echo json_encode($features);
http_response_code(200);

$conn->close();
?>