<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$host = 'localhost';
$dbname = 'hotel_paraiso';
$username = 'tu_usuario';
$password = 'tu_contraseña';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$numeroHabitacion = $input['room_number'] ?? null;
$fechaSalida = $input['fecha_salida'] ?? date('Y-m-d');

if (!$numeroHabitacion) {
    echo json_encode(['success' => false, 'message' => 'Número de habitación requerido']);
    exit;
}

// Validar la fecha de salida
try {
    $fechaSalidaDt = new DateTime($fechaSalida);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Fecha de salida inválida']);
    exit;
}

$pdo->beginTransaction();

try {
    // Buscar la reserva activa y verificar que la habitación esté ocupada
    $query = "SELECT r.id_reserva, r.fecha_ingreso, r.id_cliente, c.nombre, h.numero_habitacion 
              FROM reserva r 
              JOIN habitacion h ON r.id_habitacion = h.id_habitacion 
              JOIN cliente c ON r.id_cliente = c.id_cliente 
              WHERE h.numero_habitacion = :numero 
              AND r.estado = 'confirmada' 
              AND h.disponibilidad = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':numero' => $numeroHabitacion]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Reserva no encontrada o habitación no ocupada']);
        exit;
    }

    // Validar que la fecha de salida sea posterior a la fecha de ingreso
    $fechaIngreso = new DateTime($reserva['fecha_ingreso']);
    if ($fechaSalidaDt < $fechaIngreso) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'La fecha de salida no puede ser anterior a la fecha de ingreso']);
        exit;
    }

    // Calcular días de estadía
    $diasEstadia = $fechaIngreso->diff($fechaSalidaDt)->days;
    if ($diasEstadia == 0) {
        $diasEstadia = 1; // Mínimo 1 día de estadía
    }
    $costoPorDia = 200.00;
    $costoEstadia = $diasEstadia * $costoPorDia;

    // Calcular cargos extras directamente (evitar problemas con la columna generada)
    $query = "SELECT SUM(cantidad * costo_unitario) as total_cargos 
              FROM servicios_extras 
              WHERE id_reserva = :reservaId";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':reservaId' => $reserva['id_reserva']]);
    $totalCargos = (float)($stmt->fetch(PDO::FETCH_ASSOC)['total_cargos'] ?? 0);

    $totalPagar = $costoEstadia + $totalCargos;

    // Actualizar la reserva
    $query = "UPDATE reserva 
              SET estado = 'finalizada', fecha_salida = :fechaSalida 
              WHERE id_reserva = :reservaId";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':fechaSalida' => $fechaSalida, ':reservaId' => $reserva['id_reserva']]);

    // Liberar la habitación
    $query = "UPDATE habitacion 
              SET disponibilidad = 1 
              WHERE numero_habitacion = :numero";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':numero' => $numeroHabitacion]);

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'detalles' => [
            'nombre' => $reserva['nombre'],
            'habitacion' => $reserva['numero_habitacion'],
            'dias_estadia' => $diasEstadia,
            'costo_estadia' => $costoEstadia,
            'cargos_extras' => $totalCargos,
            'total_pagar' => $totalPagar
        ]
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>