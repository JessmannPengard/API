<?php

class User extends Orm
{

    public function __construct($db)
    {
        parent::__construct("id", "users", $db);
    }

    // Obtener usuario por nombre de usuario
    public function getUserByUsername($username)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE username = :username");
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    // Obtener usuario por email
    public function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
}

?>