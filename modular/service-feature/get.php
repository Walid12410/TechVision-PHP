<?php
include "../../config/connection.php";
include "../../config/header.php";

$sql = "SELECT sf.*, 
        s.service_name, s.service_description, s.image_url,
        GROUP_CONCAT(sd.id) as detail_ids,
        GROUP_CONCAT(sd.detail_description) as detail_descriptions
        FROM service_features sf
        JOIN services s ON sf.service_id = s.id
        LEFT JOIN service_details sd ON s.id = sd.service_id
        GROUP BY sf.id
        ORDER BY sf.service_id";

$result = $conn->query($sql);

$features = [];
while($row = $result->fetch_assoc()) {
    // Get the detail IDs and descriptions arrays
    $detailIds = $row['detail_ids'] ? explode(',', $row['detail_ids']) : [];
    $detailDescriptions = $row['detail_descriptions'] ? explode(',', $row['detail_descriptions']) : [];
    
    // Combine details
    $details = [];
    for ($i = 0; $i < count($detailIds); $i++) {
        $details[] = [
            'id' => (int)$detailIds[$i],
            'detail_description' => $detailDescriptions[$i]
        ];
    }

    $features[] = [
        "id" => (int)$row['id'],
        "service_id" => (int)$row['service_id'],
        "service" => [
            "service_name" => $row['service_name'],
            "service_description" => $row['service_description'],
            "image_url" => $row['image_url'],
            "details" => $details
        ]
    ];
}

echo json_encode($features);
http_response_code(200);

$conn->close();
?>