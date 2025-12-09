<?php
require_once __DIR__ . '/../conexion/conexion.php';

class InventarioModel {
    private $conn;

    public function __construct() {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    // Listar todos los bienes
    public function getAll() {
        $sql = "SELECT i.*, u.nombre as autor_nombre, u.apellido as autor_apellido 
                FROM inventario i
                LEFT JOIN usuarios u ON i.idusuario = u.idusuario
                ORDER BY i.fecha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Insertar nuevo bien
    public function insert($arr) {
        try {
            $sql = "INSERT INTO inventario (
                idbien, descripcion, fecha, monto, archivo, idusuario
            ) VALUES (UUID_SHORT(), ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['descripcion'],
                $arr['fecha'],
                $arr['monto'],
                $arr['archivo'],
                $arr['idusuario']
            ]);
            
            return $stmt->rowCount() > 0 ? ["success" => "Bien registrado correctamente."] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) {
            return ["error" => "Error BD: " . $e->getMessage()];
        }
    }

    // Eliminar bien y su archivo
    public function delete($id) {
        try {
            // 1. Obtener ruta del archivo
            $sqlFile = "SELECT archivo FROM inventario WHERE idbien = ?";
            $stmtFile = $this->conn->prepare($sqlFile);
            $stmtFile->execute([$id]);
            $file = $stmtFile->fetch(PDO::FETCH_ASSOC);

            // 2. Eliminar registro
            $sql = "DELETE FROM inventario WHERE idbien = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                // 3. Eliminar archivo físico
                if ($file && !empty($file['archivo'])) {
                    $path = __DIR__ . '/../../' . $file['archivo'];
                    if (file_exists($path)) unlink($path);
                }
                return ["success" => "Bien eliminado del inventario."];
            }
            return ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            return ["error" => "Error: " . $e->getMessage()];
        }
    }
}