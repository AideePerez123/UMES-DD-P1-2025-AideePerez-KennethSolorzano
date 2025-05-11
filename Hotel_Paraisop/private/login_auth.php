<?php
session_start();
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

$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Faltan credenciales']);
    exit;
}

$query = "SELECT * FROM empleado WHERE usuario = :username";
$stmt = $pdo->prepare($query);
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['contraseña'])) {
    $_SESSION['user_id'] = $user['id_empleado'];
    $_SESSION['username'] = $user['usuario'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
}
?>