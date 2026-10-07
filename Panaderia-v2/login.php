<?php
session_start();

if (isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panadería - Iniciar sesión</title>
    <!-- Bootstrap 5.3.8 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- CSS propio -->
    <link rel="stylesheet" href="estilo.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card border-0 shadow rounded-4 p-4 p-md-5"
             style="max-width: 420px; width: 100%;">

            <!-- Logo -->
            <div class="text-center mb-2">
                <img src="imagenes/logo.png" alt="Panadería" class="img-fluid" style="max-width: 170px;">
            </div>

            <!-- Nombre del sistema -->
            <div class="text-center mb-4">
                <h5 class="fw-bold mb-1">Sistema de gestión de pagos</h5>
                <p class="text-muted mb-0">Panadería</p>
            </div>

            <!-- Separador -->
            <hr class="mb-4">

            <!-- Título -->
            <h3 class="text-center fw-bold mb-4">Iniciar sesión</h3>

            <!-- Mensaje de error -->
            <?php if ($error !== ""): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form action="autenticar.php" method="POST">

                <!-- Usuario -->
                <div class="mb-3">
                    <label for="usuario" class="form-label fw-bold"><i class="bi bi-person"></i> Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="usuario" name="usuario" maxlength="32" required
                         autofocus placeholder="Ingrese su usuario">
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="mb-4">
                    <label for="contrasenia" class="form-label fw-bold"><i class="bi bi-lock"></i> Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="contrasenia" name="contrasenia" required 
                        placeholder="Ingrese su contraseña">
                    </div>
                </div>

                <!-- Botón -->
                <button type="submit" class="btn btn-dark w-100 py-2 fw-bold"><i class="bi bi-box-arrow-in-right"></i> Ingresar</button>
            </form>

            <!-- Pie -->
            <p class="text-center text-muted small mt-4 mb-0">
                Acceso exclusivo para usuarios del sistema
            </p>
        </div>
    </div>

    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

