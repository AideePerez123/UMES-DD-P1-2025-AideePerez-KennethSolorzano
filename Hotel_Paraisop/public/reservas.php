<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
$imagePath = __DIR__ . '/../imagenes/reserva_confirmacion.png';
if (file_exists($imagePath)) {
    echo "<!-- Debug: reserva_confirmacion.png exists -->";
} else {
    echo "<!-- Debug: reserva_confirmacion.png does NOT exist at $imagePath -->";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Reserva - Hotel El Paraíso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../estilos.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center mb-4">Confirmar Reserva</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card confirmation-card">
                    <div class="card-body">
                        <div id="reservationDetails" class="mb-3"></div>
                        <div id="reservationMessage" class="text-center mb-3"></div>
                        <div class="text-center">
                            <a href="index.php" class="btn btn-primary">Volver al Inicio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const reservationData = {
                name: urlParams.get('name'),
                nit: urlParams.get('nit'),
                birthDate: urlParams.get('birthDate'),
                checkIn: urlParams.get('checkIn'),
                checkOut: urlParams.get('checkOut')
            };

            fetch('reserve_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(reservationData)
            })
            .then(response => response.json())
            .then(data => {
                const reservationDetails = document.getElementById('reservationDetails');
                const reservationMessage = document.getElementById('reservationMessage');
                if (data.success) {
                    reservationDetails.innerHTML = `
                        <p><strong>Nombre:</strong> ${reservationData.name}</p>
                        <p><strong>NIT:</strong> ${reservationData.nit}</p>
                        <p><strong>Fecha de Ingreso:</strong> ${reservationData.checkIn}</p>
                        <p><strong>Fecha de Salida:</strong> ${reservationData.checkOut}</p>
                        <p><strong>Habitación Asignada:</strong> ${data.room}</p>
                    `;
                    reservationMessage.innerHTML = '<div class="alert alert-success">Reserva confirmada exitosamente.</div>';
                } else {
                    reservationMessage.innerHTML = '<div class="alert alert-danger">Error al procesar la reserva: ' + (data.message || 'Intente nuevamente.') + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('reservationMessage').innerHTML = '<div class="alert alert-danger">Error al conectar con el servidor. Por favor, intente nuevamente.</div>';
            });
        };
    </script>
</body>
</html>