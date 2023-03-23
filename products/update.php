<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Method: POST');
header('Content-Type: application/json; Charset=UTF-8');

require '../classes/crud_actions.php';
include '../auth/classes/auth.php';

$obj = new CRUD();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        'status' => 0,
        'message' => 'Access Denied!'
    ]);

    die();
}

$headers = getallheaders();

$json_web_token = $headers['Authorization'] ?? die();

$token_data = $auth->jwt_decode($json_web_token);

if (!$token_data) {
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Expired token!']);
    die();
}

$updatedData = json_decode(file_get_contents("php://input"));

// product data
$id = $updatedData->id;
$title = $updatedData->title;
$description = $updatedData->description;
$price = $updatedData->price;
$quantity = $updatedData->quantity;


if (empty($id)) {
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Cannot update Product!']);
    die();
}

// check if product exists
$productExists = $obj->productExists($id);
if (!$productExists) {
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Product does not Exists!']);
    die();
}


// update product
$updated_product = [
    'title' => $title,
    'description' => $description,
    'price' => $price,
    'quantity' => $quantity
];


$obj->update('products', $updated_product, "id = {$id}");
$result = $obj->getResult();


if (!$result[1]['update']) {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Error while updating the Product! Server problem.'
    ]);

    die();
}

http_response_code(200);
echo json_encode([
    'status' => 1,
    'message' => 'Product updated successfully!'
]);
