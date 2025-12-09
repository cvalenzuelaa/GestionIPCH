<?php
require_once __DIR__ . '/../conexion/conexion.php';

class NotificacionesModel {
    private $conn;

    public function __construct() {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    public function getUserNotifications($idUsuario) {
        $sql = "SELECT * FROM notificaciones 
                WHERE idusuario_destino = ? 
                AND (
                    estado = 'pendiente' 
                    OR 
                    (estado = 'leida' AND fecha_lectura >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
                )
                ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($tipo, $titulo, $mensaje, $idDestino) {
        try {
            $sql = "INSERT INTO notificaciones (idnotificacion, tipo, titulo, mensaje, fecha_creacion, fecha_programada, estado, idusuario_destino) 
                    VALUES (UUID_SHORT(), ?, ?, ?, NOW(), NOW(), 'pendiente', ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$tipo, $titulo, $mensaje, $idDestino]);
            return true;
        } catch (PDOException $e) { return false; }
    }

    public function markAsRead($idNotificacion) {
        $sql = "UPDATE notificaciones SET estado = 'leida', fecha_lectura = NOW() WHERE idnotificacion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idNotificacion]);
        return $stmt->rowCount() > 0;
    }

    // MODIFICADO: Notificar solo a usuarios activos (puede incluir filtro de alabanza)
    public function notifyAllUsers($tipo, $titulo, $mensaje, $idUsuarioExcluir = null, $soloAlabanza = false) {
        $sql = "SELECT idusuario FROM usuarios WHERE estado = 1"; 
        
        // Si es notificación de alabanza, solo enviar a miembros del grupo
        if ($soloAlabanza) {
            $sql .= " AND es_alabanza = 1";
        }
        
        if ($idUsuarioExcluir) {
            $sql .= " AND idusuario != ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuarioExcluir]);
        } else {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
        
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($usuarios as $usr) {
            $this->insert($tipo, $titulo, $mensaje, $usr['idusuario']);
        }
    }

    public function notifyAdmins($tipo, $titulo, $mensaje) {
        $sql = "SELECT idusuario FROM usuarios WHERE rol IN ('admin', 'super') AND estado = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($admins as $adm) {
            $this->insert($tipo, $titulo, $mensaje, $adm['idusuario']);
        }
    }

    public function exists($tipo, $tituloLike, $idUsuario) {
        $sql = "SELECT idnotificacion FROM notificaciones 
                WHERE tipo = ? AND idusuario_destino = ? AND titulo = ? 
                AND DATE(fecha_creacion) = CURDATE()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tipo, $idUsuario, $tituloLike]);
        return $stmt->fetch();
    }
    
    public function getActivitiesToday() {
        $sql = "SELECT idactividad, titulo, hora_inicio FROM actividades 
                WHERE DATE(fecha) = CURDATE() AND estado != 'cancelada'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getBirthdaysToday() {
        $sql = "SELECT idmiembro, nombre, apellido FROM miembros 
                WHERE MONTH(fecha_nacimiento) = MONTH(CURDATE()) 
                AND DAY(fecha_nacimiento) = DAY(CURDATE())";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>