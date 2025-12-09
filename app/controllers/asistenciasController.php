<?php
require_once '../models/asistenciasModel.php';

$accion = $_POST['accion'] ?? null;
$obj = new AsistenciasController();

if ($accion === 'getEstadisticas') {
    if (empty($_POST['idmiembro'])) {
        echo json_encode(['error' => 'Falta ID']);
        exit;
    }
    
    // Gráfico + Detalles
    $grafico = $obj->getEstadisticasPorMiembro([$_POST['idmiembro']]);
    $detalle = $obj->getDetalleAsistencias([$_POST['idmiembro']]);
    
    if (empty($grafico) && empty($detalle)) {
        echo json_encode(['vacio' => true]);
    } else {
        echo json_encode(['success' => true, 'grafico' => $grafico, 'detalle' => $detalle]);
    }
}

class AsistenciasController {
    private $model;
    public function __construct() { $this->model = new AsistenciasModel(); }
    public function getEstadisticasPorMiembro($arr) { return $this->model->getEstadisticasPorMiembro($arr); }
    public function getDetalleAsistencias($arr) { return $this->model->getDetalleAsistencias($arr); }
}