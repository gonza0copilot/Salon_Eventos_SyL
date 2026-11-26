<?php
// Configura la cabecera HTTP para que la respuesta se envíe en formato JSON con codificación UTF-8
header("Content-Type: application/json; charset=utf-8");

// Incluye la conexión PDO ($pdo) a la base de datos salon_eventos_syl
require_once "conexion.php";

// Lee el cuerpo JSON enviado mediante fetch desde el frontend y lo convierte en un array asociativo
$data = json_decode(file_get_contents("php://input"), true);

// Verifica que se hayan recibido datos decodificables
if ($data) {
    // Extrae y normaliza los campos principales de la reserva, turno, pago y servicios adicionales
    $emailCliente = trim($data['email'] ?? '');
    $idSalon = intval($data['id_salon'] ?? 1);
    $fechaEvento = $data['fecha_evento'] ?? '';
    $turno = $data['turno'] ?? '';
    $metodoPago = trim($data['metodo_pago'] ?? 'efectivo'); // 'efectivo' o 'transferencia'
    $servicios = $data['servicios'] ?? [];

    // Extrae y limpia los datos personales del cliente solicitante
    $nombre = trim($data['nombre'] ?? '');
    $dni = trim($data['dni'] ?? '');
    $telefono = trim($data['telefono'] ?? '');

    // Valida que estén presentes al menos el correo del cliente y la fecha del evento
    if (empty($emailCliente) || empty($fechaEvento)) {
        echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios para la reserva."], JSON_UNESCAPED_UNICODE);
        exit();
    }

    try {
        // Consulta el precio base vigente del salón seleccionado desde la tabla salon
        $stmtSalon = $pdo->prepare("SELECT precio_base FROM salon WHERE id_salon = ?");
        $stmtSalon->execute([$idSalon]);
        $precioSalon = $stmtSalon->fetchColumn();

        // Verifica que el salón exista en la base de datos
        if ($precioSalon === false) {
            echo json_encode(["status" => "error", "message" => "El salón seleccionado no existe."], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Validación de disponibilidad: bloquea la reserva si ya existe otra no rechazada en el mismo salón y fecha
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM reserva WHERE id_salon = ? AND fecha_evento = ? AND estado != 'Rechazada'");
        $stmtCheck->execute([$idSalon, $fechaEvento]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode([
                "status" => "error", 
                "message" => "Lo sentimos, este salón ya se encuentra reservado o con solicitud pendiente para esa fecha."
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Busca si el cliente ya existe por email; si existe, actualiza DNI/teléfono si fueron informados, o crea uno nuevo
        $stmtCliente = $pdo->prepare("SELECT id_cliente FROM cliente WHERE email = ?");
        $stmtCliente->execute([$emailCliente]);
        $cliente = $stmtCliente->fetch();

        if ($cliente) {
            $idCliente = $cliente['id_cliente'];

            // Actualiza dni_cuit y/o telefono conservando los valores previos si alguno viene vacío
            if (!empty($dni) || !empty($telefono)) {
                $sqlUpdateCliente = "UPDATE cliente SET 
                    dni_cuit = CASE WHEN ? != '' THEN ? ELSE dni_cuit END,
                    telefono = CASE WHEN ? != '' THEN ? ELSE telefono END
                    WHERE id_cliente = ?";
                $stmtUpdateCliente = $pdo->prepare($sqlUpdateCliente);
                $stmtUpdateCliente->execute([$dni, $dni, $telefono, $telefono, $idCliente]);
            }
        } else {
            // Crea un nuevo registro de cliente con contraseña temporal cifrada si aún no existía
            $passTemp = password_hash("123456", PASSWORD_DEFAULT);
            $stmtInsertCliente = $pdo->prepare("INSERT INTO cliente (nombre, apellido, dni_cuit, telefono, email, contrasena) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsertCliente->execute([$nombre, $nombre, $dni, $telefono, $emailCliente, $passTemp]);
            $idCliente = $pdo->lastInsertId();
        }

        // Determina el rango horario (hora_inicio y hora_fin) según el turno seleccionado
        if ($turno === 'Tarde') {
            $horaInicio = '12:00:00';
            $horaFin = '19:00:00';
        } elseif ($turno === 'Día completo') {
            $horaInicio = '10:00:00';
            $horaFin = '19:00:00';
        } else {
            $horaInicio = '20:00:00';
            $horaFin = '04:00:00';
        }
        $cantidadInvitados = 100;

        // Calcula el monto total en el servidor partiendo del precio base del salón y sumando servicios válidos
        $montoTotal = floatval($precioSalon);
        $serviciosValidos = [];

        if (!empty($servicios) && is_array($servicios)) {
            $stmtPrecioServ = $pdo->prepare("SELECT precio_base FROM servicio WHERE id_servicio = ?");
            foreach ($servicios as $idServicio) {
                $idServicioLimpio = intval($idServicio);
                if ($idServicioLimpio > 0) {
                    // Busca el precio base oficial de cada servicio en la base de datos
                    $stmtPrecioServ->execute([$idServicioLimpio]);
                    $precioServ = $stmtPrecioServ->fetchColumn();
                    if ($precioServ !== false) {
                        $precioServFloat = floatval($precioServ);
                        $montoTotal += $precioServFloat;
                        $serviciosValidos[] = [
                            'id_servicio' => $idServicioLimpio,
                            'precio_final' => $precioServFloat
                        ];
                    }
                }
            }
        }

        // 1. Inserta la reserva con estado inicial "Pendiente" y el monto total calculado en PHP
        $sqlReserva = "INSERT INTO reserva (id_cliente, id_salon, fecha_evento, hora_inicio, hora_fin, cantidad_invitados, monto_total, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente')";
        $stmtReserva = $pdo->prepare($sqlReserva);
        $stmtReserva->execute([$idCliente, $idSalon, $fechaEvento, $horaInicio, $horaFin, $cantidadInvitados, $montoTotal]);
        $idReserva = $pdo->lastInsertId();

        // 2. Registra el pago inicial en la tabla pago con fecha actual (NOW()) y estado "Pendiente"
        $sqlPago = "INSERT INTO pago (id_reserva, monto, fecha_pago, metodo_pago, estado) VALUES (?, ?, NOW(), ?, 'Pendiente')";
        $stmtPago = $pdo->prepare($sqlPago);
        $stmtPago->execute([$idReserva, $montoTotal, $metodoPago]);

        // 3. Registra cada servicio adicional válido en la tabla detalle_reserva_servicio
        if (!empty($serviciosValidos)) {
            $stmtDetalle = $pdo->prepare("INSERT INTO detalle_reserva_servicio (id_reserva, id_servicio, cantidad, precio_final) VALUES (?, ?, 1, ?)");
            foreach ($serviciosValidos as $servItem) {
                $stmtDetalle->execute([$idReserva, $servItem['id_servicio'], $servItem['precio_final']]);
            }
        }

        // Devuelve respuesta JSON de éxito al frontend
        echo json_encode([
            "status" => "success", 
            "message" => "¡Su petición ha sido enviada con éxito! El administrador la revisará a la brevedad."
        ], JSON_UNESCAPED_UNICODE);

    } catch (\PDOException $e) {
        // Captura errores de PDO/SQL y responde con el mensaje de error en JSON
        echo json_encode(["status" => "error", "message" => "Error al procesar la solicitud: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
} else {
    // Responde con error si no se recibieron datos o el JSON no era válido
    echo json_encode(["status" => "error", "message" => "No se recibieron datos."], JSON_UNESCAPED_UNICODE);
}
?>