<?php
// Indica que la respuesta del servidor tendrá formato JSON con codificación UTF-8
header("Content-Type: application/json; charset=utf-8");

// Incluye la conexión PDO ($pdo) a la base de datos salon_eventos_syl
require_once "conexion.php";

// Lee el cuerpo crudo de la petición HTTP (JSON enviado por fetch) y lo convierte en un array asociativo
$data = json_decode(file_get_contents("php://input"), true);

// Verifica que se haya decodificado un payload válido
if ($data) {
    // Extrae y limpia espacios en los extremos de los campos enviados desde el formulario de registro
    $nombre = trim($data['nombre'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');

    // Valida que ninguno de los campos obligatorios esté vacío
    if (empty($nombre) || empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Faltan completar campos obligatorios."]);
        exit();
    }

    try {
        // Divide el texto ingresado en "Nombre Completo" en hasta 2 partes: nombre y apellido
        $partes = explode(' ', $nombre, 2);
        $nombres = $partes[0];
        $apellidos = isset($partes[1]) ? $partes[1] : 'Sin apellido';
        
        // Genera valores provisorios para DNI/CUIT y teléfono (se completan o actualizan al reservar)
        $dni_cuit = "TEMP-" . time(); 
        $telefono = "Sin teléfono";

        // Genera un hash seguro de la contraseña usando el algoritmo por defecto de PHP (bcrypt)
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Prepara e inserta el nuevo cliente en la tabla `cliente` con consulta parametrizada
        $sql = "INSERT INTO cliente (nombre, apellido, dni_cuit, telefono, email, contrasena) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombres, $apellidos, $dni_cuit, $telefono, $email, $passwordHash]);

        // Responde confirmando que el registro se completó correctamente
        echo json_encode(["status" => "success", "message" => "¡Registro y contraseña guardados con éxito!"]);

    } catch (\PDOException $e) {
        // El código SQLSTATE 23000 indica violación de restricción (por ejemplo, email duplicado por índice UNIQUE)
        if ($e->getCode() == '23000') {
            echo json_encode(["status" => "error", "message" => "El correo electrónico ya se encuentra registrado."]);
        } else {
            // Cualquier otro error de base de datos se informa con el detalle de la excepción
            echo json_encode(["status" => "error", "message" => "Error en la base de datos: " . $e->getMessage()]);
        }
    }
} else {
    // Si no llegó JSON válido o el cuerpo estaba vacío, devuelve mensaje de error
    echo json_encode(["status" => "error", "message" => "No se recibieron datos válidos."]);
}
?>