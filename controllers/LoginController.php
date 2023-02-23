<?php

require_once(__DIR__ . "../../config/config.php");
require_once(__DIR__ . "../../config/Database.php");
require_once(__DIR__ . "../../models/Orm.php");
require_once(__DIR__ . "../../models/User.php");
require_once(__DIR__ . "../../vendor/autoload.php");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


// Comprobamos que el método sea POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Comprobamos el token de sesión
    $headers = apache_request_headers();

    if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer ') !== false) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            $decoded = JWT::decode($token, new key(API_KEY, 'HS512'));
            $userId=$decoded->id;
            $userEmail=$decoded->email;
            $exp=$decoded->exp;
            // token is valid, continue with processing the request
            echo "Token válido: ".$exp."->".$userId."-".$userEmail.": ".$token;
        } catch (Exception $e) {
            // token is invalid, return an error response
            echo "Token no válido: ".$token;
        }
    } else {
        // Authorization header not present, return an error response
        echo "Cabecera de Autorización no se encuentra";
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $email = trim($data['email']);
    $password = $data['password'];

    // Verificamos que email o password no estén vacíos
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(array('message' => 'Correo electrónico y contraseña son requeridos.'));
        exit();
    }

    // Inicializar la conexión a la base de datos
    $db = new Database();
    $user = new User($db);

    // Comprobamos si existe el email en la base de datos
    $userEmail = $user->getUserByEmail($email);
    if (!$userEmail) {
        http_response_code(400);
        echo json_encode(array('message' => 'Email no válido.'));
        exit();
    }
    // Si existe comprobamos que la contraseña sea correcta
    if (password_verify($password, $userEmail['password'])) {
        // Si es correcta generamos el token de sesión
        $payload = array(
            'id' => $userEmail['id'],
            'email' => $userEmail['email'],
            'exp' => time() + (60 * 60 * 24) // Expira en 24 horas
        );
        $token = JWT::encode($payload, API_KEY, 'HS512');

        // Y lo devolvemos en la respuesta
        http_response_code(200);
        echo json_encode(array('token' => $token));
        exit();
    } else {
        // Contraseña no válida
        http_response_code(400);
        echo json_encode(array('message' => 'Contraseña no válida.'));
        exit();
    }
} else {
    // Método no permitido, distinto de POST
    http_response_code(405);
    echo json_encode(array('message' => 'Método no permitido.'));
    exit();
}