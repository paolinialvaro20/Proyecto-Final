<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT factura.id, factura.numero_serie, factura.monto, factura.fecha_registro, factura.fecha_cobro, factura.estado, usuario.usuario AS usuario 
        FROM factura INNER JOIN usuario ON factura.id_USUARIO_registro = usuario.id 
        ORDER BY factura.fecha_registro DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturas</title>
    <link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">
    <link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <?php require_once "menu.php"; ?>

    <div class="container mt-5">

        <!-- =====================================================
             CARD - FACTURAS REGISTRADAS
             ===================================================== -->
        <div class="card shadow mb-5">

            <!-- Encabezado -->
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0"><i class="bi bi-list-ul"></i> Listado de facturas</h4>
            </div>

            <div class="card-body">

                <?php if (count($facturas) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Número de serie</th>
                                    <th>Monto</th>
                                    <th>Fecha de registro</th>
                                    <th>Fecha de cobro</th>
                                    <th>Estado</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($facturas as $factura): ?>
                                    <tr>
                                        <!-- ID -->
                                        <td class="text-center"><?= htmlspecialchars($factura["id"]) ?></td>

                                        <!-- Número de serie -->
                                        <td><?= htmlspecialchars($factura["numero_serie"]) ?></td>

                                        <!-- Monto -->
                                        <td class="text-end">$<?= number_format($factura["monto"], 2, ',', '.') ?></td>

                                        <!-- Fecha de registro -->
                                        <td><?= htmlspecialchars($factura["fecha_registro"]) ?></td>

                                        <!-- Fecha de cobro -->
                                        <td>
                                            <?php if ($factura["fecha_cobro"] !== null): ?>
                                                <?= htmlspecialchars($factura["fecha_cobro"]) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin cobrar</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Estado -->
                                        <td class="text-center">
                                            <?php if ($factura["estado"] == "Cobrado"): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Cobrado</span>

                                            <?php elseif ($factura["estado"] == "Pendiente"): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock-fill"></i> Pendiente</span>

                                            <?php elseif ($factura["estado"] == "Anulada"): ?>
                                                <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Anulada</span>

                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($factura["estado"]) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Usuario -->
                                        <td>
                                            <?= htmlspecialchars($factura["usuario"]) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>

                <?php else: ?>
                    <div class="alert alert-secondary text-center mb-0">
                        <i class="bi bi-receipt"></i>
                        No hay facturas registradas.
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>


    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>