<?php
header("Content-Type: application/json; charset=utf-8");
require_once "conexion.php";

// Obtener los datos enviados por POST desde JavaScript
$data = json_decode(file_get_contents("php://input"), true);


if ($data) {
    $nombre = trim($data['nombre'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');

    if (empty($nombre) || empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Faltan completar campos obligatorios."]);
        exit();
    }

    try {
        // Separar nombre y apellido de forma básica para adaptarlo a tu tabla cliente
        $partes = explode(' ', $nombre, 2);
        $nombres = $partes[0];
        $apellidos = isset($partes[1]) ? $partes[1] : 'Sin apellido';
        
        // Generar un DNI/CUIT temporal único basado en el tiempo para evitar errores de campo único en pruebas
        $dni_cuit = "TEMP-" . time(); 
        $telefono = "Sin teléfono";

        // Insertar en la tabla cliente
        $sql = "INSERT INTO cliente (nombre, apellido, dni_cuit, telefono, email) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombres, $apellidos, $dni_cuit, $telefono, $email]);

        echo json_encode(["status" => "success", "message" => "¡Registro exitoso en la base de datos!"]);

    } catch (\PDOException $e) {
        // Código 23000 es duplicidad de entrada (email ya registrado)
        if ($e->getCode() == '23000') {
            echo json_encode(["status" => "error", "message" => "El correo electrónico ya se encuentra registrado."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "No se recibieron datos válidos."]);
}
?>