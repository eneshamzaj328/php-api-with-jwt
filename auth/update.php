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


$updatedData = json_decode(file_get_contents("php://input")) ?? die();

$id = htmlentities($updatedData->id ?? '');
$name = htmlentities($updatedData->name ?? '');
$surname = htmlentities($updatedData->name ?? '');
$username = htmlentities($updatedData->username ?? '');
$email = htmlentities($updatedData->email ?? '');
$password = htmlentities($updatedData->password ?? '');
$new_password = password_hash($password, PASSWORD_DEFAULT);
$user_role = htmlentities($updatedData->role ?? '');


if (empty($id)) {
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Cannot update User!']);
    die();
}

// update user
$updated_user = [
    'name' => $name,
    'surname' => $surname,
    'username' => $username,
    'email' => $email,
    'password' => $password,
    'role' => $user_role
];


if (!empty($id) || !empty($user_role)) {
    $obj->update('users', "role = '{$user_role}'", "id = {$id}");
} else {
    $obj->update('users', $updated_user, "id = {$id}");
}


$result = $obj->getResult();

if (!$result[1]['update']) {
    echo json_encode([
        'status' => 1,
        'message' => 'Error while updating the User! Server problem.'
    ]);

    die();
}

echo json_encode([
    'status' => 1,
    'message' => 'User updated successfully!'
]);
