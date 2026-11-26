<?php
// Establece que la respuesta del servidor será un JSON codificado en UTF-8
header("Content-Type: application/json; charset=utf-8");

// Incluye la conexión PDO ($pdo) a la base de datos salon_eventos_syl
require_once "conexion.php";

// Lee el cuerpo JSON enviado por fetch desde el frontend y lo decodifica como array asociativo
$data = json_decode(file_get_contents("php://input"), true);

// Verifica que se hayan recibido datos válidos
if ($data) {
    // Extrae y limpia los datos principales de la reserva y del cliente
    $emailCliente = trim($data['email'] ?? '');
    $idSalon = $data['id_salon'] ?? 1;
    $fechaEvento = $data['fecha_evento'] ?? '';
    $turno = $data['turno'] ?? '';
    $montoTotal = $data['monto_total'] ?? 0;
    $metodoPago = trim($data['metodo_pago'] ?? 'efectivo'); // 'efectivo' o 'transferencia'
    $servicios = $data['servicios'] ?? [];
    
    // Extrae y limpia los datos de contacto del solicitante
    $nombre = trim($data['nombre'] ?? '');
    $dni = trim($data['dni'] ?? '');
    $telefono = trim($data['telefono'] ?? '');

    // Valida que existan al menos el correo del cliente y la fecha del evento
    if (empty($emailCliente) || empty($fechaEvento)) {
        echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios para la reserva."]);
        exit();
    }

    try {
        // Validación de disponibilidad: impide reservar el mismo salón en la misma fecha si hay una reserva activa o pendiente
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM reserva WHERE id_salon = ? AND fecha_evento = ? AND estado != 'Rechazada'");
        $stmtCheck->execute([$idSalon, $fechaEvento]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode([
                "status" => "error", 
                "message" => "Lo sentimos, este salón ya se encuentra reservado o con solicitud pendiente para esa fecha."
            ]);
            exit();
        }

        // Busca si ya existe un cliente registrado con ese email; si no existe, crea uno nuevo
        $stmtCliente = $pdo->prepare("SELECT id_cliente FROM cliente WHERE email = ?");
        $stmtCliente->execute([$emailCliente]);
        $cliente = $stmtCliente->fetch();

        if ($cliente) {
            // Reutiliza el ID del cliente ya registrado
            $idCliente = $cliente['id_cliente'];
        } else {
            // Crea una contraseña temporal cifrada e inserta el nuevo registro en la tabla cliente
            $passTemp = password_hash("123456", PASSWORD_DEFAULT);
            $stmtInsertCliente = $pdo->prepare("INSERT INTO cliente (nombre, apellido, dni_cuit, telefono, email, contrasena) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsertCliente->execute([$nombre, $nombre, $dni, $telefono, $emailCliente, $passTemp]);
            $idCliente = $pdo->lastInsertId();
        }

        // Define la franja horaria según el turno elegido ("Tarde" o turno noche por defecto)
        $horaInicio = ($turno === 'Tarde') ? '12:00:00' : '20:00:00';
        $horaFin = ($turno === 'Tarde') ? '19:00:00' : '04:00:00';
        $cantidadInvitados = 100; 

        // 1. Inserta el registro principal de la reserva con estado inicial "Pendiente"
        $sqlReserva = "INSERT INTO reserva (id_cliente, id_salon, fecha_evento, hora_inicio, hora_fin, cantidad_invitados, monto_total, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente')";
        $stmtReserva = $pdo->prepare($sqlReserva);
        $stmtReserva->execute([$idCliente, $idSalon, $fechaEvento, $horaInicio, $horaFin, $cantidadInvitados, $montoTotal]);
        $idReserva = $pdo->lastInsertId();

        // 2. Registra el pago asociado a la reserva usando NOW() para la fecha actual y estado "Pendiente"
        $sqlPago = "INSERT INTO pago (id_reserva, monto, fecha_pago, metodo_pago, estado) VALUES (?, ?, NOW(), ?, 'Pendiente')";
        $stmtPago = $pdo->prepare($sqlPago);
        $stmtPago->execute([$idReserva, $montoTotal, $metodoPago]);

        // 3. Registra cada servicio adicional seleccionado en la tabla intermedia detalle_reserva_servicio
        if (!empty($servicios) && is_array($servicios)) {
            $stmtPrecioServ = $pdo->prepare("SELECT precio_base FROM servicio WHERE id_servicio = ?");
            $stmtDetalle = $pdo->prepare("INSERT INTO detalle_reserva_servicio (id_reserva, id_servicio, cantidad, precio_final) VALUES (?, ?, 1, ?)");

            foreach ($servicios as $idServicio) {
                $idServicioLimpio = intval($idServicio);
                if ($idServicioLimpio > 0) {
                    // Consulta el precio base oficial del servicio desde la base de datos
                    $stmtPrecioServ->execute([$idServicioLimpio]);
                    $precioServ = $stmtPrecioServ->fetchColumn();

                    if ($precioServ !== false) {
                        $stmtDetalle->execute([$idReserva, $idServicioLimpio, $precioServ]);
                    }
                }
            }
        }

        // Responde al frontend confirmando que la solicitud fue registrada correctamente
        echo json_encode([
            "status" => "success", 
            "message" => "¡Su petición ha sido enviada con éxito! El administrador la revisará a la brevedad."
        ]);

    } catch (\PDOException $e) {
        // Captura y devuelve errores de base de datos en formato JSON
        echo json_encode(["status" => "error", "message" => "Error al procesar la solicitud: " . $e->getMessage()]);
    }
} else {
    // Si no llegó un JSON válido o está vacío, informa el error
    echo json_encode(["status" => "error", "message" => "No se recibieron datos."]);
}
?>