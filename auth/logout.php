<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Method: POST');
header('Content-Type: application/json; Charset=UTF-8');

require '../auth/classes/auth.php';

// Handle logout request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Access Denied'
    ]);
    exit();
}


$data = json_decode(file_get_contents("php://input", true));

// Retrieve JWT token from Authorization header
$auth_header = $_SERVER['HTTP_AUTHORIZATION'];
$token = $auth_header;

if (str_contains($token, 'Bearer')) {
    $token = str_replace('Bearer ', '', $token);
}

if (!$token) {
    // Return response indicating no JWT token was present
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(array('message' => 'No JWT token present'));

    die();
}


// Decode JWT token
$payload = $auth->jwt_decode($token);

// Set expiration time to a date in the past
$payload->exp = strtotime('-1 day');

// Encode modified payload back into a new JWT token
$new_token = $auth->jwt_encode((array) $payload);

// Return response indicating logout was successful
header('Content-Type: application/json');


$cookie_params = [
    'expires' => time() + 1,
    'path' => '/',
    'secure' => true,
    // 'httponly' => true,
    'samesite' => 'Strict'
];

setcookie('jwt_token', '', $cookie_params);
unset($_COOKIE['jwt_token']);
setcookie('user_role', '', $cookie_params);

if (isset($_COOKIE['jwt_token'])) {
    die();
}

echo json_encode(['status' => 1, 'message' => 'You have been, Logged Out!']);
