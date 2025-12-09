<?php
require_once __DIR__ . '/../conexion/conexion.php';

class AlabanzasModel {
    private $conn;

    public function __construct() {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    public function getAll() {
        $sql = "SELECT a.*, u.nombre, u.apellido 
                FROM alabanzas a
                LEFT JOIN usuarios u ON a.idusuario_alabanza = u.idusuario
                ORDER BY a.fecha_subida DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM alabanzas WHERE idalabanza = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($arr) {
        try {
            $sql = "INSERT INTO alabanzas (
                idalabanza, titulo, archivo_pdf, archivo_ppt, enlace_video, fecha_subida, idusuario_alabanza
            ) VALUES (UUID_SHORT(), ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['titulo'], 
                $arr['pdf'], 
                $arr['ppt'], 
                $arr['video'],
                date('Y-m-d'), 
                $arr['idusuario']
            ]);
            return $stmt->rowCount() > 0 ? ["success" => "Alabanza registrada."] : ["error" => "No se pudo guardar."];
        } catch (PDOException $e) { return ["error" => "BD Error: " . $e->getMessage()]; }
    }

    public function update($arr) {
        try {
            // Obtener archivos viejos para borrar si se reemplazan
            $oldData = $this->getById($arr['idalabanza']);

            // Lógica: Si viene un archivo nuevo ($arr['pdf']), úsalo. Si no, mantén el viejo ($oldData).
            $finalPDF = !empty($arr['pdf']) ? $arr['pdf'] : $oldData['archivo_pdf'];
            $finalPPT = !empty($arr['ppt']) ? $arr['ppt'] : $oldData['archivo_ppt'];

            // Si hay archivo nuevo, borrar el viejo físico
            if (!empty($arr['pdf']) && !empty($oldData['archivo_pdf'])) {
                $path = __DIR__ . '/../../' . $oldData['archivo_pdf'];
                if (file_exists($path)) unlink($path);
            }
            if (!empty($arr['ppt']) && !empty($oldData['archivo_ppt'])) {
                $path = __DIR__ . '/../../' . $oldData['archivo_ppt'];
                if (file_exists($path)) unlink($path);
            }

            $sql = "UPDATE alabanzas SET titulo=?, archivo_pdf=?, archivo_ppt=?, enlace_video=? WHERE idalabanza=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$arr['titulo'], $finalPDF, $finalPPT, $arr['video'], $arr['idalabanza']]);

            return ["success" => "Alabanza actualizada."];
        } catch (PDOException $e) { return ["error" => "Error: " . $e->getMessage()]; }
    }

    public function delete($id) {
        try {
            $data = $this->getById($id);
            $del = $this->conn->prepare("DELETE FROM alabanzas WHERE idalabanza = ?");
            $del->execute([$id]);

            if ($del->rowCount() > 0) {
                // Borrar archivos asociados
                if (!empty($data['archivo_pdf'])) @unlink(__DIR__ . '/../../' . $data['archivo_pdf']);
                if (!empty($data['archivo_ppt'])) @unlink(__DIR__ . '/../../' . $data['archivo_ppt']);
                return ["success" => "Eliminado correctamente."];
            }
            return ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) { return ["error" => "Error: " . $e->getMessage()]; }
    }
}