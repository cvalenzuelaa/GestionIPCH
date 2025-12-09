<?php
// Evitar cualquier salida antes del JSON
ob_start();

require_once './session.php';

$obj = new Session();
$response = $obj->logout();

// Limpiar cualquier salida no deseada
ob_end_clean();

// Establecer header correcto
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>