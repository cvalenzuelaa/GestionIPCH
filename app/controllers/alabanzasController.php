<?php
require_once __DIR__ . '/../sesiones/session.php';
$sesionObj = new Session();
$uData = $sesionObj->getSession();

require_once '../models/alabanzasModel.php';
require_once '../models/notificacionesModel.php';

$accion = $_POST['accion'] ?? null;
$obj = new AlabanzasModel();
$notifObj = new NotificacionesModel();
$idUsuario = $uData['idusuario'] ?? null;

function subirArchivo($fileInput, $prefijo, $carpeta) {
    if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK) {
        $dir = __DIR__ . '/../../assets/uploads/' . $carpeta . '/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        
        $ext = pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION);
        $name = uniqid($prefijo) . '.' . $ext;
        
        if (move_uploaded_file($_FILES[$fileInput]['tmp_name'], $dir . $name)) {
            return 'assets/uploads/' . $carpeta . '/' . $name;
        }
    }
    return null;
}

switch ($accion) {
    case 'getAll':
        echo json_encode($obj->getAll());
        break;

    case 'getById':
        echo json_encode($obj->getById($_POST['idalabanza']));
        break;

    case 'insert':
    case 'update':
        if (!$idUsuario) { echo json_encode(['error' => 'Sesión inválida']); exit; }

        $pathPDF = subirArchivo('file_pdf', 'partitura_', 'alabanzas');
        $pathPPT = subirArchivo('file_ppt', 'presentacion_', 'alabanzas');

        $datos = [
            'titulo' => $_POST['titulo'],
            'video' => $_POST['enlace_video'],
            'pdf' => $pathPDF,
            'ppt' => $pathPPT,
            'idusuario' => $idUsuario
        ];

        if ($accion == 'insert') {
            $res = $obj->insert($datos);
            
            // --- CAMBIO: NOTIFICAR SOLO A MIEMBROS DEL GRUPO DE ALABANZA ---
            if(isset($res['success'])) {
                $nombreUsuario = $uData['nombre'] . ' ' . $uData['apellido'];
                $notifObj->notifyAllUsers(
                    'alabanza', 
                    'Nueva Alabanza Disponible', 
                    $nombreUsuario . ' subió: "' . $_POST['titulo'] . '"', 
                    $idUsuario, // Excluir al que la subió
                    true // TRUE = Solo enviar a es_alabanza = 1
                );
            }
            echo json_encode($res);
        } else {
            $datos['idalabanza'] = $_POST['idalabanza'];
            echo json_encode($obj->update($datos));
        }
        break;

    case 'delete':
        echo json_encode($obj->delete($_POST['idalabanza']));
        break;
}
?>