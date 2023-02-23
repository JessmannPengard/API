<?php

class Orm
{
    protected $id;
    protected $table;
    protected $conn;

    public function __construct($id, $table, $db)
    {
        $this->id = $id;
        $this->table = $table;
        $this->conn = $db->getConnection();
    }

    // Obtener todos
    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    // Obtener por id
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id}=:id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    // Obtener todos excepto un id
    public function getAllButId($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE {$this->id}!=:id");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    // Crear nuevo
    public function add($data)
    {
        $sql = "INSERT INTO {$this->table} ";
        $keys = "(";
        $values = "VALUES (";

        foreach ($data as $key => $value) {
            $keys .= "{$key},";
            $values .= ":{$key},";
        }
        $keys = substr($keys, 0, -1) . ")";
        $values = substr($values, 0, -1) . ")";
        $sql .= $keys . $values;
        $stm = $this->conn->prepare($sql);
        foreach ($data as $key => $value) {
            $stm->bindValue(":{$key}", $value);
        }
        $stm->execute();
        $id = $this->conn->lastInsertId();
        return $this->getById($id);
    }

    // Actualizar por id
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET ";

        foreach ($data as $key => $value) {
            $sql .= "{$key}=:{$key},";
        }
        $sql = substr($sql, 0, -1);
        $sql .= " WHERE {$this->id}=:id";

        $stmt = $this->conn->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $this->getById($id);
    }

    // Borrar por id
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE {$this->id}=:id");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        $stmt->execute();
    }
}

?>