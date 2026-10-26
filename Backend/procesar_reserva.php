<?php
header("Content-Type: application/json; charset=utf-8");
require_once "conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $emailCliente = trim($data['email'] ?? '');
    $idSalon = $data['id_salon'] ?? 1; // Por defecto el salón actual
    $fechaEvento = $data['fecha_evento'] ?? '';
    $turno = $data['turno'] ?? '';
    $montoTotal = $data['monto_total'] ?? 0;
    
    // Datos del formulario de contacto por si no está registrado
    $nombre = trim($data['nombre'] ?? '');
    $dni = trim($data['dni'] ?? '');
    $telefono = trim($data['telefono'] ?? '');

    if (empty($emailCliente) || empty($fechaEvento)) {
        echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios para la reserva."]);
        exit();
    }

    try {
        // 1. Verificar si el cliente ya existe en la base de datos por su email
        $stmtCliente = $pdo->prepare("SELECT id_cliente FROM cliente WHERE email = ?");
        $stmtCliente->execute([$emailCliente]);
        $cliente = $stmtCliente->fetch();

        if ($cliente) {
            $idCliente = $cliente['id_cliente'];
        } else {
            // Si no existe, lo registramos automáticamente con una contraseña genérica temporal
            $passTemp = password_hash("123456", PASSWORD_DEFAULT);
            $stmtInsertCliente = $pdo->prepare("INSERT INTO cliente (nombre, apellido, dni_cuit, telefono, email, contrasena) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsertCliente->execute([$nombre, $nombre, $dni, $telefono, $emailCliente, $passTemp]);
            $idCliente = $pdo->lastInsertId();
        }

        // 2. Definir horas según el turno seleccionado
        $horaInicio = ($turno === 'Tarde') ? '12:00:00' : '20:00:00';
        $horaFin = ($turno === 'Tarde') ? '19:00:00' : '04:00:00';

        // 3. Insertar la reserva en la tabla `reserva`
        $sqlReserva = "INSERT INTO reserva (id_cliente, id_salon, fecha_evento, hora_inicio, hora_fin, cantidad_invitados, monto_total, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente')";
        $stmtReserva = $pdo->prepare($sqlReserva);
        // Usamos un estimado de invitados por defecto o capacidad fija según el salón
        $cantidadInvitados = 100; 
        $stmtReserva->execute([$idCliente, $idSalon, $fechaEvento, $horaInicio, $horaFin, $cantidadInvitados, $montoTotal]);

        echo json_encode(["status" => "success", "message" => "¡Reserva registrada con éxito en la base de datos!"]);

    } catch (\PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error al guardar la reserva: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No se recibieron datos."]);
}
?>