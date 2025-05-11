<?php
header('Content-Type: application/json');

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
$nombre = $input['name'] ?? null;
$nit = $input['nit'] ?? null;
$fechaNacimiento = $input['birthDate'] ?? null;
$checkIn = $input['checkIn'] ?? null;
$checkOut = $input['checkOut'] ?? null;

if (!$nombre || !$nit || !$fechaNacimiento || !$checkIn || !$checkOut) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

$birthDate = new DateTime($fechaNacimiento);
$today = new DateTime();
$edad = $today->diff($birthDate)->y;

$pdo->beginTransaction();

try {
    $query = "INSERT INTO cliente (nombre, NIT, fecha_nacimiento, telefono) 
              VALUES (:nombre, :nit, :fechaNacimiento, :telefono)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':nombre' => $nombre,
        ':nit' => $nit,
        ':fechaNacimiento' => $fechaNacimiento,
        ':telefono' => ''
    ]);
    $clienteId = $pdo->lastInsertId();

    $esMayor = $edad >= 60;
    $query = "SELECT id_habitacion, numero_habitacion 
              FROM habitacion 
              WHERE disponibilidad = 1 
              AND cercana_salida = :cercana 
              AND id_habitacion NOT IN (
                  SELECT id_habitacion 
                  FROM reserva 
                  WHERE estado != 'finalizada' 
                  AND (fecha_ingreso <= :checkOut AND fecha_salida >= :checkIn)
              ) 
              ORDER BY numero_habitacion 
              LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':cercana' => $esMayor, ':checkIn' => $checkIn, ':checkOut' => $checkOut]);
    $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$habitacion) {
        $query = "SELECT id_habitacion, numero_habitacion 
                  FROM habitacion 
                  WHERE disponibilidad = 1 
                  AND id_habitacion NOT IN (
                      SELECT id_habitacion 
                      FROM reserva 
                      WHERE estado != 'finalizada' 
                      AND (fecha_ingreso <= :checkOut AND fecha_salida >= :checkIn)
                  ) 
                  ORDER BY numero_habitacion 
                  LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':checkIn' => $checkIn, ':checkOut' => $checkOut]);
        $habitacion = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$habitacion) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'No hay habitaciones disponibles']);
        exit;
    }

    $query = "INSERT INTO reserva (fecha_ingreso, fecha_salida, estado, id_cliente, id_habitacion) 
              VALUES (:checkIn, :checkOut, 'confirmada', :clienteId, :habitacionId)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':checkIn' => $checkIn,
        ':checkOut' => $checkOut,
        ':clienteId' => $clienteId,
        ':habitacionId' => $habitacion['id_habitacion']
    ]);

    $query = "UPDATE habitacion SET disponibilidad = 0 WHERE id_habitacion = :habitacionId";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':habitacionId' => $habitacion['id_habitacion']]);

    $pdo->commit();
    echo json_encode(['success' => true, 'room' => $habitacion['numero_habitacion']]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>