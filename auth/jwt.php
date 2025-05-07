<?php
// JWT secret key (this should be kept safe)
$secret = "your_super_secret_key";

// Function to generate JWT token
function generateJWT($user_id, $role) {
    global $secret;

    $issued_at = time();
    $expiration_time = $issued_at + 3600;  // Token expires in 1 hour
    $payload = [
        'iat' => $issued_at,
        'exp' => $expiration_time,
        'id' => $user_id,
        'role' => $role
    ];

    // Encode the payload
    $jwt = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', $jwt, $secret, true);
    $jwt .= "." . base64_encode($signature);

    return $jwt;
}

// Function to validate JWT token
function validateJWT($jwt, $secret) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return false;
    }

    list($header, $payload, $signature) = $parts;
    $data = base64_decode($header) . "." . base64_decode($payload);
    $validSignature = hash_hmac('sha256', $data, $secret, true);

    if ($signature !== base64_encode($validSignature)) {
        return false;
    }

    $decoded = json_decode(base64_decode($payload), true);

    if ($decoded['exp'] < time()) {
        return false;
    }

    return $decoded;
}
?>
