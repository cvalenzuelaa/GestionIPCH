<?php
require_once __DIR__ . '/../sesiones/session.php';
$sesionObj = new Session();
$usuarioData = $sesionObj->getSession();

require_once '../models/oracionesModel.php';
require_once '../models/notificacionesModel.php';

$accion = $_POST['accion'] ?? null;
$obj = new OracionesModel();
$notifObj = new NotificacionesModel();
$idUsuario = $usuarioData['idusuario'] ?? null;
$rolUsuario = $usuarioData['rol'] ?? 'usuario';

switch ($accion) {
    case 'getAll':
        // ADMIN y SUPER ven todas
        if ($rolUsuario === 'admin' || $rolUsuario === 'super') {
            echo json_encode($obj->getAll());
        } else {
            // USUARIO normal ve SOLO las suyas
            echo json_encode($obj->getByUser($idUsuario));
        }
        break;

    // NUEVO: Muro Público
    case 'getApproved':
        echo json_encode($obj->getApproved());
        break;

    case 'insert':
        if (!$idUsuario) { echo json_encode(['error' => 'Sesión no válida.']); exit; }
        
        if (empty($_POST['descripcion'])) { echo json_encode(['error' => 'Escribe tu petición.']); exit; }

        // Solo el ADMIN puede publicar directo
        $estadoInicial = ($rolUsuario === 'admin') ? 'aprobada' : 'pendiente';

        $res = $obj->insert([
            'descripcion' => $_POST['descripcion'],
            'estado' => $estadoInicial,
            'idusuario' => $idUsuario
        ]);

        // Notificar a admins si es pendiente
        if (isset($res['success']) && $estadoInicial === 'pendiente') {
            $nombre = $usuarioData['nombre'] . ' ' . $usuarioData['apellido'];
            $rolTexto = $rolUsuario === 'super' ? 'Ministro' : 'Miembro';
            
            $notifObj->notifyAdmins(
                'oracion', 
                'Nueva Petición de Oración', 
                $nombre . ' (' . $rolTexto . ') envió una petición que requiere aprobación.'
            );
        }

        echo json_encode($res);
        break;

    case 'cambiarEstado':
        if ($rolUsuario !== 'admin') {
            echo json_encode(['error' => 'Solo el Pastor puede aprobar/rechazar peticiones.']);
            exit;
        }
        
        $res = $obj->updateStatus($_POST['idoracion'], $_POST['estado']);
        
        if ($res && $_POST['estado'] === 'aprobada') {
            $oracion = $obj->getById($_POST['idoracion']);
            if ($oracion) {
                $notifObj->insert(
                    'oracion',
                    'Petición Aprobada',
                    'Tu petición de oración ha sido aprobada y compartida con la congregación.',
                    $oracion['idusuario_oracion']
                );
            }
        }
        
        echo json_encode($res ? ['success' => 'Estado actualizado.'] : ['error' => 'No se pudo actualizar.']);
        break;

    case 'delete':
        if ($rolUsuario !== 'admin') { 
            echo json_encode(['error' => 'Solo el Pastor puede eliminar.']); 
            exit; 
        }
        echo json_encode($obj->delete($_POST['idoracion']) ? ['success' => 'Eliminado'] : ['error' => 'Error al eliminar']);
        break;
}
?>