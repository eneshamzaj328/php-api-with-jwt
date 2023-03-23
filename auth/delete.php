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

$data = json_decode(file_get_contents("php://input"));

$id = null;
if (!$data && isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = $data->id;
}

if ($id === null) die('Something went wrong while trying to delete theres no id!');

$headers = getallheaders();

$json_web_token = $headers['Authorization'] ?? [];
// echo $json_web_token;
if (!$headers || !$json_web_token) {
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Server error!']);
    die();
}

// check if logged user/person is admin
$user_data = $auth->jwt_decode($json_web_token, 'HS256');
$data = $user_data->data;
$user_role = $data->user_role;

// check if the user exist before deleting it
$user_data = $obj->userExists($id, '', true);

if ($user_data && $user_role !== 'admin') {
    http_response_code(403);

    echo json_encode([
        'status' => 0,
        'message' => 'You have no authorization to this particular action!'
    ]);

    die();
}


if (!$user_data) {
    http_response_code(500);

    $resMsg = empty($user_data) > 0 ? 'User does not exists!' : 'User has been deleted! User not founded.';

    echo json_encode([
        'status' => 0,
        'message' => $resMsg
    ]);

    die();
}


// delete user
$obj->delete("users", "id = {$id}");
$result = $obj->getResult();

if (!$result[2]['delete']) {
    echo json_encode([
        'status' => 0,
        'message' => 'Something went wrong! Server problem.'
    ]);

    exit();
}


$successMsg = 'User ' . ($id ? ':' . $id . ', ' : '') . 'deleted successfully!';
echo json_encode([
    'status' => 1,
    'message' => $successMsg
]);
