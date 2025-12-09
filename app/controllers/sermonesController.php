<?php
require_once __DIR__ . '/../sesiones/session.php';
require_once '../models/sermonesModel.php';
require_once '../models/notificacionesModel.php';

$accion = $_POST['accion'] ?? null;
$obj = new SermonesModel();
$notifObj = new NotificacionesModel();
$sesion = new Session();
$uData = $sesion->getSession();

$esAdmin = ($uData['rol'] === 'admin' || $uData['rol'] === 'super');

function subirArchivo($file, $folder) {
    if (isset($_FILES[$file]) && $_FILES[$file]['error'] === UPLOAD_ERR_OK) {
        $dir = __DIR__ . '/../../assets/uploads/' . $folder . '/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $ext = pathinfo($_FILES[$file]['name'], PATHINFO_EXTENSION);
        $name = uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES[$file]['tmp_name'], $dir . $name)) {
            return 'assets/uploads/' . $folder . '/' . $name;
        }
    }
    return null;
}

switch ($accion) {
    case 'getSeries':
        echo json_encode($obj->getSeries());
        break;

    case 'getSermones':
        echo json_encode($obj->getSermonesBySerie($_POST['idserie']));
        break;

    case 'addSerie':
        if (!$esAdmin) { echo json_encode(['error' => 'No autorizado']); exit; }
        $img = subirArchivo('imagen', 'series_covers');
        $res = $obj->insertSerie([
            'titulo' => $_POST['titulo'],
            'desc' => $_POST['descripcion'],
            'fecha' => $_POST['fecha_inicio'],
            'img' => $img
        ]);
        
        // --- NOTIFICAR NUEVA SERIE ---
        if(isset($res['success'])) {
            $nombreUsuario = $uData['nombre'] . ' ' . $uData['apellido'];
            $notifObj->notifyAllUsers(
                'sermon',
                'Nueva Serie de Sermones',
                $nombreUsuario . ' inició: "' . $_POST['titulo'] . '"',
                $uData['idusuario']
            );
        }
        
        echo json_encode($res);
        break;

    case 'updateStatus':
        if (!$esAdmin) { echo json_encode(['error' => 'No autorizado']); exit; }
        $res = $obj->updateSerieStatus($_POST['idserie'], $_POST['estado']);
        echo json_encode(['success' => $res ? 'Estado actualizado.' : 'Error al actualizar.']);
        break;

    case 'addSermon':
        if (!$esAdmin) { echo json_encode(['error' => 'No autorizado']); exit; }
        $pdf = subirArchivo('pdf', 'sermones_notas');
        $res = $obj->insertSermon([
            'idserie' => $_POST['idserie'],
            'titulo' => $_POST['titulo'],
            'predicador' => $_POST['predicador'],
            'desc' => $_POST['descripcion'],
            'cita' => $_POST['cita'],
            'video' => $_POST['video'],
            'fecha' => $_POST['fecha'],
            'pdf' => $pdf
        ]);
        
        // --- NOTIFICAR NUEVO SERMÓN ---
        if(isset($res['success'])) {
            $notifObj->notifyAllUsers(
                'sermon',
                'Nuevo Sermón Publicado',
                '"' . $_POST['titulo'] . '" - ' . $_POST['predicador'],
                $uData['idusuario']
            );
        }
        
        echo json_encode($res);
        break;
        
    case 'deleteSermon':
        if (!$esAdmin) { echo json_encode(['error' => 'No autorizado']); exit; }
        echo json_encode(['success' => $obj->deleteSermon($_POST['idsermon'])]);
        break;
}
?>