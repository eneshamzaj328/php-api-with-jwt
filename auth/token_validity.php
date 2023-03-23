<?php

header('Access-Control-Allow-Origin: *');
header('Access-Conrol-Allow-Method: GET');
header('Content-Type: application/json; Charset=UTF-8');

include '../auth/classes/auth.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(500);

    echo json_encode([
        'status' => 0,
        'message' => 'Access Denied'
    ]);

    exit();
}

try {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    $token = $auth_header;

    if (!$token) {
        die('Error 403.');
    }

    $user_data = $auth->jwt_decode($token);

    if ($user_data) {
        echo json_encode([
            'token_validity' => true,
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'token_validity' => false,
    ]);
}