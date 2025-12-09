<?php
require_once __DIR__ . '/../conexion/conexion.php';

class AsistenciasModel
{
    private $conn;
    public function __construct() {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    public function getEstadisticasPorMiembro($arr) {
        $sql = "SELECT estado, COUNT(*) as total FROM asistencias WHERE idmiembro = ? GROUP BY estado";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($arr);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetalleAsistencias($arr) {
        $sql = "SELECT 
                    act.fecha,
                    asis.estado, 
                    act.titulo 
                FROM asistencias asis
                INNER JOIN actividades act ON asis.idactividad = act.idactividad
                WHERE asis.idmiembro = ?
                ORDER BY act.fecha DESC 
                LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($arr);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>