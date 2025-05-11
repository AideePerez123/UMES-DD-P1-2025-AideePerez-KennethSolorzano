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
    echo json_encode(['available' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

$checkIn = $_POST['checkIn'] ?? null;
$checkOut = $_POST['checkOut'] ?? null;

if (!$checkIn || !$checkOut) {
    echo json_encode(['available' => false, 'message' => 'Fechas no proporcionadas']);
    exit;
}

$checkInDate = new DateTime($checkIn);
$checkOutDate = new DateTime($checkOut);
$today = new DateTime();
$today->setTime(0, 0, 0);

if ($checkInDate < $today || $checkInDate >= $checkOutDate) {
    echo json_encode(['available' => false, 'message' => 'Fechas inválidas']);
    exit;
}

$query = "SELECT COUNT(DISTINCT id_habitacion) as ocupadas 
          FROM reserva 
          WHERE estado != 'finalizada' 
          AND (fecha_ingreso <= :checkOut AND fecha_salida >= :checkIn)";
$stmt = $pdo->prepare($query);
$stmt->execute([':checkIn' => $checkIn, ':checkOut' => $checkOut]);
$ocupadas = $stmt->fetch(PDO::FETCH_ASSOC)['ocupadas'];

$totalHabitaciones = 10;
$disponibles = $totalHabitaciones - $ocupadas;

echo json_encode(['available' => $disponibles > 0]);
?>