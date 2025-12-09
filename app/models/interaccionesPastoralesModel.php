<?php
require_once __DIR__ . '/../conexion/conexion.php';

class InteraccionesPastoralesModel
{
    private $conn = null;

    public function __construct()
    {
        $this->conn = new Conexion();
        $this->conn = $this->conn->getConexion();
    }

    public function getByMiembro($arr)
    {
        // Traemos las notas y el nombre del autor
        $sql = "SELECT i.*, u.nombre as autor_nombre 
                FROM interacciones_pastorales i 
                LEFT JOIN usuarios u ON i.idusuario_registro = u.idusuario 
                WHERE i.idmiembro = ? 
                ORDER BY i.fecha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($arr);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($arr)
    {
        try {
            // Insertamos sin fecha (la BD usa CURRENT_TIMESTAMP)
            $sql = "INSERT INTO interacciones_pastorales 
                    (idinteraccion, idmiembro, tipo, descripcion, idusuario_registro) 
                    VALUES (UUID_SHORT(), ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($arr);

            if ($stmt->rowCount() > 0) {
                return array("success" => "Nota pastoral registrada correctamente.");
            } else {
                return array("error" => "No se pudo guardar el registro.");
            }
        } catch (PDOException $e) {
            return array("error" => "Error SQL: " . $e->getMessage());
        }
    }
}