<?php

header('Access-Control-Allow-Origin: *');
header('Access-Conrol-Allow-Method: POST');
header('Content-Type: application/json; Charset=UTF-8');

require '../classes/crud_actions.php';
include '../auth/classes/auth.php';

$obj = new CRUD();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(500);

    echo json_encode([
        'status' => 0,
        'message' => 'Access denied!'
    ]);

    die();
}


$headers = getallheaders();

$json_web_token = $headers['Authorization'] ?? [];

$token_data = $auth->jwt_decode($json_web_token);

if (!$token_data) {
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Expired token!']);
    die();
}

$auth_user = $token_data->data;
$username = $auth_user->username;
$email = $auth_user->email;
$user_role = $auth_user->user_role;

$user_data = $obj->userExists($username, $email, true);

if (!$user_data) {
    http_response_code(500);

    echo json_encode([
        'status' => 0,
        'message' => 'User does not Exists!',
    ]);

    die();
}

if ($user_data && $user_role === 'admin') {
    $obj->select('users', '*', null, null, null, null);
    $users_results = $obj->getResult();
    $users = $users_results[2]['result'];

    if (!$users) {
        echo json_encode(['status' => 0, 'message' => 'Server Problem.']);

        exit();
    }

    $all_users = [];
    foreach ($users as $user) {
        unset($user['password']);
        $all_users[] = $user;
    }

    echo json_encode(['status' => 1, 'users' => $all_users]);

    die();
}

echo json_encode([
    'status' => 1,
    'message' => $user_data
]);
