<?php
require_once __DIR__ . '/../conexion/conexion.php';

class MiembrosModel
{
    private $conn = null;

    public function __construct()
    {
        $this->conn = new Conexion();
        $this->conn = $this->conn->getConexion();
    }

    public function getAll()
    {
        // Solo mostrar miembros activos
        $sql = "SELECT * FROM miembros WHERE activo = 1 ORDER BY apellido ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($arr)
    {
        $sql = "SELECT * FROM miembros WHERE idmiembro = ? AND activo = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($arr);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existeUnico($rut, $correo, $telefono)
    {
        // Validar solo entre miembros activos
        $sql = "SELECT idmiembro FROM miembros WHERE rut = ? AND activo = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$rut]);
        if ($stmt->fetch()) return ["error" => "El RUT ya está registrado.", "campo" => "rut"];

        if ($correo) {
            $sql = "SELECT idmiembro FROM miembros WHERE correo = ? AND activo = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$correo]);
            if ($stmt->fetch()) return ["error" => "El correo ya está registrado.", "campo" => "correo"];
        }

        if ($telefono) {
            $sql = "SELECT idmiembro FROM miembros WHERE telefono = ? AND activo = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$telefono]);
            if ($stmt->fetch()) return ["error" => "El teléfono ya está registrado.", "campo" => "telefono"];
        }

        return false;
    }

    public function insert($arr)
    {
        try {
            $sql = "INSERT INTO miembros (
                        idmiembro, nombre, apellido, rut, fecha_nacimiento, 
                        fecha_ingreso, direccion, correo, telefono, estado, 
                        hoja_vida, activo
                    ) 
                    VALUES (UUID_SHORT(), ?, ?, ?, ?, ?, ?, ?, ?, ?, '', 1)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($arr);

            if ($stmt->rowCount() > 0) {
                return array("success" => "Miembro registrado exitosamente.");
            } else {
                return array("error" => "No se pudo registrar el miembro.");
            }
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return array("error" => "El RUT ya existe en el sistema.", "campo" => "rut");
            }
            return array("error" => "Error SQL: " . $e->getMessage());
        }
    }

    public function update($arr)
    {
        try {
            $sql = "UPDATE miembros SET nombre = ?, apellido = ?, rut = ?, fecha_nacimiento = ?, fecha_ingreso = ?, direccion = ?, correo = ?, telefono = ?, estado = ? 
                    WHERE idmiembro = ? AND activo = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($arr);
            return array("success" => "Información actualizada correctamente.");
        } catch (PDOException $e) {
            return array("error" => "Error al actualizar: " . $e->getMessage());
        }
    }

    public function delete($arr)
    {
        try {
            // Soft Delete: marcar como inactivo en lugar de eliminar
            $sql = "UPDATE miembros SET activo = 0 WHERE idmiembro = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($arr);
            
            if ($stmt->rowCount() > 0) {
                return ["success" => "Miembro desactivado correctamente. Se preservó su historial."];
            } else {
                return ["error" => "No se pudo desactivar el miembro."];
            }
        } catch (PDOException $e) {
            return ["error" => "Error: " . $e->getMessage()];
        }
    }

    // Nueva función para ver miembros inactivos (opcional)
    public function getInactivos()
    {
        $sql = "SELECT * FROM miembros WHERE activo = 0 ORDER BY apellido ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nueva función para reactivar (opcional)
    public function reactivar($arr)
    {
        try {
            $sql = "UPDATE miembros SET activo = 1 WHERE idmiembro = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($arr);
            return ["success" => "Miembro reactivado correctamente."];
        } catch (PDOException $e) {
            return ["error" => "Error: " . $e->getMessage()];
        }
    }
}