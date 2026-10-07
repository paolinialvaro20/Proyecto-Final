<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";

// Usuario que inició sesión
$id_usuario_registro = $_SESSION["id_usuario"];

// Obtener los datos del usuario que inició sesión
$sqlUsuario = "SELECT usuario FROM usuario WHERE id = ?";
$stmtUsuario = $conexion->prepare($sqlUsuario);
$stmtUsuario->execute([$id_usuario_registro]);
$usuarioRegistro = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

// Fecha y hora actual para mostrar en pantalla
$fechaRegistro = date("Y-m-d H:i:s");

// Obtener usuarios activos para el desplegable
$sqlUsuarios = "SELECT id, usuario FROM usuario WHERE activo = 1 ORDER BY usuario";
$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_serie = trim($_POST["numero_serie"]);
    $monto = $_POST["monto"];
    $id_usuario_asignado = $_POST["id_usuario_asignado"];

    // Determinar estado y fecha de cobro
    if (isset($_POST["cobrado"])) {
        $estado = "Cobrado";
        $fecha_cobro = date("Y-m-d H:i:s");

    } else {
        $estado = "Pendiente";
        $fecha_cobro = null;
    }

    $sql = "INSERT INTO factura(numero_serie, monto, fecha_registro, fecha_cobro, estado, id_usuario_registro, id_usuario_asignado) 
    VALUES(?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    if ($stmt->execute([$numero_serie, $monto, $fechaRegistro, $fecha_cobro, $estado, $id_usuario_registro, $id_usuario_asignado])) {
        $mensaje = "Factura agregada correctamente.";

    } else {
        $mensaje = "Error al guardar la factura.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar factura</title>
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
            <div class="col-md-8 col-lg-7">
                <div class="card shadow">

                    <!-- Encabezado -->
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-receipt"></i> Nueva factura</h4>
                    </div>

                    <div class="card-body">

                        <!-- Mensaje -->
                        <?php if ($mensaje != ""): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill"></i>
                                <?= htmlspecialchars($mensaje) ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>


                        <form method="post">

                            <!-- Usuario que registra -->
                            <div class="mb-3">
                                <label for="usuario_registro" class="form-label"><i class="bi bi-person-fill"></i> Usuario que registra</label>
                                <input type="text" id="usuario_registro" class="form-control"
                                    value="<?= htmlspecialchars($usuarioRegistro["usuario"]) ?>"
                                    readonly
                                >
                            </div>

                            <!-- Fecha de registro -->
                            <div class="mb-3">
                                <label for="fecha_registro" class="form-label">
                                    <i class="bi bi-calendar3"></i> Fecha y hora de registro</label>
                                <input type="text" id="fecha_registro" class="form-control"
                                    value="<?= htmlspecialchars($fechaRegistro) ?>"
                                    readonly
                                >
                            </div>

                            <!-- Número de serie -->
                            <div class="mb-3">
                                <label for="numero_serie" class="form-label">
                                    <i class="bi bi-upc"></i> Número de serie
                                </label>

                                <input type="text" id="numero_serie" name="numero_serie" class="form-control" maxlength="20" 
                                placeholder="Ingrese el número de serie" required>
                            </div>

                            <!-- Monto -->
                            <div class="mb-3">
                                <label for="monto" class="form-label"><i class="bi bi-currency-dollar"></i> Monto</label>
                                <input type="number" id="monto" name="monto" class="form-control" step="0.01" min="0" placeholder="Ingrese el monto" required>
                            </div>

                            <!-- Usuario asignado -->
                            <div class="mb-3">
                                <label for="id_usuario_asignado" class="form-label"><i class="bi bi-person-check-fill"></i> Usuario asignado</label>
                                <select id="id_usuario_asignado" name="id_usuario_asignado" class="form-select" required>
                                    <option value="">Seleccione un usuario</option>

                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= $usuario["id"] ?>">
                                            <?= htmlspecialchars($usuario["usuario"]) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <!-- Estado -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="cobrado" value="1" id="cobrado">
                                <label class="form-check-label" for="cobrado"><i class="bi bi-check-circle-fill"></i> Cobrado</label>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save-fill"></i> Guardar factura</button>
                                <a href="index.php" class="btn btn-secondary px-4"><i class="bi bi-x-circle-fill"></i> Cancelar</a>
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