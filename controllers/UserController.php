<?php

require_once("../config/Database.php");
require_once("../models/User.php");
require_once("../vendor/autoload.php");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Configurar la respuesta HTTP
header('Content-Type: application/json');

// Obtener el método de la solicitud HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener los datos de la solicitud HTTP
$data = json_decode(file_get_contents('php://input'), true);

// Manejar el inicio de sesión
if ($method == 'GET' && isset($data['email']) && isset($data['password'])) {
    $db = new Database;
    $user = new User($db);
    $jwt = $user->login($data['email'], $data['password']);

    if ($jwt) {
        echo json_encode(array('jwt' => $jwt));
    } else {
        http_response_code(401);
        echo json_encode(array('error' => 'Credenciales inválidas'));
    }
}

// Manejar el registro de usuario
if ($method == 'POST' && isset($data['username']) && isset($data['email']) && isset($data['password'])) {
    $db = new Database;
    $user = new User($db);
    $registered = $user->register($data['username'], $data['email'], $data['password']);

    if ($registered) {
        echo json_encode(array('message' => 'Usuario registrado correctamente'));
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'El correo electrónico ya está registrado'));
    }
}

// Manejar la modificación de usuario
if ($method == 'PUT' && isset($data['jwt']) && isset($data['username']) && isset($data['email']) && isset($data['password'])) {
    try {
        // Verificar si el token JWT es válido y obtener el ID del usuario
        $jwt = $data['jwt'];
        $decoded = JWT::decode($jwt, new Key(API_KEY, 'HS256'));
        $user_id = $decoded->user_id;

        // Modificar el usuario en la base de datos
        $db = new Database;
        $user = new User($db);
        $modified = $user->modify($user_id, $data['username'], $data['email'], $data['password']);

        if ($modified) {
            echo json_encode(array('message' => 'Usuario modificado correctamente'));
        } else {
            http_response_code(400);
            echo json_encode(array('error' => 'El correo electrónico ya está registrado por otro usuario'));
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array('error' => 'Token JWT inválido'));
    }
}

// Manejar la eliminación de usuario
if ($method == 'DELETE' && isset($data['jwt'])) {
    try {
        // Verificar si el token JWT es válido y obtener el ID del usuario
        $jwt = $data['jwt'];
        $decoded = JWT::decode($jwt, new Key(API_KEY, 'HS256'));
        $user_id = $decoded->user_id;

        // Eliminar el usuario de la base de datos
        $db = new Database;
        $user = new User($db);
        $deleted = $user->delete($user_id);

        if ($deleted) {
            echo json_encode(array('message' => 'Usuario eliminado correctamente'));
        } else {
            http_response_code(400);
            echo json_encode(array('error' => 'Error al eliminar el usuario'));
        }
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array('error' => 'Token JWT inválido'));
    }
}

// Si no se reconoce la solicitud HTTP
if (!in_array($method, ["GET", "POST", "PUT", "DELETE"])) {
    http_response_code(400);
    echo json_encode(array('error' => 'Solicitud HTTP inválida'));
}

?>