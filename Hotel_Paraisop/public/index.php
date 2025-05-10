<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
$imagePath = __DIR__ . '/../imagenes/fondo.png';
if (file_exists($imagePath)) {
    echo "<!-- Debug: fondo.png exists -->";
} else {
    echo "<!-- Debug: fondo.png does NOT exist at $imagePath -->";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel El Paraíso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../estilos.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../imagenes/logo.png" alt="Hotel El Paraíso" style="height: 40px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#reserve">Reservas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Iniciar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero text-center d-flex align-items-center justify-content-center">
        <div>
            <h1>Bienvenidos a Hotel El Paraíso</h1>
            <p>Un lugar ideal para personas de la tercera edad</p>
        </div>
    </div>

    <div class="container my-5" id="reserve">
        <h2 class="text-center mb-4">Realiza tu Reserva</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-body">
                        <form id="reservationForm" action="reservas.php" method="GET" onsubmit="return validateForm(event)">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="nit" class="form-label">NIT</label>
                                <input type="text" class="form-control" id="nit" name="nit" required>
                            </div>
                            <div class="mb-3">
                                <label for="birthDate" class="form-label">Fecha de Nacimiento (AAAA-MM-DD)</label>
                                <input type="text" class="form-control date-input" id="birthDate" name="birthDate" placeholder="Ejemplo: 1950-01-01" required>
                            </div>
                            <div class="mb-3">
                                <label for="checkIn" class="form-label">Fecha de Ingreso (AAAA-MM-DD)</label>
                                <input type="text" class="form-control date-input" id="checkIn" name="checkIn" placeholder="Ejemplo: 2025-05-15" required>
                            </div>
                            <div class="mb-3">
                                <label for="checkOut" class="form-label">Fecha de Salida (AAAA-MM-DD)</label>
                                <input type="text" class="form-control date-input" id="checkOut" name="checkOut" placeholder="Ejemplo: 2025-05-20" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Reservar</button>
                        </form>
                        <div id="availability" class="mt-3 text-center"></div>
                        <div id="dateError" class="mt-2 text-center text-danger"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="room-preview text-center">
                    <h3 class="text-white">Vista de Habitaciones</h3>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateDate(dateStr, isBirthDate = false) {
            const regex = /^\d{4}-\d{2}-\d{2}$/;
            if (!regex.test(dateStr)) return false;
            const date = new Date(dateStr);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (isNaN(date.getTime())) return false;
            if (isBirthDate) {
                return date <= today;
            }
            return true;
        }

        function validateForm(event) {
            event.preventDefault();
            const birthDate = document.getElementById('birthDate').value;
            const checkIn = document.getElementById('checkIn').value;
            const checkOut = document.getElementById('checkOut').value;
            const dateError = document.getElementById('dateError');

            if (!validateDate(birthDate, true) || !validateDate(checkIn) || !validateDate(checkOut)) {
                dateError.innerHTML = 'Por favor, ingrese fechas válidas en formato AAAA-MM-DD.';
                return false;
            }

            const checkInDate = new Date(checkIn);
            const checkOutDate = new Date(checkOut);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (checkInDate < today) {
                dateError.innerHTML = 'La fecha de ingreso no puede ser anterior a hoy.';
                return false;
            }
            if (checkInDate >= checkOutDate) {
                dateError.innerHTML = 'La fecha de ingreso debe ser anterior a la fecha de salida.';
                return false;
            }

            dateError.innerHTML = '';
            const formData = new FormData(document.getElementById('reservationForm'));
            fetch('check_availability.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const availability = document.getElementById('availability');
                if (data.available) {
                    availability.innerHTML = '<div class="alert alert-success">Habitación disponible. Procediendo con la reserva...</div>';
                    document.getElementById('reservationForm').submit();
                } else {
                    availability.innerHTML = '<div class="alert alert-danger">No hay habitaciones disponibles.</div>';
                }
            })
            .catch(error => console.error('Error:', error));
            return true;
        }
    </script>
</body>
</html>