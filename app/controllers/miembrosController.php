<?php
require_once '../models/miembrosModel.php';

$accion = $_POST['accion'] ?? null;
if ($accion == null) {
    echo json_encode(array("error" => "No se ha recibido la acción."));
    exit;
}

$obj = new MiembrosController();

switch ($accion) {
    case 'getAll':
        echo json_encode($obj->getAll());
        break;

    case 'getById':
        if (!isset($_POST['idmiembro'])) {
            echo json_encode(array("error" => "Falta ID."));
            exit;
        }
        echo json_encode($obj->getById([$_POST['idmiembro']]));
        break;

    case 'insert':
        if (empty($_POST['nombre']) || empty($_POST['rut']) || empty($_POST['estado'])) {
            echo json_encode(array("error" => "Faltan datos obligatorios (Nombre, RUT, Estado)."));
            exit;
        }

        $existe = $obj->existeUnico($_POST['rut'], $_POST['correo'], $_POST['telefono']);
        if ($existe) {
            echo json_encode($existe);
            exit;
        }

        echo json_encode($obj->insert([
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['rut'],
            $_POST['fecha_nacimiento'],
            $_POST['fecha_ingreso'],
            $_POST['direccion'],
            $_POST['correo'],
            $_POST['telefono'],
            $_POST['estado']
        ]));
        break;

    case 'update':
        echo json_encode($obj->update([
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['rut'],
            $_POST['fecha_nacimiento'],
            $_POST['fecha_ingreso'],
            $_POST['direccion'],
            $_POST['correo'],
            $_POST['telefono'],
            $_POST['estado'],
            $_POST['idmiembro']
        ]));
        break;

    case 'delete':
        echo json_encode($obj->delete([$_POST['idmiembro']]));
        break;
        
    case 'getInactivos':
        echo json_encode($obj->getInactivos());
        break;
    
    case 'reactivar':
        if (!isset($_POST['idmiembro'])) {
            echo json_encode(array("error" => "Falta ID."));
            exit;
        }
        echo json_encode($obj->reactivar([$_POST['idmiembro']]));
        break;
}

class MiembrosController
{
    private $model;
    public function __construct() { $this->model = new MiembrosModel(); }
    public function getAll() { return $this->model->getAll(); }
    public function getById($arr) { return $this->model->getById($arr); }
    public function insert($arr) { return $this->model->insert($arr); }
    public function update($arr) { return $this->model->update($arr); }
    public function delete($arr) { return $this->model->delete($arr); }
    public function getInactivos() { 
        return $this->model->getInactivos(); 
    }
    
    public function reactivar($arr) { 
        return $this->model->reactivar($arr); 
    }

    public function existeUnico($rut, $correo, $telefono) {
        $res = $this->model->existeUnico($rut, $correo, $telefono);
        if ($res) return $res;
        return false;
    }
}