<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "menu.php";
$mensaje = "";

// Usuario que inició sesión
$id_usuario_actual = $_SESSION["id_usuario"];

// Procesar eliminación
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario_eliminar = $_POST["id_usuario"] ?? "";

    if ($id_usuario_eliminar == "") {
        $mensaje = "Debe seleccionar un usuario.";
    } elseif ($id_usuario_eliminar == $id_usuario_actual) {
        $mensaje = "No puede eliminar el usuario con el que inició sesión.";
    } else {
        // Desactivar usuario en lugar de eliminarlo físicamente
        $sql = "UPDATE usuario SET activo = 0 WHERE id = ?";
        $stmt = $conexion->prepare($sql);

        if ($stmt->execute([$id_usuario_eliminar])) {
            if ($stmt->rowCount() > 0) {
                $mensaje = "Usuario eliminado correctamente.";
            } else {
                $mensaje = "No se encontró el usuario seleccionado.";
            }
        } else {
            $mensaje = "Error al eliminar el usuario.";
        }
    }
}

// Obtener usuarios activos
$sqlUsuarios = "SELECT id, usuario FROM usuario WHERE activo = 1 ORDER BY usuario";
$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario</title>
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
                        <h4 class="mb-0"><i class="bi bi-person-x-fill"></i> Eliminar usuario</h4>
                    </div>

                    <div class="card-body">

                        <!-- Mensaje -->
                        <?php if ($mensaje != ""): ?>
                            <div class="alert
                                <?= $mensaje === "Usuario eliminado correctamente."
                                    ? "alert-success"
                                    : "alert-danger"
                                ?>
                                alert-dismissible fade show"
                                role="alert">

                                <i class="bi
                                    <?= $mensaje === "Usuario eliminado correctamente."
                                        ? "bi-check-circle-fill"
                                        : "bi-exclamation-triangle-fill"
                                    ?>"></i>
                                <?= htmlspecialchars($mensaje) ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Advertencia -->
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Atención:</strong>
                            al eliminar un usuario, este quedará desactivado
                            y ya no podrá iniciar sesión.
                        </div>

                        <!-- Formulario -->
                        <form method="post" onsubmit="return confirmarEliminacion();">
                            <div class="mb-4">
                                <label for="id_usuario" class="form-label"><i class="bi bi-person-fill"></i> Usuario</label>
                                <select id="id_usuario" name="id_usuario" class="form-select" required>
                                    <option value="">Seleccione un usuario</option>

                                    <?php foreach ($usuarios as $usuario): ?>
                                        <?php if ($usuario["id"] != $id_usuario_actual): ?>

                                            <option value="<?= $usuario["id"] ?>">
                                                <?= htmlspecialchars($usuario["usuario"]) ?>
                                            </option>

                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                
                                <div class="form-text">Seleccione el usuario que desea desactivar.</div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="submit" class="btn btn-danger"><i class="bi bi-trash-fill"></i> Eliminar usuario</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <!-- JavaScript de confirmacion -->
    <script src="js/eliminar_usuario.js"></script>
    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>