<?php
// Configuración de los parámetros de acceso al servidor MySQL y a la base de datos del proyecto
$host = "127.0.0.1";          // Dirección del servidor de base de datos (localhost por IP)
$db   = "salon_eventos_syl";  // Nombre de la base de datos de reservas y salones
$user = "root";               // Usuario de MySQL (cambia si tu usuario es distinto)
$pass = "";                   // Contraseña de MySQL (vacía por defecto en instalación local)
$charset = "utf8mb4";         // Codificación de caracteres compatible con tildes, eñes y símbolos

// Cadena DSN (Data Source Name) que indica el motor (mysql), host, base de datos y charset
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opciones de configuración para el objeto PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Levanta excepciones ante errores SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Devuelve los datos como arreglos asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                // Usa consultas preparadas reales para mayor seguridad
];

try {
    // Instancia de la conexión PDO que será reutilizada por los scripts del backend
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si falla la conexión, responde con código HTTP 500, un JSON de error y detiene la ejecución
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit();
}
?>