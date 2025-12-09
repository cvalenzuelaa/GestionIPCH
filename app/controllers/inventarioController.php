<?php
require_once __DIR__ . '/../sesiones/session.php';
$sesionObj = new Session();
$usuarioData = $sesionObj->getSession();

require_once '../models/inventarioModel.php';

$accion = $_POST['accion'] ?? null;
$obj = new InventarioModel();
$idUsuario = $usuarioData['idusuario'] ?? null;

switch ($accion) {
    case 'getAll':
        echo json_encode($obj->getAll());
        break;

    case 'insert':
        if (!$idUsuario) { echo json_encode(['error' => 'Sesión expirada.']); exit; }

        if (empty($_POST['descripcion']) || empty($_POST['monto']) || empty($_POST['fecha'])) {
            echo json_encode(['error' => 'Faltan datos obligatorios.']);
            exit;
        }

        // --- SUBIDA DE ARCHIVO ---
        $rutaArchivo = '';
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../assets/uploads/inventario/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('bien_') . '.' . $ext;
            
            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $uploadDir . $filename)) {
                $rutaArchivo = 'assets/uploads/inventario/' . $filename;
            }
        }

        echo json_encode($obj->insert([
            'descripcion' => $_POST['descripcion'],
            'fecha' => $_POST['fecha'],
            'monto' => $_POST['monto'],
            'archivo' => $rutaArchivo,
            'idusuario' => $idUsuario
        ]));
        break;

    case 'delete':
        if (!$idUsuario) { echo json_encode(['error' => 'No autorizado.']); exit; }
        echo json_encode($obj->delete($_POST['idbien']));
        break;
}