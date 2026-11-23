<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// Obtener los datos JSON enviados por fetch
$data = json_decode(file_get_contents("php://input"), true);

$identificador = trim($data['email'] ?? $data['usuario'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($identificador) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Faltan completar campos obligatorios."], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    // 1. Buscar primero en la tabla USUARIO por email, nombre o nombre completo (para el Administrador y personal)
    $stmtAdmin = $pdo->prepare("SELECT * FROM usuario 
        WHERE LOWER(TRIM(email)) = LOWER(TRIM(?)) 
           OR LOWER(TRIM(nombre)) = LOWER(TRIM(?)) 
           OR LOWER(TRIM(CONCAT(nombre, ' ', apellido))) = LOWER(TRIM(?)) 
        LIMIT 1");
    $stmtAdmin->execute([$identificador, $identificador, $identificador]);
    $usuario = $stmtAdmin->fetch();

    if ($usuario) {
        // Verificar contraseña del administrador/usuario
        if (password_verify($password, $usuario['contrasena'])) {
            // Guardar los datos del usuario en la sesión de PHP
            $_SESSION['usuario'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol']; // 'Administrador'
            $_SESSION['nombre'] = $usuario['nombre'];

            echo json_encode([
                "status" => "success",
                "message" => "¡Bienvenido, Administrador " . $usuario['nombre'] . "!",
                "nombre" => $usuario['nombre'],
                "email" => $usuario['email'],
                "rol" => $usuario['rol'] // Enviamos el rol real devuelto por la BD
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    // 2. Si no es un usuario/admin, buscar en la tabla CLIENTE por email, nombre o nombre completo
    $stmtCliente = $pdo->prepare("SELECT * FROM cliente 
        WHERE LOWER(TRIM(email)) = LOWER(TRIM(?)) 
           OR LOWER(TRIM(nombre)) = LOWER(TRIM(?)) 
           OR LOWER(TRIM(CONCAT(nombre, ' ', apellido))) = LOWER(TRIM(?)) 
        LIMIT 1");
    $stmtCliente->execute([$identificador, $identificador, $identificador]);
    $user = $stmtCliente->fetch();

    if ($user) {
        // Verificar si la contraseña del cliente coincide
        if (password_verify($password, $user['contrasena'])) {
            echo json_encode([
                "status" => "success", 
                "message" => "¡Bienvenido de nuevo, " . $user['nombre'] . "!",
                "nombre" => $user['nombre'],
                "email" => $user['email'],
                "rol" => "cliente"
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."], JSON_UNESCAPED_UNICODE);
        }
    } else {
        // Nombre o correo no encontrado en ninguna de las dos tablas
        echo json_encode(["status" => "error", "message" => "No existe una cuenta registrada con ese nombre o correo."], JSON_UNESCAPED_UNICODE);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>