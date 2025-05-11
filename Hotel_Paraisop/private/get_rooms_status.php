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

$query = "SELECT h.numero_habitacion, h.disponibilidad, c.nombre, se.total as total_cargos 
          FROM habitacion h 
          LEFT JOIN reserva r ON h.id_habitacion = r.id_habitacion AND r.estado = 'confirmada' 
          LEFT JOIN cliente c ON r.id_cliente = c.id_cliente 
          LEFT JOIN (
              SELECT id_reserva, SUM(total) as total 
              FROM servicios_extras 
              GROUP BY id_reserva
          ) se ON r.id_reserva = se.id_reserva 
          ORDER BY h.numero_habitacion";
$stmt = $pdo->prepare($query);
$stmt->execute();
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'habitaciones' => $habitaciones]);
?>