<?php

require_once("../config/Config.php");
require_once("../vendor/autoload.php");

use Firebase\JWT\JWT;

class User
{
    protected $conn;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db->getConnection();
    }

    // Registrar un nuevo usuario
    function register($username, $email, $password)
    {
        // Comprobar si el correo electrónico ya está registrado
        $stmt = $this->conn->prepare('SELECT id FROM Users WHERE email = :email');
        $stmt->execute(array('email' => $email));
        $exist = $stmt->fetch();

        if ($exist) {
            // El correo electrónico ya está registrado
            return false;
        }

        // Crear una contraseña segura
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el usuario en la base de datos
        $stmt = $this->conn->prepare('INSERT INTO Users (username, email, password) VALUES (:username, :email, :password)');
        $stmt->execute(array('username' => $username, 'email' => $email, 'password' => $hash));

        // El usuario se ha registrado correctamente
        return true;
    }

    // Iniciar sesión
    function login($email, $password)
    {
        // Buscar el usuario por correo electrónico
        $stmt = $this->conn->prepare('SELECT id, password FROM Users WHERE email = :email');
        $stmt->execute(array('email' => $email));
        $user = $stmt->fetch();

        if (!$user) {
            // El correo electrónico no está registrado
            return false;
        }

        // Comprobar la contraseña
        if (!password_verify($password, $user['password'])) {
            // La contraseña es incorrecta
            return false;
        }

        // Generar un token JWT
        $payload = array(
            'user_id' => $user['id'],
            'exp' => time() + (60 * 60 * 24) // Expira en 1 día
        );
        $jwt = JWT::encode($payload, API_KEY, 'HS256');

        // Devolver el token JWT
        return $jwt;
    }

    // Modificar un usuario
    function modify($user_id, $username, $email, $password)
    {
        // Comprobar si el correo electrónico ya está registrado por otro usuario
        $stmt = $this->conn->prepare('SELECT id FROM Users WHERE email = :email AND id != :user_id');
        $stmt->execute(array('email' => $email, 'user_id' => $user_id));
        $exist = $stmt->fetch();

        // El correo electrónico ya está registrado por otro usuario
        if ($exist) {
            return false;
        }

        // Si no se proporciona una contraseña, no se modifica
        if ($password) {
            // Crear una contraseña segura
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Actualizar el usuario en la base de datos con la nueva contraseña
            $stmt = $this->conn->prepare('UPDATE Users SET username = :username, email = :email, password = :password WHERE id = :user_id');
            $stmt->execute(array('username' => $username, 'email' => $email, 'password' => $hash, 'user_id' => $user_id));
        } else {
            // Actualizar el usuario en la base de datos sin modificar la contraseña
            $stmt = $this->conn->prepare('UPDATE Users SET username = :username, email = :email WHERE id = :user_id');
            $stmt->execute(array('username' => $username, 'email' => $email, 'user_id' => $user_id));
        }

        // El usuario se ha modificado correctamente
        return true;
    }

    // Eliminar un usuario
    function delete($user_id)
    {
        // Eliminar el usuario de la base de datos
        $stmt = $this->conn->prepare('DELETE FROM Users WHERE id = :user_id');
        $stmt->execute(array('user_id' => $user_id));

        // El usuario se ha eliminado correctamente
        return true;
    }

    // Comprobar si un usuario existe
    function exist($user_id)
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM Users WHERE id = ?');
        $stmt->execute(array($user_id));
        if ($stmt->fetchColumn() == 0) {
            return false;
        } else {
            return true;
        }
    }

}

?>