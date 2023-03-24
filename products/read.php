<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Method: POST');
header('Content-Type: application/json; Charset=UTF-8');

require '../classes/crud_actions.php';
include '../auth/classes/auth.php';

$obj = new CRUD();

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Access Denied',
    ]);

    die();
}

$data = json_decode(file_get_contents("php://input"), true);

try {

    $headers = getallheaders();

    $json_web_token = $headers['Authorization'] ?? [];
    if (!$headers || !$json_web_token) {
        http_response_code(500);
        echo json_encode(['status' => 0, 'message' => 'Not authorized!']);
        die();
    }

    $user_data = $auth->jwt_decode($json_web_token);
    $user = $user_data->data;

    $id = $user->id;

    $obj->select('products', '*', null, "user_id='{$id}'", "BY id DESC", null);
    $result = $obj->getResult();

    $products = $result[1]['result'] ?? [];

    if (!$products) {
        if (count($products) === 0) {
            echo json_encode([
                'status' => 0,
                'message' => "No records found!"
            ]);

            exit();
        }

        echo json_encode([
            'status' => 0,
            'message' => "Error: Something went wrong, server problem."
        ]);

        exit();
    }

    echo json_encode([
        'status' => 1,
        'results' => count($products) ?? 0,
        'data' => $products
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 0,
        'message' => $e->getMessage(),
    ]);
}
