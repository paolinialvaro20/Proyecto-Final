<?php
session_start();
require_once "conexion.php";

// ==========================================
// VERIFICAR SESIÓN
// ==========================================
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$tipoMensaje = "";

// ==========================================
// PROCESAR FORMULARIO
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $contraseniaActual = $_POST["contrasenia_actual"] ?? "";
    $contraseniaNueva = $_POST["contrasenia_nueva"] ?? "";
    $confirmarContrasenia = $_POST["confirmar_contrasenia"] ?? "";

    // Validar que los campos estén completos
    if (
        $contraseniaActual === "" ||
        $contraseniaNueva === "" ||
        $confirmarContrasenia === ""
    ) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipoMensaje = "danger";

    } elseif ($contraseniaNueva !== $confirmarContrasenia) {
        $mensaje = "Las nuevas contraseñas no coinciden.";
        $tipoMensaje = "danger";

    } elseif ($contraseniaActual === $contraseniaNueva) {
        $mensaje = "La nueva contraseña debe ser diferente de la actual.";
        $tipoMensaje = "danger";

    } else {
        // ==========================================
        // OBTENER CONTRASEÑA DEL USUARIO LOGUEADO
        // ==========================================
        $sql = "SELECT contrasenia FROM usuario WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$_SESSION["id_usuario"]]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            $mensaje = "No se encontró el usuario.";
            $tipoMensaje = "danger";
        } elseif (!password_verify(
            $contraseniaActual,
            $usuario["contrasenia"]
        )) {
            $mensaje = "La contraseña actual es incorrecta.";
            $tipoMensaje = "danger";
        } else {

            // ==========================================
            // ACTUALIZAR CONTRASEÑA
            // ==========================================
            $contraseniaHash = password_hash($contraseniaNueva, PASSWORD_DEFAULT);
            $sqlActualizar = "UPDATE usuario SET contrasenia = ? WHERE id = ?";
            $stmtActualizar = $conexion->prepare($sqlActualizar);
            $stmtActualizar->execute([$contraseniaHash, $_SESSION["id_usuario"]]);
            $mensaje = "La contraseña se cambió correctamente.";
            $tipoMensaje = "success";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña</title>
    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="css/bootstrap-5.3.8/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
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
                        <h3 class="mb-0"><i class="bi bi-shield-lock-fill"></i> Cambiar contraseña</h3>
                    </div>

                    <!-- Cuerpo -->
                    <div class="card-body">

                        <!-- Mensajes -->
                        <?php if ($mensaje !== ""): ?>
                            <div class="alert alert-<?= htmlspecialchars($tipoMensaje) ?>
                                alert-dismissible fade show" role="alert">
                                <i class="bi <?= $tipoMensaje === "success"
                                    ? "bi-check-circle-fill"
                                    : "bi-exclamation-triangle-fill" ?>">
                                </i>
                                <?= htmlspecialchars($mensaje) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">

                            <!-- Contraseña actual -->
                            <div class="mb-3">
                                <label for="contrasenia_actual" class="form-label">
                                    <i class="bi bi-lock-fill"></i>
                                    Contraseña actual
                                </label>
                                <input type="password" class="form-control" id="contrasenia_actual" name="contrasenia_actual"
                                    placeholder="Ingrese su contraseña actual" autocomplete="current-password" required
                                >
                            </div>

                            <!-- Nueva contraseña -->
                            <div class="mb-3">
                                <label for="contrasenia_nueva" class="form-label"><i class="bi bi-key-fill"></i> Nueva contraseña</label>
                                <input type="password" class="form-control" id="contrasenia_nueva" name="contrasenia_nueva"
                                    placeholder="Ingrese la nueva contraseña" autocomplete="new-password" required
                                >
                            </div>

                            <!-- Confirmar contraseña -->
                            <div class="mb-4">
                                <label for="confirmar_contrasenia" class="form-label">
                                    <i class="bi bi-shield-check"></i>
                                    Confirmar nueva contraseña
                                </label>
                                <input type="password" class="form-control" id="confirmar_contrasenia" name="confirmar_contrasenia"
                                    placeholder="Repita la nueva contraseña" autocomplete="new-password" required
                                >
                            </div>

                            <!-- Botón -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Cambiar contraseña
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <!-- JavaScript de Bootstrap -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>