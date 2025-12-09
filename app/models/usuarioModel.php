<?php
require_once __DIR__ . '/../conexion/conexion.php';

class UsuariosModel
{
    private $conn = null;

    public function __construct()
    {
        $con = new Conexion();
        $this->conn = $con->getConexion();
    }

    // Login (Incluye es_alabanza)
    public function login($arr)
    {
        try {
            $sql = "SELECT * FROM usuarios WHERE correo = ? AND pass = ? AND estado = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($arr);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if(empty($result)) {
                return [['error' => 'Credenciales incorrectas o cuenta inactiva.']];
            }
            return $result;
        } catch (PDOException $e) {
            return [['error' => $e->getMessage()]];
        }
    }

    public function getAll() {
        $sql = "SELECT * FROM usuarios ORDER BY estado DESC, nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        try {
            $sql = "SELECT idusuario, nombre, apellido, correo, telefono, rol, avatar, es_alabanza, estado 
                    FROM usuarios WHERE idusuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { 
            error_log("Error en getById: " . $e->getMessage());
            return null; 
        }
    }

    // Insertar nuevo usuario (INCLUYE es_alabanza)
    public function insert($arr) {
        try {
            $sql = "INSERT INTO usuarios (idusuario, nombre, apellido, correo, telefono, pass, rol, es_alabanza, estado) 
                    VALUES (UUID_SHORT(), ?, ?, ?, ?, ?, ?, ?, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['nombre'], 
                $arr['apellido'], 
                $arr['correo'], 
                $arr['telefono'], 
                sha1($arr['pass']), 
                $arr['rol'],
                $arr['es_alabanza'] ?? 0
            ]);
            return ($stmt->rowCount() > 0) ? ["success" => "Usuario creado correctamente."] : ["error" => "No se pudo registrar."];
        } catch (PDOException $e) { return ["error" => $e->getMessage()]; }
    }

    public function softDelete($id) {
        $sql = "UPDATE usuarios SET estado = 0 WHERE idusuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function activate($id) {
        $sql = "UPDATE usuarios SET estado = 1 WHERE idusuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function updateRole($id, $rol) {
        $sql = "UPDATE usuarios SET rol = ? WHERE idusuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$rol, $id]);
        return $stmt->rowCount() > 0;
    }

    // NUEVO: Cambiar estado de miembro de alabanza
    public function updateAlabanzaStatus($id, $status) {
        $sql = "UPDATE usuarios SET es_alabanza = ? WHERE idusuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$status, $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateProfile($arr) {
        try {
            if (!empty($arr['avatar'])) {
                $sql = "UPDATE usuarios SET nombre=?, apellido=?, correo=?, telefono=?, avatar=? WHERE idusuario=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$arr['nombre'], $arr['apellido'], $arr['correo'], $arr['telefono'], $arr['avatar'], $arr['idusuario']]);
            } else {
                $sql = "UPDATE usuarios SET nombre=?, apellido=?, correo=?, telefono=? WHERE idusuario=?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$arr['nombre'], $arr['apellido'], $arr['correo'], $arr['telefono'], $arr['idusuario']]);
            }
            return $stmt->rowCount() >= 0;
        } catch (PDOException $e) { return false; }
    }

public function update($arr) {
    try {
        if (!empty($arr['pass'])) {
            // Con contraseña
            $sql = "UPDATE usuarios 
                    SET nombre=?, apellido=?, correo=?, telefono=?, pass=?, rol=?, es_alabanza=? 
                    WHERE idusuario=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['nombre'], 
                $arr['apellido'], 
                $arr['correo'], 
                $arr['telefono'], 
                sha1($arr['pass']), 
                $arr['rol'],
                $arr['es_alabanza'],
                $arr['idusuario']
            ]);
        } else {
            // Sin cambiar contraseña
            $sql = "UPDATE usuarios 
                    SET nombre=?, apellido=?, correo=?, telefono=?, rol=?, es_alabanza=? 
                    WHERE idusuario=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $arr['nombre'], 
                $arr['apellido'], 
                $arr['correo'], 
                $arr['telefono'], 
                $arr['rol'],
                $arr['es_alabanza'],
                $arr['idusuario']
            ]);
        }
        
        return $stmt->rowCount() >= 0; // Cambio: >= 0 en lugar de > 0
    } catch (PDOException $e) { 
        error_log("Error en update: " . $e->getMessage());
        return false; 
    }
}
    public function changePass($arr) {
        try {
            $sql = "UPDATE usuarios SET pass = ? WHERE idusuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($arr);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) { return false; }
    }
}
?>