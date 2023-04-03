<?php

require_once("../config/Config.php");
require_once("../config/Database.php");
require_once("../models/Message.php");
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

// Manejar la solicitud de envío de mensaje
if ($method == 'POST' && isset($data['jwt']) && isset($data['id_recipient']) && isset($data['content'])) {
    try {
        // Verificar si el remitente está autenticado y obtener su ID
        $jwt = $data['jwt'];
        $decoded = JWT::decode($jwt, new Key(API_KEY, 'HS256'));
        $id_sender = $decoded->user_id;

        // Obtener el ID del destinatario y validar que exista
        $id_recipient = $data['id_recipient'];
        $db = new Database();
        $user = new User($db);
        $exist = $user->exist($id_recipient);
        if (!$exist) {
            http_response_code(400);
            echo json_encode(array('error' => 'El destinatario no existe'));
            return;
        }

        $path = "";
        // Guardar el archivo adjunto, si se proporciona uno
        if (isset($_FILES['file'])) {
            $file_name = $_FILES['file']['name'];
            $file_type = $_FILES['file']['type'];
            $file_size = $_FILES['file']['size'];
            $file_tmp_name = $_FILES['file']['tmp_name'];
            $file_error = $_FILES['file']['error'];
            if ($file_error != UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(array('error' => 'Error al cargar el archivo adjunto'));
                return;
            }
            $path = 'attached_files/' . $id_sender . ' ' . $date_time . '_' . $file_name;
            // Aquí se guarda el archivo en un directorio en el servidor
            move_uploaded_file($file_tmp_name, $path);
        }

        // Insertar el mensaje en la base de datos
        $content = $data['content'];
        $date_time = date('Y-m-d H:i:s');
        $message = new Message($db);
        $message_id = $message->sendMsg($id_sender, $id_recipient, $content, $path, $date_time);

        echo json_encode(array('message' => 'Mensaje enviado con éxito'));
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array('error' => 'Token JWT inválido'));
    }
}

// Manejar la solicitud de obtener mensajes
if ($method == 'GET' && isset($data['jwt']) && isset($data['id_partner'])) {
    try {
        // Verificar si el usuario está autenticado y obtener su ID
        $jwt = $data['jwt'];
        $decoded = JWT::decode($jwt, new Key(API_KEY, 'HS256'));
        $user_id = $decoded->user_id;
        $partner_id = $data['id_partner'];

        $db = new Database();
        $message = new Message($db);
        $messages = $message->getMsgs($user_id, $partner_id);

        echo json_encode($messages);
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array('error' => 'Token JWT inválido'));
    }
}

// Manejar la solicitud de borrar mensajes
if ($method == 'DELETE' && isset($data['jwt']) && isset($data['id_message'])) {
    try {
        // Verificar si el token JWT es válido y obtener el ID del usuario
        $jwt = $data['jwt'];
        $decoded = JWT::decode($jwt, new Key(API_KEY, 'HS256'));
        $user_id = $decoded->user_id;

        // Eliminar el mensaje
        $db = new Database;
        $message = new Message($db);
        $id_message = $data["id_message"];
        $deleted = $message->delete($id_message, $user_id);

        if ($deleted) {
            echo json_encode(array('message' => 'Mensaje eliminado correctamente'));
        } else {
            http_response_code(400);
            echo json_encode(array('error' => 'Error al eliminar el mensaje'));
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