<?php

require_once __DIR__ . '/../conexion/conexion.php';

class DocumentosModel {
    private $conn = null;

    public function __construct() {
        $this->conn = new Conexion();
        $this->conn = $this->conn->getConexion();
    }

    public function getDocumentos($incluirPrivados = false) {
        try {
            $sql = "SELECT d.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido 
                    FROM documentos d
                    LEFT JOIN usuarios u ON d.idusuario_subida = u.idusuario
                    WHERE 1=1";
            
            if (!$incluirPrivados) {
                $sql .= " AND d.publico = 1";
            }
            
            $sql .= " ORDER BY d.fecha_subida DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener documentos: " . $e->getMessage());
        }
    }

    public function insert($data) {
        try {
            $sql = "INSERT INTO documentos (
                        iddocumento, titulo, tipo, archivo, publico, 
                        fecha_subida, idusuario_subida
                    ) VALUES (
                        UUID_SHORT(), ?, ?, ?, ?, NOW(), ?
                    )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data['titulo'],
                $data['tipo'],
                $data['archivo'],
                $data['publico'],
                $data['idusuario_subida']
            ]);

            if ($stmt->rowCount() > 0) {
                return ["success" => true, "message" => "Documento guardado correctamente"];
            }
            return ["error" => "No se pudo guardar el documento"];
        } catch (PDOException $e) {
            throw new Exception("Error al insertar documento: " . $e->getMessage());
        }
    }

    public function update($data) {
        try {
            $campos = [];
            $valores = [];

            if (isset($data['titulo'])) {
                $campos[] = "titulo = ?";
                $valores[] = $data['titulo'];
            }
            if (isset($data['tipo'])) {
                $campos[] = "tipo = ?";
                $valores[] = $data['tipo'];
            }
            if (isset($data['archivo'])) {
                $campos[] = "archivo = ?";
                $valores[] = $data['archivo'];
            }
            if (isset($data['publico'])) {
                $campos[] = "publico = ?";
                $valores[] = $data['publico'];
            }

            if (empty($campos)) {
                return ["error" => "No hay campos para actualizar"];
            }

            $valores[] = $data['iddocumento'];
            
            $sql = "UPDATE documentos SET " . implode(", ", $campos) . " WHERE iddocumento = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($valores);

            if ($stmt->rowCount() > 0) {
                return ["success" => true, "message" => "Documento actualizado correctamente"];
            }
            return ["error" => "No se realizaron cambios en el documento"];
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar documento: " . $e->getMessage());
        }
    }

    public function delete($iddocumento) {
        try {
            $sql = "DELETE FROM documentos WHERE iddocumento = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$iddocumento]);

            if ($stmt->rowCount() > 0) {
                return ["success" => true, "message" => "Documento eliminado correctamente"];
            }
            return ["error" => "No se pudo eliminar el documento"];
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar documento: " . $e->getMessage());
        }
    }

    public function getById($iddocumento) {
        try {
            $sql = "SELECT d.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido 
                    FROM documentos d
                    LEFT JOIN usuarios u ON d.idusuario_subida = u.idusuario
                    WHERE d.iddocumento = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$iddocumento]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener documento: " . $e->getMessage());
        }
    }

    public function buscar($termino, $incluirPrivados = false) {
        try {
            $sql = "SELECT d.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido 
                    FROM documentos d
                    LEFT JOIN usuarios u ON d.idusuario_subida = u.idusuario
                    WHERE (d.titulo LIKE ? OR d.tipo LIKE ?)";
            
            if (!$incluirPrivados) {
                $sql .= " AND d.publico = 1";
            }
            
            $sql .= " ORDER BY d.fecha_subida DESC";

            $termino = "%{$termino}%";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$termino, $termino]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al buscar documentos: " . $e->getMessage());
        }
    }
}