<?php
// Inicia o reanuda la sesión PHP en el servidor para almacenar variables de sesión autenticado
session_start();

// Configura la cabecera HTTP para devolver respuestas en formato JSON con codificación UTF-8
header('Content-Type: application/json; charset=utf-8');

// Incluye la conexión PDO ($pdo) a la base de datos salon_eventos_syl
require_once 'conexion.php';

// Lee el cuerpo JSON enviado por fetch desde JavaScript y lo convierte en un array asociativo
$data = json_decode(file_get_contents("php://input"), true);

// Obtiene el identificador (acepta clave 'email' o 'usuario') y la contraseña limpiando espacios extremos
$identificador = trim($data['email'] ?? $data['usuario'] ?? '');
$password = trim($data['password'] ?? '');

// Valida que tanto el identificador como la contraseña no estén vacíos
if (empty($identificador) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Faltan completar campos obligatorios."], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    // 1. Busca primero en la tabla `usuario` (administrador/personal) por email, nombre o nombre completo
    $stmtAdmin = $pdo->prepare("SELECT * FROM usuario 
        WHERE LOWER(TRIM(email)) = LOWER(TRIM(?)) 
           OR LOWER(TRIM(nombre)) = LOWER(TRIM(?)) 
           OR LOWER(TRIM(CONCAT(nombre, ' ', apellido))) = LOWER(TRIM(?)) 
        LIMIT 1");
    $stmtAdmin->execute([$identificador, $identificador, $identificador]);
    $usuario = $stmtAdmin->fetch();

    if ($usuario) {
        // Verifica la contraseña ingresada contra el hash almacenado en la base de datos
        if (password_verify($password, $usuario['contrasena'])) {
            // Registra los datos principales del usuario/administrador en la sesión PHP
            $_SESSION['usuario'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol']; // Ej.: 'Administrador'
            $_SESSION['nombre'] = $usuario['nombre'];

            // Devuelve respuesta de éxito con nombre, correo real y rol de la base de datos
            echo json_encode([
                "status" => "success",
                "message" => "¡Bienvenido, Administrador " . $usuario['nombre'] . "!",
                "nombre" => $usuario['nombre'],
                "email" => $usuario['email'],
                "rol" => $usuario['rol'] // Enviamos el rol real devuelto por la BD
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } else {
            // La cuenta existe en `usuario`, pero la contraseña no coincide
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    // 2. Si no se encontró en `usuario`, busca en la tabla `cliente` por email, nombre o nombre completo
    $stmtCliente = $pdo->prepare("SELECT * FROM cliente 
        WHERE LOWER(TRIM(email)) = LOWER(TRIM(?)) 
           OR LOWER(TRIM(nombre)) = LOWER(TRIM(?)) 
           OR LOWER(TRIM(CONCAT(nombre, ' ', apellido))) = LOWER(TRIM(?)) 
        LIMIT 1");
    $stmtCliente->execute([$identificador, $identificador, $identificador]);
    $user = $stmtCliente->fetch();

    if ($user) {
        // Verifica si la contraseña del cliente coincide con su hash en `cliente`
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
        // No se encontró ninguna cuenta coincidente ni en `usuario` ni en `cliente`
        echo json_encode(["status" => "error", "message" => "No existe una cuenta registrada con ese nombre o correo."], JSON_UNESCAPED_UNICODE);
    }

} catch (PDOException $e) {
    // Captura errores de consulta o conexión PDO y los informa en formato JSON
    echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>