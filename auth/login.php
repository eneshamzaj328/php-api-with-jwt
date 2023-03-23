<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Method: POST');
header('Content-Type: application/json; Charset=UTF-8');

require '../classes/crud_actions.php';
include '../auth/classes/auth.php';

$obj = new CRUD();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 0,
        'message' => 'Access Denied',
    ]);

    unset($_COOKIE['jwt_token']);

    die();
}

$data = json_decode(file_get_contents("php://input"), true);

$uid = htmlentities($data['uid'] ?? $data['username']);

$username = '';
$email = '';

if (gettype($uid) === 'string') {
    if (!str_contains($uid, '@')) {
        $username = $uid;
    } else {
        $email = $uid;
    }
}

$password = htmlentities($data['password']);

if (!$obj->userExists($username, $email)) {
    http_response_code(500);

    echo json_encode([
        'status' => 0,
        'message' => 'User does not Exists!',
    ]);

    die();
}

$obj->select('users', '*', null, "username='$username' OR email='$email'", null, null);
$data = $obj->userExists($username, $email, true);

// user login token time-left to expire
$exp_login_token_time = $datetime->set_expire_time(60);
$login_token_time_to_exp = $exp_login_token_time['time'];
$login_token_time_tostr = $exp_login_token_time['time_to_str'];

# Only when debugging: echo $login_token_time_tostr;

$id = $data['id'] ?? '';
$name = $data['name'];
$surname = $data['surname'];
$username = $data['username'] ?? '';
$email = $data['email'] ?? '';
$user_password = $data['password'] ?? '';
$user_role = $data['role'] ?? '';

if (!password_verify($password, $user_password)) {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid Credentials',
    ]);

    exit();
}

$payload = [
    'iss' => 'localhost',
    'aud' => 'localhost',
    'exp' => $login_token_time_to_exp, // 30min
    'data' => [
        'id' => $id,
        'name' => $name,
        'surname' => $surname,
        'username' => $username,
        'email' => $email,
        'user_role' => $user_role
    ]
];


$json_web_token = $auth->jwt_encode($payload, 'HS256');

$cookie_params = [
    'expires' => $login_token_time_to_exp,
    'path' => '/',
    'secure' => true,
    // 'httponly' => true,
    'samesite' => 'Strict'
];

echo json_encode([
    'status' => 1,
    'user' => [
        'name' => $name,
        'surname' => $surname
    ],
    // 'jwt' => $json_web_token, // only if you want to manipulate token in front_end 
    'message' => 'Login Successfully!',
]);

if (isset($_COOKIE['jwt_token']) && !empty($_COOKIE['jwt_token'])) exit();

setcookie('jwt_token', $json_web_token, $cookie_params);

if ($user_role === 'admin') {
    setcookie('user_role', $user_role,  [
        'expires' => $login_token_time_to_exp,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}
