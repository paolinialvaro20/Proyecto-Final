<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$error = "";

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    if ($nombre === "") {
        $error = "Debés ingresar el nombre del rol.";
    } else {

        try {
            $sql = "INSERT INTO rol (nombre) VALUES (:nombre)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([":nombre" => $nombre]);
            $mensaje = "Rol agregado correctamente.";

        } catch (PDOException $e) {
            // Error de clave duplicada
            if ($e->errorInfo[0] === "23000") {
                $error = "Rol ya existente en la base de datos, verifique";
            } else {
                $error = "Error al agregar el rol.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar rol</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <?php require_once "menu.php"; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">

                    <!-- Encabezado -->
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-person-badge-fill"></i>Agregar nuevo rol</h4>
                    </div>


                    <div class="card-body">
                        <!-- Mensaje de éxito -->
                        <?php if ($mensaje !== ""): ?>

                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill"></i>
                                <?= htmlspecialchars($mensaje) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>

                        <?php endif; ?>

                        <!-- Mensaje de error -->
                        <?php if ($error !== ""): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <?= htmlspecialchars($error) ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Información -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i>
                            Ingrese el nombre del nuevo rol que desea agregar al sistema.
                        </div>

                        <!-- Formulario -->
                        <form method="POST">
                            <div class="mb-4">
                                <label for="nombre" class="form-label"><i class="bi bi-person-badge"></i> Nombre del rol</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" maxlength="100" 
                                placeholder="Ingrese el nombre del rol" required>
                            </div>

                            <!-- Botón -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-plus-circle-fill"></i> Agregar rol</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>


     <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>