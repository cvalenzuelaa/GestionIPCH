<?php

require_once __DIR__ . '/../models/documentosModel.php';
require_once __DIR__ . '/../models/notificacionesModel.php';

class DocumentosController {
    private $model;
    private $notificacionesModel;

    public function __construct() {
        $this->model = new DocumentosModel();
        $this->notificacionesModel = new NotificacionesModel();
    }

    public function getDocumentos() {
        if (!isset($_SESSION['usuario'])) {
            return json_encode(['error' => 'Usuario no autenticado']);
        }

        $esAdmin = $_SESSION['usuario']['rol'] === 'admin';
        $documentos = $this->model->getDocumentos($esAdmin);

        return json_encode(['success' => true, 'data' => $documentos]);
    }

    public function subirDocumento() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            return json_encode(['error' => 'No tiene permisos para subir documentos']);
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            return json_encode(['error' => 'Error al subir el archivo']);
        }

        try {
            $archivo = $_FILES['archivo'];
            $tipo = $_POST['tipo'] ?? '';
            $titulo = $_POST['titulo'] ?? '';
            $publico = isset($_POST['publico']) ? 1 : 0;

            // Validar tipo de archivo
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $permitidos = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
            
            if (!in_array($extension, $permitidos)) {
                return json_encode(['error' => 'Tipo de archivo no permitido']);
            }

            // Generar nombre único
            $nombreArchivo = uniqid() . '_' . $archivo['name'];
            $rutaArchivo = 'documentos/' . $nombreArchivo;

            // Crear directorio si no existe
            if (!is_dir('documentos')) {
                mkdir('documentos', 0777, true);
            }

            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                $resultado = $this->model->insert([
                    'titulo' => $titulo,
                    'tipo' => $tipo,
                    'archivo' => $rutaArchivo,
                    'publico' => $publico,
                    'idusuario_subida' => $_SESSION['usuario']['idusuario']
                ]);

                if ($resultado['success']) {
                    // Notificar si el documento es público
                    if ($publico) {
                        $this->notificacionesModel->create([
                            'tipo' => 'documento',
                            'titulo' => 'Nuevo Documento Disponible',
                            'mensaje' => "Se ha publicado un nuevo documento: {$titulo}",
                            'rol_destino' => 'all'
                        ]);
                    }
                }

                return json_encode($resultado);
            }

            return json_encode(['error' => 'Error al guardar el archivo']);
        } catch (Exception $e) {
            return json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function eliminarDocumento() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            return json_encode(['error' => 'No tiene permisos para eliminar documentos']);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['iddocumento'])) {
            return json_encode(['error' => 'ID de documento no proporcionado']);
        }

        try {
            // Obtener información del documento
            $documento = $this->model->getById($data['iddocumento']);
            
            if ($documento && file_exists($documento['archivo'])) {
                unlink($documento['archivo']); // Eliminar archivo físico
            }

            $resultado = $this->model->delete($data['iddocumento']);
            return json_encode($resultado);
        } catch (Exception $e) {
            return json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function actualizarDocumento() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            return json_encode(['error' => 'No tiene permisos para actualizar documentos']);
        }

        try {
            $data = [];
            if (isset($_POST['iddocumento'])) {
                $data['iddocumento'] = $_POST['iddocumento'];
            } else {
                return json_encode(['error' => 'ID de documento no proporcionado']);
            }

            if (isset($_POST['titulo'])) {
                $data['titulo'] = $_POST['titulo'];
            }
            if (isset($_POST['tipo'])) {
                $data['tipo'] = $_POST['tipo'];
            }
            if (isset($_POST['publico'])) {
                $data['publico'] = $_POST['publico'];
            }

            // Si hay un nuevo archivo
            if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                $archivo = $_FILES['archivo'];
                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $permitidos = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
                
                if (!in_array($extension, $permitidos)) {
                    return json_encode(['error' => 'Tipo de archivo no permitido']);
                }

                // Eliminar archivo anterior
                $documentoAnterior = $this->model->getById($data['iddocumento']);
                if ($documentoAnterior && file_exists($documentoAnterior['archivo'])) {
                    unlink($documentoAnterior['archivo']);
                }

                // Guardar nuevo archivo
                $nombreArchivo = uniqid() . '_' . $archivo['name'];
                $rutaArchivo = 'documentos/' . $nombreArchivo;
                
                if (move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                    $data['archivo'] = $rutaArchivo;
                }
            }

            $resultado = $this->model->update($data);
            return json_encode($resultado);
        } catch (Exception $e) {
            return json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function buscarDocumentos() {
        if (!isset($_SESSION['usuario'])) {
            return json_encode(['error' => 'Usuario no autenticado']);
        }

        if (!isset($_GET['q'])) {
            return json_encode(['error' => 'Término de búsqueda no proporcionado']);
        }

        try {
            $esAdmin = $_SESSION['usuario']['rol'] === 'admin';
            $resultados = $this->model->buscar($_GET['q'], $esAdmin);
            return json_encode(['success' => true, 'data' => $resultados]);
        } catch (Exception $e) {
            return json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }
}