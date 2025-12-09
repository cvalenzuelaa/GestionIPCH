<?php
require_once __DIR__ . '/../sesiones/session.php';
$sesionObj = new Session();
$usuarioData = $sesionObj->getSession();

require_once '../models/tesoreriaModel.php';

$accion = $_POST['accion'] ?? null;
$obj = new TesoreriaModel();
$idUsuario = $usuarioData['idusuario'] ?? null;

switch ($accion) {
    case 'getAll':
        echo json_encode($obj->getAll());
        break;

    case 'getBalance':
        echo json_encode($obj->getBalanceData());
        break;

    case 'insert':
        if (!$idUsuario) { echo json_encode(['error' => 'Sesión expirada.']); exit; }

        // Validación básica
        if (empty($_POST['tipo']) || empty($_POST['monto']) || empty($_POST['fecha'])) {
            echo json_encode(['error' => 'Faltan datos obligatorios.']);
            exit;
        }

        // --- MANEJO DE ARCHIVO (BOLETA/COMPROBANTE) ---
        $rutaArchivo = '';
        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            // Definir directorio: assets/uploads/tesoreria/
            $uploadDir = __DIR__ . '/../../assets/uploads/tesoreria/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('comprobante_') . '.' . $ext;
            $destino = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $destino)) {
                // Guardamos la ruta relativa para la BD
                $rutaArchivo = 'assets/uploads/tesoreria/' . $filename;
            } else {
                echo json_encode(['error' => 'Error al subir el archivo.']);
                exit;
            }
        }

        $datos = [
            'tipo' => $_POST['tipo'], // ingreso o gasto
            'categoria_tipo' => $_POST['categoria_tipo'], // Enum
            'categoria' => $_POST['categoria'], // Título corto
            'monto' => $_POST['monto'],
            'descripcion' => $_POST['descripcion'],
            'fecha' => $_POST['fecha'],
            'comprobante' => $rutaArchivo,
            'idusuario' => $idUsuario
        ];

        echo json_encode($obj->insert($datos));
        break;

    case 'delete':
        if (!$idUsuario) { echo json_encode(['error' => 'No autorizado.']); exit; }
        echo json_encode($obj->delete($_POST['idmovimiento']));
        break;
}