<?php
require_once __DIR__ . '/../conexion/conexion.php';

class OracionesModel {
    private $conn;

    public function __construct() {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    // ADMIN: Ve TODAS
    public function getAll() {
        $sql = "SELECT o.*, u.nombre, u.apellido, u.rol 
                FROM oraciones o
                JOIN usuarios u ON o.idusuario_oracion = u.idusuario
                ORDER BY FIELD(o.estado, 'pendiente', 'aprobada', 'rechazada'), o.fecha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NUEVO: Usuario normal ve SOLO sus propias peticiones
    public function getByUser($idUsuario) {
        $sql = "SELECT o.*, u.nombre, u.apellido, u.rol 
                FROM oraciones o
                JOIN usuarios u ON o.idusuario_oracion = u.idusuario
                WHERE o.idusuario_oracion = ?
                ORDER BY o.fecha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NUEVO: Muro público (Solo aprobadas)
    public function getApproved() {
        $sql = "SELECT o.*, u.nombre, u.apellido 
                FROM oraciones o
                JOIN usuarios u ON o.idusuario_oracion = u.idusuario
                WHERE o.estado = 'aprobada'
                ORDER BY o.fecha DESC
                LIMIT 50";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM oraciones WHERE idoracion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($arr) {
        try {
            $sql = "INSERT INTO oraciones (idoracion, descripcion, fecha, estado, idusuario_oracion) 
                    VALUES (UUID_SHORT(), ?, NOW(), ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['descripcion'], 
                $arr['estado'],
                $arr['idusuario']
            ]);
            
            return $stmt->rowCount() > 0 ? ["success" => "Petición registrada correctamente."] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) {
            return ["error" => "Error BD: " . $e->getMessage()];
        }
    }

    public function updateStatus($id, $estado) {
        $sql = "UPDATE oraciones SET estado = ? WHERE idoracion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$estado, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete($id) {
        $sql = "DELETE FROM oraciones WHERE idoracion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
?>