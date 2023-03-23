<?php

header('Access-Control-Allow-Origin: *');
header('Access-Conrol-Allow-Method: POST');
header('Content-Type: application/json; Charset=UTF-8');

require '../classes/crud_actions.php';

$obj = new CRUD();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_encode([
        'status' => 0,
        'message' => 'Access Denied'
    ]);

    die('Error 403!');
}

$data = json_decode(file_get_contents("php://input", true));

// error handlers
$error_msg = !$data && !isset($_GET['id']) ? 'No products found! Hint: Search through url or forms.' : 'No recordrs founded!';

if (isset($_GET['id']) && empty($_GET['id'])) $error_msg = 'product_id is empty, no products found!';

if (!$data && (!isset($_GET['id']) || empty($_GET['id']))) {
    echo json_encode([
        'status' => 0,
        'message' => $error_msg
    ]);

    die();
}


$id = null;
if (!$data && isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = $data->id;
}

// $join = "users ON products.user_id = users.id";

// $obj->select('products', '*', $join, "users.id = {$id}", null, null);
$obj->select('products', '*', null, "id = {$id}", null, null);
$result = $obj->getResult();
$products = $result[1] ?? [];


if (count($products['result'] ?? $products) === 0 || empty($products)) {

    echo json_encode([
        'status' => 0,
        'data' => 'No product found!'
    ]);


    exit();
}

$single_product = $products['result'];

http_response_code(200);
echo json_encode([
    'status' => 1,
    'data' => $single_product
]);
