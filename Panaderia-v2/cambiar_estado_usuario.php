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

// Procesar cambio de estado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"] ?? "";
    $activo = $_POST["activo"] ?? "";

    if ($id === "" || !is_numeric($id)) {
        $error = "Usuario no válido.";
    } elseif ($activo !== "0" && $activo !== "1") {
        $error = "Estado no válido.";
    } else {

        try {
            // Verificar que el usuario exista
            $sql = "SELECT id, usuario, activo FROM usuario WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([":id" => $id]);
            $usuarioSeleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuarioSeleccionado) {
                $error = "El usuario seleccionado no existe.";
            } else {
                // Cambiar estado
                $sql = "UPDATE usuario SET activo = :activo WHERE id = :id";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([":activo" => $activo, ":id" => $id]);
                if ($activo === "1") {
                    $mensaje = "Usuario activado correctamente.";
                } else {
                    $mensaje = "Usuario desactivado correctamente.";
                }
            }
        } catch (PDOException $e) {
            $error = "Error al cambiar el estado del usuario.";
        }
    }
}

// Obtener todos los usuarios
try {
    $sql = "SELECT id, usuario, activo FROM usuario ORDER BY id";
    $stmt = $conexion->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $usuarios = [];
    $error = "No se pudieron cargar los usuarios.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar estado de usuario</title>
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
            <div class="col-md-7 col-lg-6">
                <div class="card shadow">

                    <!-- Encabezado -->
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-person-check-fill"></i> Cambiar estado de usuario</h4>
                    </div>
                    <div class="card-body">

                        <!-- Mensaje de éxito -->
                        <?php if ($mensaje !== ""): ?>
                            <div class="alert alert-success alert-dismissible fade show"
                                 role="alert">
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
                            Seleccione un usuario y luego indique si desea
                            activarlo o desactivarlo.
                        </div>

                        <?php if (count($usuarios) > 0): ?>
                            <form method="POST">

                                <!-- Usuario -->
                                <div class="mb-3">
                                    <label for="id" class="form-label"><i class="bi bi-person-fill"></i> Seleccionar usuario</label>
                                    <select name="id" id="id" class="form-select" required>
                                        <option value="">Seleccionar usuario</option>
                                        
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?= htmlspecialchars($usuario["id"]) ?>">
                                                <?= htmlspecialchars($usuario["usuario"]) ?>
                                                -
                                                <?php if ($usuario["activo"] == 1): ?>
                                                    Activo
                                                <?php else: ?>
                                                    Inactivo
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>

                                <!-- Estado -->
                                <div class="mb-4">
                                    <label for="activo" class="form-label"><i class="bi bi-toggle-on"></i> Nuevo estado</label>
                                    <select name="activo" id="activo" class="form-select" required>
                                        <option value="">Seleccionar estado</option>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>

                                <!-- Botón -->
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Cambiar estado</button>
                                </div>
                            </form>

                        <?php else: ?>
                            <div class="alert alert-secondary text-center">
                                <i class="bi bi-people-fill"></i> No hay usuarios registrados.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
