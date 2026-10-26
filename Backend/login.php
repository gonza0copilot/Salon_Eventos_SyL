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
    // Buscar al cliente por su correo electrónico
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Verificar si la contraseña ingresada coincide con el hash guardado en la base de datos
        if (password_verify($password, $user['contrasena'])) {
            echo json_encode([
                "status" => "success", 
                "message" => "¡Bienvenido de nuevo, " . $user['nombre'] . "!",
                "nombre" => $user['nombre']
            ]);
        } else {
            // Contraseña errónea
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
        }
    } else {
        // Correo no encontrado
        echo json_encode(["status" => "error", "message" => "No existe una cuenta registrada con ese correo."]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
}
?>