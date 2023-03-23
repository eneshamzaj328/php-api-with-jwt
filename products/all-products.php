<?php

header('Access-Control-Allow-Origin: *');
header('Access-Cotrol-Allow-Method: GET');
header('Content-Type: application/json; Charset=UTF-8');

require '../classes/crud_actions.php';
include '../auth/classes/auth.php';

$obj = new CRUD();

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode([
        'status' => 0,
        'message' => 'Access Denied',
    ]);

    exit();
}

$data = json_decode(file_get_contents("php://input"), true);


try {

    $obj->select('products', '*', null, null, "BY id DESC", null);
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
