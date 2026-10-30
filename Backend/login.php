<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// Obtener los datos JSON enviados por fetch
$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Faltan completar campos obligatorios."]);
    exit();
}

try {
    // 1. Buscar primero en la tabla USUARIO (para el Administrador y personal)
    $stmtAdmin = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
    $stmtAdmin->execute([$email]);
    $usuario = $stmtAdmin->fetch();

    if ($usuario) {
        // Verificar contraseña del administrador
        if (password_verify($password, $usuario['contrasena'])) {
            echo json_encode([
                "status" => "success",
                "message" => "¡Bienvenido, Administrador " . $usuario['nombre'] . "!",
                "nombre" => $usuario['nombre'],
                "rol" => "admin"
            ]);
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
            exit();
        }
    }

    // 2. Si no es un usuario/admin, buscar en la tabla CLIENTE
    $stmtCliente = $pdo->prepare("SELECT * FROM cliente WHERE email = ?");
    $stmtCliente->execute([$email]);
    $user = $stmtCliente->fetch();

    if ($user) {
        // Verificar si la contraseña del cliente coincide
        if (password_verify($password, $user['contrasena'])) {
            echo json_encode([
                "status" => "success", 
                "message" => "¡Bienvenido de nuevo, " . $user['nombre'] . "!",
                "nombre" => $user['nombre'],
                "rol" => "cliente"
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
        }
    } else {
        // Correo no encontrado en ninguna de las dos tablas
        echo json_encode(["status" => "error", "message" => "No existe una cuenta registrada con ese correo."]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
}
?>