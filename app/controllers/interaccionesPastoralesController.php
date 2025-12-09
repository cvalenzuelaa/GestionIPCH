<?php
require_once '../models/interaccionesPastoralesModel.php';

$accion = $_POST['accion'] ?? null;
if ($accion == null) {
    echo json_encode(array("error" => "Acción indefinida."));
    exit;
}

$obj = new InteraccionesPastoralesController();

switch ($accion) {
    case 'getByMiembro':
        if (!isset($_POST['idmiembro'])) {
            echo json_encode(['error' => 'Falta idmiembro']);
            exit;
        }
        echo json_encode($obj->getByMiembro([$_POST['idmiembro']]));
        break;

    case 'insert':
        // 1. Recibimos el ID del usuario responsable desde el input hidden del formulario
        $idUsuario = $_POST['idusuario_registro'] ?? null;

        if (empty($idUsuario)) {
            echo json_encode(['error' => 'Error: No se identificó al usuario responsable. Recarga la página.']);
            exit;
        }

        // 2. Validamos el resto de datos
        if (empty($_POST['idmiembro']) || empty($_POST['tipo'])) {
            echo json_encode(['error' => 'Por favor complete los campos obligatorios.']);
            exit;
        }
        
        // 3. Insertamos
        echo json_encode($obj->insert([
            $_POST['idmiembro'],
            $_POST['tipo'],
            $_POST['descripcion'],
            $idUsuario
        ]));
        break;
}

class InteraccionesPastoralesController
{
    private $model;
    public function __construct() { 
        $this->model = new InteraccionesPastoralesModel(); 
    }
    public function getByMiembro($arr) { return $this->model->getByMiembro($arr); }
    public function insert($arr) { return $this->model->insert($arr); }
}