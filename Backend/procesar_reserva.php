<?php
header("Content-Type: application/json; charset=utf-8");
require_once "conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $emailCliente = trim($data['email'] ?? '');
    $idSalon = $data['id_salon'] ?? 1;
    $fechaEvento = $data['fecha_evento'] ?? '';
    $turno = $data['turno'] ?? '';
    $montoTotal = $data['monto_total'] ?? 0;
    $metodoPago = trim($data['metodo_pago'] ?? 'efectivo'); // 'efectivo' o 'transferencia'
    
    $nombre = trim($data['nombre'] ?? '');
    $dni = trim($data['dni'] ?? '');
    $telefono = trim($data['telefono'] ?? '');

    if (empty($emailCliente) || empty($fechaEvento)) {
        echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios para la reserva."]);
        exit();
    }

    try {
        // Validación: Solo permitir una reserva por salón por día
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM reserva WHERE id_salon = ? AND fecha_evento = ? AND estado != 'Rechazada'");
        $stmtCheck->execute([$idSalon, $fechaEvento]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode([
                "status" => "error", 
                "message" => "Lo sentimos, este salón ya se encuentra reservado o con solicitud pendiente para esa fecha."
            ]);
            exit();
        }

        // Verificar o registrar cliente
        $stmtCliente = $pdo->prepare("SELECT id_cliente FROM cliente WHERE email = ?");
        $stmtCliente->execute([$emailCliente]);
        $cliente = $stmtCliente->fetch();

        if ($cliente) {
            $idCliente = $cliente['id_cliente'];
        } else {
            $passTemp = password_hash("123456", PASSWORD_DEFAULT);
            $stmtInsertCliente = $pdo->prepare("INSERT INTO cliente (nombre, apellido, dni_cuit, telefono, email, contrasena) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsertCliente->execute([$nombre, $nombre, $dni, $telefono, $emailCliente, $passTemp]);
            $idCliente = $pdo->lastInsertId();
        }

        $horaInicio = ($turno === 'Tarde') ? '12:00:00' : '20:00:00';
        $horaFin = ($turno === 'Tarde') ? '19:00:00' : '04:00:00';
        $cantidadInvitados = 100; 

        // 1. Insertar la reserva con estado inicial "Pendiente" de aprobación del admin
        $sqlReserva = "INSERT INTO reserva (id_cliente, id_salon, fecha_evento, hora_inicio, hora_fin, cantidad_invitados, monto_total, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente')";
        $stmtReserva = $pdo->prepare($sqlReserva);
        $stmtReserva->execute([$idCliente, $idSalon, $fechaEvento, $horaInicio, $horaFin, $cantidadInvitados, $montoTotal]);
        $idReserva = $pdo->lastInsertId();

        // 2. Registrar el pago asociado con el atributo 'estado' (Pendiente / Aprobado / etc.)
        $sqlPago = "INSERT INTO pago (id_reserva, monto, metodo_pago, estado) VALUES (?, ?, ?, 'Pendiente')";
        $stmtPago = $pdo->prepare($sqlPago);
        $stmtPago->execute([$idReserva, $montoTotal, $metodoPago]);

        echo json_encode([
            "status" => "success", 
            "message" => "¡Su petición ha sido enviada con éxito! El administrador la revisará a la brevedad."
        ]);

    } catch (\PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error al procesar la solicitud: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No se recibieron datos."]);
}
?>