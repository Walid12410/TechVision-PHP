<?php
include "../../config/connection.php";
include "../../config/header.php";

try {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(max(1, (int)$_GET['limit']), 50) : 10;
    $offset = ($page - 1) * $limit;

    // Modified query to include all pricing_details columns
    $sql = "SELECT p.*, 
            GROUP_CONCAT(pd.id) as detail_ids,
            GROUP_CONCAT(pd.pricing_id) as detail_pricing_ids,
            GROUP_CONCAT(pd.pricing_detail) as detail_texts
            FROM pricing_plans p
            LEFT JOIN pricing_details pd ON p.id = pd.pricing_id
            GROUP BY p.id
            ORDER BY p.price ASC 
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get total count
    $totalRows = $conn->query("SELECT COUNT(*) as total FROM pricing_plans")->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $limit);

    $plans = [];
    while($row = $result->fetch_assoc()) {
        // Split the concatenated strings into arrays
        $detailIds = $row['detail_ids'] ? explode(',', $row['detail_ids']) : [];
        $detailPricingIds = $row['detail_pricing_ids'] ? explode(',', $row['detail_pricing_ids']) : [];
        $detailTexts = $row['detail_texts'] ? explode(',', $row['detail_texts']) : [];
        
        // Combine all detail fields
        $details = [];
        for ($i = 0; $i < count($detailIds); $i++) {
            $details[] = [
                'id' => (int)$detailIds[$i],
                'pricing_id' => (int)$detailPricingIds[$i],
                'pricing_detail' => $detailTexts[$i]
            ];
        }
        
        $plans[] = [
            'id' => (int)$row['id'],
            'plan_title' => $row['plan_title'],
            'plan_description' => $row['plan_description'],
            'price' => (float)$row['price'],
            'billing_period' => $row['billing_period'],
            'details' => $details
        ];
    }

    $response = [
        "status" => "success",
        "data" => $plans,
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