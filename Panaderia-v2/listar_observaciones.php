<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$id_factura = $_GET["id_factura"] ?? "";

// Obtener todas las facturas
$sqlFacturas = "SELECT id, numero_serie FROM factura ORDER BY id DESC";

$stmtFacturas = $conexion->prepare($sqlFacturas);
$stmtFacturas->execute();
$facturas = $stmtFacturas->fetchAll(PDO::FETCH_ASSOC);

// Variables para las observaciones
$observaciones = [];
$facturaSeleccionada = null;

// Si se seleccionó una factura
if ($id_factura != "") {

    // Obtener los datos de la factura seleccionada
    $sqlFactura = "SELECT id, numero_serie FROM factura WHERE id = ?";
    $stmtFactura = $conexion->prepare($sqlFactura);
    $stmtFactura->execute([$id_factura]);
    $facturaSeleccionada = $stmtFactura->fetch(PDO::FETCH_ASSOC);

    // Obtener las observaciones de esa factura
    if ($facturaSeleccionada) {
        $sqlObservaciones = "SELECT o.detalle, o.fecha_hora, u.usuario
                             FROM observacion o INNER JOIN usuario u ON o.id_USUARIO = u.id
                             WHERE o.id_FACTURA_PENDIENTE = ?
                             ORDER BY o.fecha_hora DESC";

        $stmtObservaciones = $conexion->prepare($sqlObservaciones);
        $stmtObservaciones->execute([$id_factura]);
        $observaciones = $stmtObservaciones->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar observaciones</title>
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

        <!-- Tarjeta para seleccionar factura -->
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-chat-left-text-fill"></i> Observaciones</h4>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle-fill"></i>
                            Seleccione una factura para consultar sus observaciones.
                        </div>

                        <form method="get">
                            <label for="id_factura" class="form-label"><i class="bi bi-receipt"></i> Seleccionar factura:</label>

                            <select id="id_factura" name="id_factura" class="form-select" onchange="this.form.submit()" required>
                                <option value="">-- Seleccione una factura --</option>

                                <?php foreach ($facturas as $factura): ?>
                                    <option value="<?= $factura["id"] ?>"
                                        <?= ($id_factura == $factura["id"]) ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($factura["numero_serie"]) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </form>

                     </div>
                </div>
            </div>
        </div>

        <?php if ($facturaSeleccionada): ?>

            <!-- Separación -->
            <div class="my-4"></div>

            <!-- Tarjeta de observaciones -->
            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center">
                    <h4 class="mb-0"><i class="bi bi-list-ul"></i> Observaciones de la factura</h4>
                </div>

                <div class="card-body">
                    <div class="alert alert-primary text-center"><i class="bi bi-receipt"></i>
                        <strong>
                            Factura:
                            <?= htmlspecialchars($facturaSeleccionada["numero_serie"]) ?>
                        </strong>
                    </div>

                    <?php if (count($observaciones) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>Detalle</th>
                                        <th>Fecha y hora</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    <?php foreach ($observaciones as $observacion): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($observacion["detalle"]) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($observacion["fecha_hora"]) ?></td>
                                            <td class="text-center"><i class="bi bi-person-fill"></i> <?= htmlspecialchars($observacion["usuario"]) ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-warning text-center mb-0">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Esta factura no tiene observaciones.
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>

    </div>


    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>

