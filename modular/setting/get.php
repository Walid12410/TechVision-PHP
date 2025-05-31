<?php
include "../../config/connection.php";
include "../../config/header.php";

try {
    $sql = "SELECT * FROM settings LIMIT 1";
    $result = $conn->query($sql);
    $settings = $result->fetch_assoc();

    if (!$settings) {
        // Default settings
        $defaultSettings = [
            'email' => 'info@techvision.com',
            'facebook_url' => 'https://www.facebook.com/techvision',
            'instagram_url' => 'https://www.instagram.com/techvision',
            'linkedin_url' => 'https://www.linkedin.com/company/techvision',
            'tiktok_url' => 'https://www.tiktok.com/@techvision',
            'whatsapp_url' => 'https://wa.me/0000000000',
            'phone_number' => '+0000000000'
        ];

        // Insert default settings
        $insertSql = "INSERT INTO settings (
            email, facebook_url, instagram_url, linkedin_url, 
            tiktok_url, whatsapp_url, phone_number
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("sssssss", 
            $defaultSettings['email'],
            $defaultSettings['facebook_url'],
            $defaultSettings['instagram_url'],
            $defaultSettings['linkedin_url'],
            $defaultSettings['tiktok_url'],
            $defaultSettings['whatsapp_url'],
            $defaultSettings['phone_number']
        );

        if ($stmt->execute()) {
            $defaultSettings['id'] = $conn->insert_id;
            echo json_encode($defaultSettings);
            http_response_code(201);
        } else {
            throw new Exception("Failed to create default settings");
        }
    } else {
        echo json_encode($settings);
        http_response_code(200);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>