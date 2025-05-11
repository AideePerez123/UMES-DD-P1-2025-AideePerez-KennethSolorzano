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

$costos = [
    'Desayuno' => 30.00,
    'Almuerzo' => 65.00,
    'Cena' => 75.00,
    'Masaje' => 150.00,
    'Spa' => 300.00
];

$input = json_decode(file_get_contents('php://input'), true);
$numeroHabitacion = $input['room_number'] ?? null;
$servicios = $input['services'] ?? [];

if (!$numeroHabitacion || empty($servicios)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

$pdo->beginTransaction();

try {
    $query = "SELECT r.id_reserva, r.id_cliente 
              FROM reserva r 
              JOIN habitacion h ON r.id_habitacion = h.id_habitacion 
              WHERE h.numero_habitacion = :numero AND r.estado = 'confirmada'";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':numero' => $numeroHabitacion]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Reserva no encontrada']);
        exit;
    }

    foreach ($servicios as $servicio) {
        $descripcion = $servicio['descripcion'] ?? '';
        $cantidad = (int)($servicio['cantidad'] ?? 0);

        if (!isset($costos[$descripcion]) || $cantidad <= 0) {
            continue;
        }

        $query = "INSERT INTO servicios_extras (id_reserva, descripcion, cantidad, costo_unitario) 
                  VALUES (:reservaId, :descripcion, :cantidad, :costo)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':reservaId' => $reserva['id_reserva'],
            ':descripcion' => $descripcion,
            ':cantidad' => $cantidad,
            ':costo' => $costos[$descripcion]
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>