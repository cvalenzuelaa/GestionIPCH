<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'Aiep');
define('DB_PASS', 'aiep');
define('DB_NAME', 'gestionipch2');
define('DB_CHARSET', 'utf8mb4');
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET);

define('DB_OPTIONS', [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
]);

class Conexion
{
  public $conexion = null;
  public function __construct()
  {
    try {
      $this->conexion = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
      $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      $this->conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
      echo 'Error de conexión: '. $e->getMessage();
    }
  }
  public function getConexion()
  {
    return $this->conexion;
  }
}
