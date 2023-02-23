<?php

// Clase que se conecta a la base de datos
class Database {
  private $host;
  private $user;
  private $password;
  private $dbname;
  private $conn;

  public function __construct() {
    $this->host = 'localhost';
    $this->user = 'root';
    $this->password = '';
    $this->dbname = 'mensapp';

    $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

    try {
      $this->conn = new PDO($dsn, $this->user, $this->password);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      echo "Connection failed: " . $e->getMessage();
      exit;
    }
  }

  // Método que devuelve la conexión a la base de datos
  public function getConnection() {
    return $this->conn;
  }
}

?>