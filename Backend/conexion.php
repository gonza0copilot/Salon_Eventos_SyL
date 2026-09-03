<?php
// Configuración de los parámetros de acceso a MySQL
$host = "127.0.0.1";
$db   = "gestion_salones";
$user = "root";        // Cambia si tu usuario de MySQL es distinto
$pass = "";            // Ingresa la contraseña de tu MySQL
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Levanta excepciones ante errores SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Devuelve los datos como arreglos asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                // Usa consultas preparadas reales para mayor seguridad
];

try {
    // Instancia de la conexión
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si falla la conexión, devuelve un JSON con el mensaje de error
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit();
}
?>