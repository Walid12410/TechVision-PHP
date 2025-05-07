<?php
// headers.php - Centralized header configuration

// Always return JSON response
header('Content-Type: application/json');

// Handle CORS (Cross-Origin Resource Sharing), especially if you're working with a frontend
header('Access-Control-Allow-Origin: *');  // You can restrict this to your domain instead of "*"
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');  // Include Authorization if using tokens

// Additional security headers can be added here, if needed
// For example, you can add HTTP Strict Transport Security (HSTS) for HTTPS sites:
// header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Prevent XSS (Cross-Site Scripting)
header('X-XSS-Protection: 1; mode=block');

// Prevent clickjacking
header('X-Frame-Options: SAMEORIGIN');

// Prevent content sniffing
header('X-Content-Type-Options: nosniff');

// Optionally, specify the character encoding
header('Content-Encoding: UTF-8');
?>
