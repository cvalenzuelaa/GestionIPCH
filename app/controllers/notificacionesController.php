<?php
require_once __DIR__ . '/../sesiones/session.php';
$sesionObj = new Session();
$usuarioData = $sesionObj->getSession();

require_once '../models/notificacionesModel.php';

$accion = $_POST['accion'] ?? null;
$obj = new NotificacionesModel();
$idUsuario = $usuarioData['idusuario'] ?? null;

if (!$idUsuario) { echo json_encode([]); exit; }

switch ($accion) {
    case 'check':
        // --- 1. GENERACIÓN AUTOMÁTICA DE ALERTAS (Cron Simulado) ---
        // (Mantenemos tu lógica existente de actividades y cumpleaños)
        $actividades = $obj->getActivitiesToday();
        foreach ($actividades as $act) {
            $titulo = "Actividad Hoy: " . $act['titulo'];
            if (!$obj->exists('actividad', $titulo, $idUsuario)) {
                $obj->insert('actividad', $titulo, "Recuerda: Hoy a las " . substr($act['hora_inicio'], 0, 5), $idUsuario);
            }
        }
        $cumples = $obj->getBirthdaysToday();
        foreach ($cumples as $cum) {
            $titulo = "Cumpleaños: " . $cum['nombre'] . " " . $cum['apellido'];
            if (!$obj->exists('cumpleanos', $titulo, $idUsuario)) {
                $obj->insert('cumpleanos', $titulo, "Hoy está de cumpleaños un miembro.", $idUsuario);
            }
        }

        // --- 2. DEVOLVER LISTA AL FRONTEND ---
        // Usamos el nuevo método que trae pendientes + leídas recientes
        $notificaciones = $obj->getUserNotifications($idUsuario);
        echo json_encode($notificaciones);
        break;

    case 'markRead':
        echo json_encode($obj->markAsRead($_POST['idnotificacion']));
        break;
}