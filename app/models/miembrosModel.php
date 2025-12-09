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
        $sql = "SELECT * FROM miembros ORDER BY apellido ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($arr)
    {
        $sql = "SELECT * FROM miembros WHERE idmiembro = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($arr);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existeUnico($rut, $correo, $telefono)
    {
        $sql = "SELECT idmiembro FROM miembros WHERE rut = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$rut]);
        if ($stmt->fetch()) return ["error" => "El RUT ya está registrado.", "campo" => "rut"];

        if ($correo) {
            $sql = "SELECT idmiembro FROM miembros WHERE correo = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$correo]);
            if ($stmt->fetch()) return ["error" => "El correo ya está registrado.", "campo" => "correo"];
        }

        if ($telefono) {
            $sql = "SELECT idmiembro FROM miembros WHERE telefono = ?";
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
                        hoja_vida
                    ) 
                    VALUES (UUID_SHORT(), ?, ?, ?, ?, ?, ?, ?, ?, ?, '')";
            
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
                    WHERE idmiembro = ?";
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
            $sql = "DELETE FROM miembros WHERE idmiembro = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($arr);
            if ($stmt->rowCount() > 0) {
                return ["success" => "Miembro eliminado correctamente"];
            } else {
                return ["error" => "No se pudo eliminar."];
            }
        } catch (PDOException $e) {
            return ["error" => "Error: " . $e->getMessage()];
        }
    }
}