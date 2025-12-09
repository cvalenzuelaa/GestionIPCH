<?php
require_once __DIR__ . '/../conexion/conexion.php';

class SermonesModel {
    private $conn;

    public function __construct() {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    // --- SERIES ---
    public function getSeries() {
        // Ordenar: Primero las activas, luego por fecha
        $sql = "SELECT * FROM sermon_series ORDER BY estado DESC, fecha_inicio DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertSerie($arr) {
        try {
            $sql = "INSERT INTO sermon_series (idserie, titulo, descripcion, imagen_cover, fecha_inicio, estado) 
                    VALUES (UUID_SHORT(), ?, ?, ?, ?, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$arr['titulo'], $arr['desc'], $arr['img'], $arr['fecha']]);
            return ["success" => "Serie creada correctamente."];
        } catch (PDOException $e) { return ["error" => $e->getMessage()]; }
    }

    // --- NUEVO: CAMBIAR ESTADO DE SERIE ---
    public function updateSerieStatus($id, $estado) {
        try {
            $sql = "UPDATE sermon_series SET estado = ? WHERE idserie = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$estado, $id]);
            return true;
        } catch (PDOException $e) { return false; }
    }
    // --------------------------------------

    // ... (El resto de métodos getSermonesBySerie, insertSermon, deleteSermon siguen igual) ...
    public function getSermonesBySerie($idSerie) {
        $sql = "SELECT * FROM sermones WHERE idserie = ? ORDER BY fecha_predicacion DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idSerie]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertSermon($arr) {
        try {
            $sql = "INSERT INTO sermones (idsermon, idserie, titulo, predicador, descripcion, cita_biblica, url_video, archivo_notas, fecha_predicacion) 
                    VALUES (UUID_SHORT(), ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['idserie'], $arr['titulo'], $arr['predicador'], $arr['desc'], 
                $arr['cita'], $arr['video'], $arr['pdf'], $arr['fecha']
            ]);
            return ["success" => "Sermón publicado."];
        } catch (PDOException $e) { return ["error" => $e->getMessage()]; }
    }

    public function deleteSermon($id) {
        $stmt = $this->conn->prepare("DELETE FROM sermones WHERE idsermon = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}