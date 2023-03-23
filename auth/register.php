<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Method: POST');
header('Content-Type: application/json; Charset=UTF-8');

require '../classes/crud_actions.php';
require_once '../controllers/helpers/date_time.php';

$obj = new CRUD();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 0,
        'message' => 'Access Denied'
    ]);

    die();
}


$register_data = json_decode(file_get_contents("php://input", true));

$name = htmlentities($register_data->name);
$surname = htmlentities($register_data->surname);
$username = htmlentities($register_data->username);
$email = htmlentities($register_data->email);
// $password = htmlentities($password); //sha1($password)); //password_hash($register_data->password, PASSWORD_BCRYPT));
$password = htmlentities($register_data->password); // enicodex.2023
$new_password = password_hash($password, PASSWORD_DEFAULT); //htmlentities();
$user_role = htmlentities($register_data->role ?? '');


if (empty($name) || empty($surname) || empty($username) || empty($email) || empty($password)) {
    echo json_encode([
        'status' => 0,
        'message' => "One or more field is Empty! \n Please Fill in all Fields and Try Again!"
    ]);

    die();
}


$user_data = $obj->userExists($username, $email, true);
$result = $obj->getResult();


if ($user_data || $result[0]['user_exists']) {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'User already Exists!',
    ]);

    die();
}



$obj->insert('users', [
    'name' => $name,
    'surname' => $surname,
    'username' => $username,
    'email' => $email,
    'password' => $new_password,
    'role' => !empty($user_role) ? $user_role : 'standard',
    'created_at' => $datetime->format_str('Y-m-d H:i:s')
]);
$result = $obj->getResult();

if ($result[1]['insert'] !== 1) {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Something went wrong, Server Problem!',
    ]);


    die();
}

if (isset($_COOKIE['jwt_token'])) {
    unset($_COOKIE['jwt_token']);
    unset($_COOKIE['user_role']);
}

http_response_code(200);
echo json_encode([
    'status' => 1,
    'message' => 'User Registered Successfully!',
]);
