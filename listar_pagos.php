<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// Consultar los pagos registrados
$sql = "SELECT p.id, p.id_factura, p.id_repartidor, p.monto, p.forma_pago, p.fecha_hora, p.liquidado, f.numero_serie, u.usuario AS nombre_repartidor
        FROM pago p INNER JOIN factura f ON p.id_factura = f.id INNER JOIN usuario u ON p.id_repartidor = u.id
        ORDER BY p.fecha_hora DESC"
    ;
$stmt = $conexion->prepare($sql);
$stmt->execute();
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar pagos</title>
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

        <!-- Botón para volver -->
        <div class="mt-4 mb-2">
            <a href="index.php" class="btn btn-secondary"><i class="bi bi-receipt"></i> Volver al listado de facturas</a>
        </div>
        <div class="card shadow mb-5">

            <!-- Encabezado -->
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0"><i class="bi bi-cash-stack"></i> Listado de pagos registrados</h4>
            </div>
            <div class="card-body">
                <?php if (count($pagos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Factura</th>
                                    <th>Repartidor</th>
                                    <th>Monto</th>
                                    <th>Forma de pago</th>
                                    <th>Fecha y hora</th>
                                    <th>Liquidación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $pago): ?>
                                    <?php
                                    // Configurar la presentación de la forma de pago
                                    switch ($pago["forma_pago"]) {
                                        case "Efectivo":
                                            $clase = "bg-success";
                                            $icono = "bi-cash";
                                            $nombre = "Efectivo";
                                            break;
                                        case "Transferencia":
                                            $clase = "bg-primary";
                                            $icono = "bi-bank";
                                            $nombre = "Transferencia";
                                            break;
                                        case "Debito":
                                            $clase = "bg-info text-dark";
                                            $icono = "bi-credit-card";
                                            $nombre = "Débito";
                                            break;
                                        case "Credito":
                                            $clase = "bg-secondary";
                                            $icono = "bi-credit-card-2-front";
                                            $nombre = "Crédito";
                                            break;
                                        case "Cheque":
                                            $clase = "bg-warning text-dark";
                                            $icono = "bi-file-earmark-text";
                                            $nombre = "Cheque";
                                            break;
                                        default:
                                            $clase = "bg-dark";
                                            $icono = "bi-wallet2";
                                            $nombre = $pago["forma_pago"];
                                            break;
                                    }
                                    ?>
                                    <tr>
                                        <!-- ID del pago -->
                                        <td class="text-center">
                                            <?= htmlspecialchars((string)$pago["id"]) ?>
                                        </td>

                                        <!-- Número de factura -->
                                        <td class="text-center">
                                            <i class="bi bi-receipt"></i>
                                            <?= htmlspecialchars($pago["numero_serie"]) ?>
                                        </td>

                                        <!-- Repartidor -->
                                        <td>
                                            <i class="bi bi-person-fill"></i>
                                            <?= htmlspecialchars($pago["nombre_repartidor"]) ?>
                                        </td>

                                        <!-- Monto -->
                                        <td class="text-end text-nowrap">
                                            $ <?= number_format((float)$pago["monto"], 2, ",", ".") ?>
                                        </td>

                                        <!-- Forma de pago -->
                                        <td class="text-center">
                                            <span class="badge <?= $clase ?>">
                                                <i class="bi <?= $icono ?>"></i>
                                                <?= htmlspecialchars($nombre) ?>
                                            </span>
                                        </td>

                                        <!-- Fecha y hora -->
                                        <td class="text-center text-nowrap">
                                            <i class="bi bi-calendar-event"></i>
                                            <?php
                                            $fecha = new DateTime($pago["fecha_hora"]);
                                            echo htmlspecialchars($fecha->format("d/m/Y H:i"));
                                            ?>
                                        </td>

                                        <!-- Estado de liquidación -->
                                        <td class="text-center">
                                            <?php if ((int)$pago["liquidado"] === 1): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Liquidado</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock-fill"></i> Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i>
                        No hay pagos registrados en el sistema.
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    
    
    <!-- JavaScript de Bootstrap -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>