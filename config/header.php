<?php
// MUST be the very first output in your PHP script — no whitespace above

// Handle CORS preflight OPTIONS request early and exit
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: http://localhost:5173');  // Your frontend URL
    header('Access-Control-Allow-Credentials: true');              // Required if cookies/sessions are involved
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
    exit();
}

// For actual requests, send these headers:
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: http://localhost:5173');      // Must exactly match frontend origin
header('Access-Control-Allow-Credentials: true');                  // Needed for cookies/session
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Optional but recommended security headers
header('X-XSS-Protection: 1; mode=block');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
?>
