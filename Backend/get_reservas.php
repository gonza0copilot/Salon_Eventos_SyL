<?php
// Permitir respuestas en formato JSON
header("Content-Type: application/json; charset=utf-8");

// Incluir el archivo de conexión creado anteriormente
require_once "conexion.php";

// Capturar los parámetros enviados desde la URL (Ej: get_reservas.php?mes=9&anio=2026)
// Si no se envían, toma por defecto el mes y año actuales
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

try {
    // Consulta SQL con marcadores de posición (?) para evitar inyección SQL
    $sql = "SELECT 
                r.id_reserva,
                r.fecha_evento,
                r.hora_inicio,
                r.hora_fin,
                s.nombre AS salon,
                CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                c.telefono,
                r.cantidad_invitados,
                r.estado,
                COALESCE(GROUP_CONCAT(srv.nombre_servicio SEPARATOR ', '), 'Sin adicionales') AS servicios_contratados
            FROM reserva r
            INNER JOIN cliente c ON r.id_cliente = c.id_cliente
            INNER JOIN salon s ON r.id_salon = s.id_salon
            LEFT JOIN detalle_reserva_servicio drs ON r.id_reserva = drs.id_reserva
            LEFT JOIN servicio srv ON drs.id_servicio = srv.id_servicio
            WHERE MONTH(r.fecha_evento) = ? 
              AND YEAR(r.fecha_evento) = ?
              AND r.estado IN ('Confirmada', 'Pagada Totalmente')
            GROUP BY r.id_reserva
            ORDER BY r.fecha_evento ASC, r.hora_inicio ASC";

    // Preparar y ejecutar la consulta pasando los valores de forma segura
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mes, $anio]);
    
    // Obtener todos los registros coincidentes
    $reservas = $stmt->fetchAll();

    // Responder con los datos codificados en JSON
    echo json_encode([
        "status" => "success",
        "mes_consultado" => $mes,
        "anio_consultado" => $anio,
        "total_registros" => count($reservas),
        "data" => $reservas
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error al consultar reservas: " . $e->getMessage()
    ]);
}
?>