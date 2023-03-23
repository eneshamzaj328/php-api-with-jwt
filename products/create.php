<?php

header('Access-Control-Allow-Origin: *');
header('Access-Cotrol-Allow-Method: POST');
header('Content-Type: application/json; Charset=UTF-8');

require '../classes/crud_actions.php';
include '../auth/classes/auth.php';


$obj = new CRUD();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        'status' => 0,
        'message' => 'Access Denied',
    ]);

    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

try {

    $headers = getallheaders();

    $json_web_token = $headers['Authorization'] ?? [];
    if (!$headers || !$json_web_token) {
        http_response_code(403);
        echo json_encode([
            'status' => 0,
            'message' => '403 Forbidden.'
        ]);
        die();
    }


    $auth_user = $auth->jwt_decode($json_web_token) ?? die();
    $username = $auth_user->username ?? '';
    $email = $auth_user->email ?? '';
    $user_role = $auth_user->user_role ?? '';

    $user_data = $obj->userExists($username, $email, true) ?? die();



    if ($user_data && $user_role !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'status' => 0,
            'message' => 'Not authorized! 403.',
        ]);
        die();
    }


    $user_id = $auth_user->data->id;
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $price = $data['price'] ?? '';
    $quantity = $data['quantity'] ?? '';

    if (empty($title) || empty($description) || empty($price) || empty($quantity)) {
        http_response_code(500);
        echo json_encode([
            'status' => 0,
            'message' => 'One or More Fields is/are Empty!',
        ]);

        die();
    }

    $product = ['title' => $title, 'user_id' => $user_id, 'description' => $description, 'price' => $price, 'quantity' => $quantity];

    $obj->insert('products', $product);
    $result = $obj->getResult();

    if ($result[2]['insert'] !== 1) {
        http_response_code(500);
        echo json_encode([
            'status' => 0,
            'message' => 'Something went wrong! Please try Again!',
        ]);

        die();
    }

    http_response_code(200);
    echo json_encode([
        'status' => 1,
        'message' => 'Product Added Successfully!',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 0,
        'message' => $e->getMessage(),
    ]);
}
