<?php
require_once __DIR__ . '/../conexion/conexion.php';

class ActividadesModel {
    private $conn;

    public function __construct() {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    public function getResponsables() {
        $sql = "SELECT idmiembro, nombre, apellido FROM miembros WHERE estado LIKE '%Comulgante%' ORDER BY apellido ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($arr) {
        try {
            $sql = "INSERT INTO actividades (idactividad, titulo, descripcion, fecha, hora_inicio, hora_fin, tipo, responsable, idusuario, frecuencia, recurrente) 
                    VALUES (UUID_SHORT(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['titulo'],
                $arr['descripcion'],
                $arr['fecha'],
                $arr['hora_inicio'],
                $arr['hora_fin'],
                $arr['tipo'],
                $arr['responsable'],
                $arr['idusuario'],
                $arr['frecuencia'],
                ($arr['frecuencia'] === 'unica') ? 0 : 1
            ]);
            
            return ["success" => "Actividad creada"];
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function checkTimeConflicts($fecha, $hora_inicio, $hora_fin) {
        $sql = "SELECT idactividad, titulo, hora_inicio, hora_fin 
                FROM actividades 
                WHERE fecha = ? 
                AND estado != 'finalizada'
                AND (
                    (hora_inicio < ? AND hora_fin > ?) OR
                    (hora_inicio < ? AND hora_fin > ?) OR
                    (hora_inicio >= ? AND hora_fin <= ?)
                )";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$fecha, $hora_fin, $hora_inicio, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActivitiesByRange($start, $end) {
        $sql = "SELECT a.idactividad, a.titulo, a.fecha, a.hora_inicio, a.hora_fin, a.tipo, a.descripcion, a.estado,
                COALESCE(m.nombre, u.nombre) as resp_nombre,
                COALESCE(m.apellido, u.apellido) as resp_apellido
                FROM actividades a
                LEFT JOIN miembros m ON a.responsable = m.idmiembro
                LEFT JOIN usuarios u ON a.idusuario = u.idusuario
                WHERE a.fecha BETWEEN ? AND ?
                ORDER BY a.fecha ASC, a.hora_inicio ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBirthdaysByMonth($month) {
        $sql = "SELECT idmiembro, nombre, apellido, fecha_nacimiento 
                FROM miembros 
                WHERE MONTH(fecha_nacimiento) = ? 
                AND estado != 'No comulgante'
                ORDER BY DAY(fecha_nacimiento) ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApprovedPrayersByRange($start, $end) {
        $sql = "SELECT o.idoracion, o.fecha, 
                CONCAT(m.nombre, ' ', m.apellido) as solicitante, 
                o.descripcion 
                FROM oraciones o
                LEFT JOIN miembros m ON o.idusuario_oracion = m.idmiembro
                WHERE o.estado = 'aprobada' 
                AND o.fecha BETWEEN ? AND ?
                ORDER BY o.fecha ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlySummary($year, $month) {
        $sql = "SELECT tipo, COUNT(*) as total 
                FROM actividades 
                WHERE YEAR(fecha) = ? AND MONTH(fecha) = ? 
                GROUP BY tipo";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActivitiesReport($year, $month) {
        $sql = "SELECT a.fecha, a.titulo, a.tipo, a.hora_inicio, a.hora_fin, a.descripcion,
                CONCAT(COALESCE(m.nombre, u.nombre), ' ', COALESCE(m.apellido, u.apellido)) as responsable
                FROM actividades a
                LEFT JOIN miembros m ON a.responsable = m.idmiembro
                LEFT JOIN usuarios u ON a.idusuario = u.idusuario
                WHERE YEAR(a.fecha) = ? AND MONTH(a.fecha) = ?
                ORDER BY a.fecha ASC, a.hora_inicio ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBirthdaysReport($month) {
        $sql = "SELECT nombre, apellido, fecha_nacimiento, telefono, correo
                FROM miembros
                WHERE MONTH(fecha_nacimiento) = ?
                AND estado != 'No comulgante'
                ORDER BY DAY(fecha_nacimiento) ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPrayersReport($year, $month) {
        $sql = "SELECT o.fecha, 
                CONCAT(m.nombre, ' ', m.apellido) as solicitante,
                o.descripcion
                FROM oraciones o
                LEFT JOIN miembros m ON o.idusuario_oracion = m.idmiembro
                WHERE o.estado = 'aprobada'
                AND YEAR(o.fecha) = ? AND MONTH(o.fecha) = ?
                ORDER BY o.fecha ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMembersForAttendance() {
        $sql = "SELECT idmiembro, nombre, apellido, estado 
                FROM miembros 
                WHERE estado IN ('Comulgante', 'Adherente', 'Visita') 
                ORDER BY apellido ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttendanceByActivity($idactividad) {
        $sql = "SELECT m.idmiembro, m.nombre, m.apellido, m.estado, 
                COALESCE(a.estado, 'Ausente') as asistencia_estado
                FROM miembros m
                LEFT JOIN asistencias a ON m.idmiembro = a.idmiembro AND a.idactividad = ?
                WHERE m.estado IN ('Comulgante', 'Adherente', 'Visita')
                ORDER BY m.apellido ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idactividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveAttendance($idactividad, $asistentes) {
        try {
            $this->conn->beginTransaction();

            $sqlDelete = "DELETE FROM asistencias WHERE idactividad = ?";
            $stmtDel = $this->conn->prepare($sqlDelete);
            $stmtDel->execute([$idactividad]);

            $allMembers = $this->getMembersForAttendance();
            $presentesIds = array_column($asistentes, 'id');

            $sqlInsert = "INSERT INTO asistencias (idasistencia, idactividad, idmiembro, estado, fecha_registro) 
                         VALUES (UUID_SHORT(), ?, ?, ?, NOW())";
            $stmtIns = $this->conn->prepare($sqlInsert);
            
            foreach ($allMembers as $member) {
                $estado = in_array($member['idmiembro'], $presentesIds) ? 'Presente' : 'Ausente';
                $stmtIns->execute([$idactividad, $member['idmiembro'], $estado]);
            }

            $this->conn->commit();
            return ["success" => true, "msg" => "Asistencia guardada"];

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ["error" => $e->getMessage()];
        }
    }

    public function updateEstado($idactividad, $nuevoEstado) {
        $sql = "UPDATE actividades SET estado = ? WHERE idactividad = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nuevoEstado, $idactividad]);
    }
}
?>