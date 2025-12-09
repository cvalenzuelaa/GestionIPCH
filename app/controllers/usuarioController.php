<?php
// filepath: c:\wamp64\www\GestionIPCH\app\controllers\usuarioController.php
ob_start();

try {
    require_once __DIR__ . '/../sesiones/session.php';
    require_once __DIR__ . '/../models/usuarioModel.php';

    $accion = $_POST['accion'] ?? null;
    
    if ($accion === null) { throw new Exception("Acción no definida."); }

    $obj = new UsuariosModel(); 
    $sesionObj = new Session();
    $uData = $sesionObj->getSession();
    $esAdmin = isset($uData['rol']) && ($uData['rol'] === 'admin' || $uData['rol'] === 'super');

    switch ($accion) {
        case 'login':
            if (empty($_POST['correo']) || empty($_POST['pass'])) { throw new Exception("Datos incompletos."); }
            $resultado = $obj->login([$_POST['correo'], sha1($_POST['pass'])]);
            
            if (empty($resultado) || isset($resultado[0]['error'])) {
                echo json_encode(["error" => $resultado[0]['error'] ?? "Credenciales incorrectas."]);
            } else {
                $u = $resultado[0];
                
                $sesionObj->login(
                    $u['idusuario'], $u['nombre'], $u['apellido'], $u['correo'], $u['telefono'], 
                    $u['rol'], $u['avatar'] ?? null, $u['es_alabanza'] ?? 0
                );
                
                echo json_encode(["success" => "Bienvenido", "rol" => $u['rol']]);
            }
            break;

        case 'getAll':
            if (!$esAdmin) throw new Exception('Acceso denegado.');
            echo json_encode($obj->getAll()); 
            break;


case 'insert':
    if (!$esAdmin) throw new Exception('Acceso denegado.');
    
    $idUsuario = $_POST['idusuario'] ?? null;
    
    // Si tiene ID, es EDICIÓN
    if (!empty($idUsuario)) {
        $datos = [
            'idusuario' => $idUsuario,
            'nombre' => $_POST['nombre'],
            'apellido' => $_POST['apellido'],
            'correo' => $_POST['correo'],
            'telefono' => $_POST['telefono'],
            'rol' => $_POST['rol'],
            'es_alabanza' => isset($_POST['es_alabanza']) ? 1 : 0
        ];
        
        // Si envió contraseña, actualizarla
        if (!empty($_POST['pass'])) {
            $datos['pass'] = $_POST['pass'];
        }
        
        $resultado = $obj->update($datos);
        echo json_encode($resultado ? ['success' => 'Usuario actualizado correctamente'] : ['error' => 'Error al actualizar']);
        
    } else {
        // Si NO tiene ID, es NUEVO USUARIO
        if(empty($_POST['pass'])) throw new Exception('La contraseña es obligatoria');

        $datos = [
            'nombre' => $_POST['nombre'],
            'apellido' => $_POST['apellido'],
            'correo' => $_POST['correo'],
            'telefono' => $_POST['telefono'],
            'pass' => $_POST['pass'],
            'rol' => $_POST['rol'],
            'es_alabanza' => isset($_POST['es_alabanza']) ? 1 : 0
        ];
        echo json_encode($obj->insert($datos));
    }
    break;

        case 'updateAlabanzaStatus':
            if (!$esAdmin) throw new Exception('Acceso denegado.');
            $status = isset($_POST['es_alabanza']) ? 1 : 0;
            $res = $obj->updateAlabanzaStatus($_POST['idusuario'], $status);
            echo json_encode($res ? ['success' => 'Estado actualizado.'] : ['error' => 'Error.']);
            break;

        case 'softDelete':
            if (!$esAdmin) throw new Exception('Acceso denegado.');
            if ($_POST['idusuario'] == $uData['idusuario']) throw new Exception('No puedes darte de baja a ti mismo.');
            
            $res = $obj->softDelete($_POST['idusuario']);
            echo json_encode($res ? ['success' => 'Usuario dado de baja.'] : ['error' => 'Error.']);
            break;

        case 'activate':
            if (!$esAdmin) throw new Exception('Acceso denegado.');
            $res = $obj->activate($_POST['idusuario']);
            echo json_encode($res ? ['success' => 'Usuario reactivado.'] : ['error' => 'Error.']);
            break;

        case 'changeRole': 
            if ($uData['rol'] !== 'admin') throw new Exception('Solo el Administrador Principal puede cambiar roles.');
            
            $res = $obj->updateRole($_POST['idusuario'], $_POST['rol']);
            
            echo json_encode($res ? ['success' => 'Rol actualizado.'] : ['error' => 'Error al cambiar rol.']);
            break;
            
        case 'updateProfile':
            // VERIFICAR SESIÓN
            if (!isset($uData['idusuario'])) {
                throw new Exception('Sesión no válida');
            }

            // VALIDAR CAMPOS
            if (empty($_POST['nombre']) || empty($_POST['apellido']) || empty($_POST['correo']) || empty($_POST['telefono'])) {
                throw new Exception('Todos los campos son obligatorios');
            }

            $idUsuario = $_POST['idusuario'] ?? $uData['idusuario'];

            // VERIFICAR PERMISOS
            if ($idUsuario != $uData['idusuario'] && $uData['rol'] !== 'admin') {
                throw new Exception('No tienes permiso para editar este perfil');
            }

            $datos = [
                'idusuario' => $idUsuario,
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'correo' => trim($_POST['correo']),
                'telefono' => trim($_POST['telefono'])
            ];

            // MANEJO DE AVATAR
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                
                // Verificar que es una imagen válida
                $imageInfo = getimagesize($_FILES['avatar']['tmp_name']);
                if ($imageInfo === false) {
                    throw new Exception('El archivo no es una imagen válida');
                }

                $uploadsDir = __DIR__ . '/../../assets/uploads/avatars/';
                
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }

                $extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($extension, $allowedExtensions)) {
                    throw new Exception('Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.');
                }

                // Eliminar avatar anterior si existe
                $usuarioActual = $obj->getById($idUsuario);
                if (!empty($usuarioActual['avatar'])) {
                    $avatarAnterior = __DIR__ . '/../../' . $usuarioActual['avatar'];
                    if (file_exists($avatarAnterior)) {
                        @unlink($avatarAnterior);
                    }
                }

                $nombreArchivo = 'avatar_' . $idUsuario . '_' . time() . '.' . $extension;
                $rutaCompleta = $uploadsDir . $nombreArchivo;
                
                if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $rutaCompleta)) {
                    throw new Exception('Error al subir el archivo. Verifica permisos de la carpeta.');
                }

                $datos['avatar'] = 'assets/uploads/avatars/' . $nombreArchivo;
            }

            // ACTUALIZAR EN BASE DE DATOS
            $resultado = $obj->updateProfile($datos);
            
            if ($resultado) {
                // ACTUALIZAR SESIÓN
                if ($idUsuario == $uData['idusuario']) {
                    $usuarioActualizado = $obj->getById($idUsuario);
                    if ($usuarioActualizado) {
                        $sesionObj->login(
                            $usuarioActualizado['idusuario'],
                            $usuarioActualizado['nombre'],
                            $usuarioActualizado['apellido'],
                            $usuarioActualizado['correo'],
                            $usuarioActualizado['telefono'],
                            $usuarioActualizado['rol'],
                            $usuarioActualizado['avatar'] ?? null,
                            $usuarioActualizado['es_alabanza'] ?? 0
                        );
                    }
                }
                
                ob_clean();
                echo json_encode(['success' => 'Perfil actualizado correctamente']);
            } else {
                throw new Exception('Error al actualizar el perfil en la base de datos');
            }
            break;

        case 'changePassProfile':
            if (!isset($uData['idusuario'])) {
                throw new Exception('Sesión no válida');
            }

            if (empty($_POST['pass_actual']) || empty($_POST['pass_nueva'])) {
                throw new Exception('Debes completar todos los campos');
            }

            $idUsuario = $_POST['idusuario'] ?? $uData['idusuario'];

            // Verificar contraseña actual
            $usuarioActual = $obj->getById($idUsuario);
            
            if (!$usuarioActual || $usuarioActual['pass'] !== sha1($_POST['pass_actual'])) {
                throw new Exception('La contraseña actual es incorrecta');
            }

            // Cambiar contraseña
            $resultado = $obj->changePass([sha1($_POST['pass_nueva']), $idUsuario]);
            
            if ($resultado) {
                ob_clean();
                echo json_encode(['success' => 'Contraseña actualizada correctamente']);
            } else {
                throw new Exception('Error al cambiar la contraseña');
            }
            break;
            
        default:
            throw new Exception("Acción no válida.");
    }

} catch (Exception $e) {
    ob_clean();
    http_response_code(200); // Mantener código 200 para que JS pueda leer el JSON
    echo json_encode(["error" => $e->getMessage()]);
}

ob_end_flush();
?>