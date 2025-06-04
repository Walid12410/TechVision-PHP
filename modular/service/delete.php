<?php
include "../../config/connection.php";
include "../../config/header.php";
include "../../auth/auth.php";

// Check if the user is authenticated
is_admin(); // This will throw 401 if not authenticated


try {
    $id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(["error" => "Service ID is required"]));

    // Start transaction
    $conn->begin_transaction();

    // Check if service exists in service_features
    $checkFeaturesSql = "SELECT COUNT(*) as count FROM service_features WHERE service_id = ?";
    $checkFeaturesStmt = $conn->prepare($checkFeaturesSql);
    $checkFeaturesStmt->bind_param("i", $id);
    $checkFeaturesStmt->execute();
    $featuresResult = $checkFeaturesStmt->get_result()->fetch_assoc();

    if ($featuresResult['count'] > 0) {
        throw new Exception("Cannot delete service. It is referenced in service features.");
    }

    // Get image URL before deletion
    $getImageSql = "SELECT image_url FROM services WHERE id = ?";
    $getImageStmt = $conn->prepare($getImageSql);
    $getImageStmt->bind_param("i", $id);
    $getImageStmt->execute();
    $imageResult = $getImageStmt->get_result()->fetch_assoc();

    // Delete service details first (due to foreign key constraint)
    $deleteDetailsSql = "DELETE FROM service_details WHERE service_id = ?";
    $deleteDetailsStmt = $conn->prepare($deleteDetailsSql);
    $deleteDetailsStmt->bind_param("i", $id);
    $deleteDetailsStmt->execute();

    // Delete the service
    $deleteServiceSql = "DELETE FROM services WHERE id = ?";
    $deleteServiceStmt = $conn->prepare($deleteServiceSql);
    $deleteServiceStmt->bind_param("i", $id);
    $deleteServiceStmt->execute();

    // Delete the image file if it exists
    if ($imageResult && $imageResult['image_url']) {
        $imagePath = "../../" . $imageResult['image_url'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $conn->commit();

    http_response_code(200);
    echo json_encode(["message" => "Service deleted successfully"]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($checkFeaturesStmt)) $checkFeaturesStmt->close();
    if (isset($getImageStmt)) $getImageStmt->close();
    if (isset($deleteDetailsStmt)) $deleteDetailsStmt->close();
    if (isset($deleteServiceStmt)) $deleteServiceStmt->close();
    $conn->close();
}
?>