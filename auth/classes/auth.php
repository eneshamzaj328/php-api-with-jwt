<?php

$my_country_timezone = 'Europe/Tirane';

date_default_timezone_set($my_country_timezone);

include '../vendor/autoload.php';
include '../controllers/helpers/date_time.php';


use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;


class Auth extends DateTimeLocal
{
    private $privateKey = 'My_Private_Key';

    public function jwt_encode($payload, string $sslcode = 'HS256')
    {
        $json_web_token = JWT::encode($payload, $this->privateKey, $sslcode);

        return $json_web_token;
    }

    public function jwt_decode($payload, string $sslcode = 'HS256')
    {
        $jwt_user_data = JWT::decode($payload, new Key($this->privateKey, $sslcode));

        return $jwt_user_data;
    }

    public function get_user_data()
    {
        $headers = getallheaders();
        $user_data = $headers['Authentication'];
        echo $user_data;
    }

    public function generate_token(int $user_id, string $user_role)
    {
        $expire = $this->set_expire_time(30);
        $expiration_time = $expire;

        // jwt payload
        $payload = [
            'id' => $user_id,
            'user_role' => $user_role,
            "exp" => $expiration_time
        ];

        // Encode the token payload with JWT secret key
        $token = $this->jwt_encode($payload, 'HS256');

        return $token;
    }
}

$auth = new Auth();
