<?php require_once 'estructura/encabezado.php'; ?>

<main class="container-fluid p-0">
    <!-- Sección Hero -->
    <section class="hero-section position-relative">
        <div class="hero-image" style="background-image: url('imagenes/fondo.png');">
            <div class="hero-overlay d-flex align-items-center">
                <div class="container text-center text-white">
                    <h1 class="display-4 mb-4">Bienvenido al Hotel El Paraíso</h1>
                    <p class="lead mb-4">Donde el confort se encuentra con la elegancia</p>
                    <a href="paginas/reserva.php" class="btn btn-primary btn-lg me-3">Reservar Ahora</a>
                    <a href="paginas/login.php" class="btn btn-outline-light btn-lg">Acceso Empleados</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección Características -->
    <section class="caracteristicas py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Nuestras Ventajas</h2>
            
            <div class="row">
                <!-- Tarjeta 1 -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow">
                        <img src="imagenes/habitaciones.png" class="card-img-top" alt="Habitaciones">
                        <div class="card-body">
                            <h5 class="card-title">Habitaciones de Lujo</h5>
                            <p class="card-text">Amplias suites con todas las comodidades modernas:</p>
                            <ul class="list-unstyled">
                                <li>✔ Aire acondicionado</li>
                                <li>✔ TV pantalla plana</li>
                                <li>✔ Wi-Fi de alta velocidad</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 2 -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow">
                        <div class="card-body">
                            <h5 class="card-title">Servicios Exclusivos</h5>
                            <div class="text-center my-4">
                                <i class="fas fa-utensils fa-3x text-primary mb-3"></i>
                            </div>
                            <p class="card-text">Disfruta de nuestros servicios premium:</p>
                            <ul class="list-unstyled">
                                <li>✔ Desayuno buffet incluido</li>
                                <li>✔ Servicio a la habitación</li>
                                <li>✔ Spa y masajes</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 3 -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow">
                        <img src="imagenes/ubicacion.png" class="card-img-top" alt="Ubicación">
                        <div class="card-body">
                            <h5 class="card-title">Ubicación Privilegiada</h5>
                            <p class="card-text">A pocos minutos de:</p>
                            <ul class="list-unstyled">
                                <li>✔ Centro histórico</li>
                                <li>✔ Zonas comerciales</li>
                                <li>✔ Principales atracciones</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección Llamado a la Acción -->
    <section class="cta-section bg-primary text-white py-5">
        <div class="container text-center">
            <h2 class="mb-4">¿Qué esperas para vivir la experiencia?</h2>
            <p class="mb-4">Oferta especial: Q350 por noche en habitación doble</p>
            <a href="paginas/reserva.php" class="btn btn-light btn-lg">Reservar Ahora</a>
        </div>
    </section>
</main>

<?php require_once 'estructura/pie.php'; ?>