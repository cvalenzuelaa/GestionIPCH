<?php
require_once __DIR__ . '/../conexion/conexion.php';

class TesoreriaModel {
    private $conn;

    public function __construct() {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    // 1. Obtener todos los movimientos (Para la planilla)
    public function getAll() {
        // Ordenamos por fecha descendente (lo más reciente primero)
        $sql = "SELECT t.*, u.nombre as autor_nombre, u.apellido as autor_apellido 
                FROM tesoreria t
                LEFT JOIN usuarios u ON t.idusuario = u.idusuario
                ORDER BY t.fecha DESC, t.fecha_registro DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Obtener Balance General (Para las tarjetas y gráficos)
    public function getBalanceData() {
        // Sumar ingresos y gastos agrupados por tipo
        $sql = "SELECT tipo, categoria_tipo, SUM(monto) as total 
                FROM tesoreria 
                GROUP BY tipo, categoria_tipo";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Insertar Movimiento
    public function insert($arr) {
        try {
            $sql = "INSERT INTO tesoreria (
                idmovimiento, tipo, categoria_tipo, categoria, 
                monto, descripcion, fecha, comprobante, idusuario
            ) VALUES (UUID_SHORT(), ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['tipo'],
                $arr['categoria_tipo'], // Enum: diezmo, ofrenda, etc.
                $arr['categoria'],      // Título corto o detalle
                $arr['monto'],
                $arr['descripcion'],
                $arr['fecha'],
                $arr['comprobante'],    // Ruta del archivo
                $arr['idusuario']
            ]);
            
            return $stmt->rowCount() > 0 ? ["success" => "Movimiento registrado exitosamente."] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) {
            return ["error" => "Error BD: " . $e->getMessage()];
        }
    }

    // 4. Eliminar Movimiento
    public function delete($id) {
        try {
            // Primero obtenemos el archivo para borrarlo del servidor
            $sqlFile = "SELECT comprobante FROM tesoreria WHERE idmovimiento = ?";
            $stmtFile = $this->conn->prepare($sqlFile);
            $stmtFile->execute([$id]);
            $file = $stmtFile->fetch(PDO::FETCH_ASSOC);

            // Borramos registro
            $sql = "DELETE FROM tesoreria WHERE idmovimiento = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                // Si se borró de la BD, intentamos borrar el archivo físico
                if ($file && !empty($file['comprobante'])) {
                    $path = __DIR__ . '/../../' . $file['comprobante'];
                    if (file_exists($path)) unlink($path);
                }
                return ["success" => "Registro eliminado."];
            }
            return ["error" => "No se pudo eliminar."];
        } catch (PDOException $e) {
            return ["error" => "Error: " . $e->getMessage()];
        }
    }
}