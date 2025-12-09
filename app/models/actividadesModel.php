<?php
require_once __DIR__ . '/../conexion/conexion.php';

class ActividadesModel {
    private $conn;

    public function __construct() {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    // ... (TUS FUNCIONES EXISTENTES: getResponsables, insert, checkTimeConflicts, etc. SE MANTIENEN IGUAL) ...
    // ... (NO BORRES NADA DE LO ANTERIOR) ...

    // REPETIMOS LAS FUNCIONES CRÍTICAS QUE YA TIENES (Para que al copiar y pegar no pierdas nada)
    public function getResponsables() {
        $sql = "SELECT idmiembro, nombre, apellido FROM miembros WHERE estado LIKE '%Comulgante%' ORDER BY apellido ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($arr) {
        try {
            $sql = "INSERT INTO actividades (idactividad, titulo, descripcion, fecha, tipo, hora_inicio, hora_fin, responsable, idusuario, estado, recurrente, frecuencia) 
                    VALUES (UUID_SHORT(), ?, ?, ?, ?, ?, ?, ?, ?, 'programada', ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['titulo'], $arr['descripcion'], $arr['fecha'], $arr['tipo'],
                $arr['hora_inicio'], $arr['hora_fin'], $arr['responsable'], $arr['idusuario'],
                $arr['recurrente'], $arr['frecuencia']
            ]);
            return $stmt->rowCount() > 0 ? ["success" => "Guardado correctamente."] : ["error" => "No se guardó."];
        } catch (PDOException $e) { return ["error" => "Error BD: " . $e->getMessage()]; }
    }

    public function checkTimeConflicts($fecha, $hora_inicio, $hora_fin) {
        $sql = "SELECT titulo FROM actividades WHERE fecha = ? AND estado != 'cancelada' AND (hora_inicio < ? AND hora_fin > ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$fecha, $hora_fin, $hora_inicio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // EN app/models/actividadesModel.php

// 4. Actividades para Calendario (Versión Híbrida: Busca Miembros y Usuarios)
public function getActivitiesByRange($start, $end) {
    // Usamos COALESCE para decir: "Si no encuentras el nombre en miembros, búscalo en usuarios"
    $sql = "SELECT a.idactividad, a.titulo, a.fecha, a.hora_inicio, a.hora_fin, a.tipo, a.descripcion,
            COALESCE(m.nombre, u.nombre) as resp_nombre, 
            COALESCE(m.apellido, u.apellido) as resp_apellido
            FROM actividades a
            LEFT JOIN miembros m ON a.responsable = m.idmiembro
            LEFT JOIN usuarios u ON a.responsable = u.idusuario
            WHERE a.fecha BETWEEN ? AND ? 
            AND a.estado != 'cancelada'";
            
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$start, $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getBirthdaysByMonth($month) {
        $sql = "SELECT idmiembro, nombre, apellido, fecha_nacimiento FROM miembros WHERE MONTH(fecha_nacimiento) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApprovedPrayersByRange($start, $end) {
        $start = date('Y-m-d', strtotime($start));
        $end = date('Y-m-d', strtotime($end));
        $sql = "SELECT o.idoracion, o.descripcion, o.fecha, u.nombre, u.apellido 
                FROM oraciones o
                LEFT JOIN usuarios u ON o.idusuario_oracion = u.idusuario
                WHERE o.estado = 'aprobada' AND DATE(o.fecha) BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlySummary($year, $month) {
        $sql = "SELECT tipo, COUNT(*) as total FROM actividades WHERE YEAR(fecha) = ? AND MONTH(fecha) = ? AND estado != 'cancelada' GROUP BY tipo";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // NUEVAS FUNCIONES PARA EL REPORTE EXCEL (AGREGAR AL FINAL)
    // =========================================================

    public function getActivitiesReport($year, $month) {
        $sql = "SELECT a.fecha, a.hora_inicio, a.hora_fin, a.titulo, a.tipo, a.descripcion,
                m.nombre as resp_nombre, m.apellido as resp_apellido
                FROM actividades a
                LEFT JOIN miembros m ON a.responsable = m.idmiembro
                WHERE YEAR(a.fecha) = ? AND MONTH(a.fecha) = ? AND a.estado != 'cancelada'
                ORDER BY a.fecha ASC, a.hora_inicio ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBirthdaysReport($month) {
        $sql = "SELECT nombre, apellido, fecha_nacimiento, DAY(fecha_nacimiento) as dia 
                FROM miembros WHERE MONTH(fecha_nacimiento) = ? ORDER BY dia ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPrayersReport($year, $month) {
        $sql = "SELECT o.fecha, o.descripcion, u.nombre, u.apellido
                FROM oraciones o
                LEFT JOIN usuarios u ON o.idusuario_oracion = u.idusuario
                WHERE YEAR(o.fecha) = ? AND MONTH(o.fecha) = ? AND o.estado = 'aprobada'
                ORDER BY o.fecha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMembersForAttendance() {
        $sql = "SELECT idmiembro, nombre, apellido, estado 
                FROM miembros 
                WHERE estado IN ('Comulgante', 'No comulgante', 'Adherente') 
                ORDER BY apellido ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Obtener la asistencia actual de una actividad
    public function getAttendanceByActivity($idactividad) {
        $sql = "SELECT idmiembro, estado FROM asistencias WHERE idactividad = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idactividad]);
        // Devolvemos un array clave-valor: [idmiembro => 'Presente', ...]
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    // 3. Guardar Asistencia (Elimina lo anterior y guarda lo nuevo)
    public function saveAttendance($idactividad, $asistentes) {
        try {
            $this->conn->beginTransaction();

            // A. Borramos la asistencia previa de esta actividad para evitar duplicados
            $sqlDelete = "DELETE FROM asistencias WHERE idactividad = ?";
            $stmtDel = $this->conn->prepare($sqlDelete);
            $stmtDel->execute([$idactividad]);

            // B. Insertamos solo a los que vienen marcados (Presentes/Justificados)
            if (!empty($asistentes)) {
                $sqlInsert = "INSERT INTO asistencias (idasistencia, idactividad, idmiembro, estado) VALUES (UUID_SHORT(), ?, ?, ?)";
                $stmtIns = $this->conn->prepare($sqlInsert);
                
                foreach ($asistentes as $item) {
                    $stmtIns->execute([$idactividad, $item['id'], $item['estado']]);
                }
            }

            $this->conn->commit();
            return ["success" => true, "msg" => "Asistencia actualizada correctamente."];

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ["error" => "Error al guardar: " . $e->getMessage()];
        }
    }
}