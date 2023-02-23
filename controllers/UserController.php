<?php

require_once(__DIR__ . "../../config/Database.php");
require_once(__DIR__ . "../../models/Orm.php");
require_once(__DIR__ . "../../models/User.php");

// Obtener el método HTTP y los datos de la solicitud
$http_method = $_SERVER['REQUEST_METHOD'];
$input_data = json_decode(file_get_contents('php://input'), true);
$url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
$url = explode("/", $url);
$id = isset($url[3]) ? $url[3] : "";

// Inicializar la conexión a la base de datos
$db = new Database();
$user = new User($db);

// Definir la respuesta por defecto
$response = [
    'status' => false,
    'message' => 'Método no soportado'
];

// Manejar las diferentes operaciones CRUD
switch ($http_method) {
    case 'GET':
        // Consultar por id
        if ($id != "") {
            $data = $user->getById($id);
            if ($data) {
                $response['status'] = true;
                $response['data'] = $data;
                $response['message'] = 'Usuario devuelto';
            } else {
                $response['message'] = 'Usuario no encontrado';
            }
        // Consultar todos
        } else {
            $data = $user->getAll();
            $response['status'] = true;
            $response['data'] = $data;
            $response['message'] = 'Listado de usuarios devuelto';
        }
        break;
    case 'POST':
        // Crear usuario nuevo
        $username = $input_data['username'];
        $email = $input_data['email'];
        $password = $input_data['password'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $values = array();
        $values['username'] = $username;
        $values['email'] = $email;
        $values['password'] = $hashed_password;
        $data = $user->add($values);
        $response['status'] = true;
        $response['message'] = 'Usuario creado exitosamente';
        $response['data'] = $data;
        break;
    case 'PUT':
        // Actualizar usuario
        if ($id != "") {
            $username = $input_data['username'];
            $email = $input_data['email'];
            $password = $input_data['password'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $values = array();
            $values['username'] = $username;
            $values['email'] = $email;
            $values['password'] = $hashed_password;
            $data = $user->update($id, $values);
            $response['status'] = true;
            $response['message'] = 'Usuario actualizado exitosamente';
            $response['data'] = $data;
        } else {
            $response['message'] = 'ID de usuario no especificado';
        }
        break;
    case 'DELETE':
        //Borrar usuario
        if ($id != "") {
            $user->delete($id);
            $response['status'] = true;
            $response['message'] = 'Usuario eliminado exitosamente';
        } else {
            $response['message'] = 'ID de usuario no especificado';
        }
        break;
}

// Enviar la respuesta como JSON
header('Content-type: application/json');
echo json_encode($response);

?>