<?php

header('Access-Control-Allow-Origin: *');
header('Access-Conrol-Allow-Methods: GET/POST');
header('Content-Type: application/json; Charset=UTF-8');

require '../classes/crud_actions.php';
include '../auth/classes/auth.php';

$obj = new CRUD();

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(500);

    echo json_encode([
        'status' => 0,
        'message' => 'Access denied!'
    ]);

    die();
}


$headers = getallheaders();

try {

    $json_web_token = $headers['Authorization'] ?? [];
    // echo $json_web_token;
    if (!$headers || !$json_web_token) {
        http_response_code(500);
        echo json_encode(['status' => 0, 'message' => 'Expired token!']);
        die();
    }

    $user_data = $auth->jwt_decode($json_web_token, 'HS256');
    $data = $user_data->data;

    if (!$data) die('Error 500.');

    echo json_encode([
        'status' => 1,
        'message' => $data,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 0,
        'message' => $e->getMessage(),
    ]);
}
