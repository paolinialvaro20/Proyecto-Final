<?php
session_start();
require_once "conexion.php";

// =====================================================
// VERIFICAR SESIÓN
// =====================================================
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// =====================================================
// DATOS RECIBIDOS
// =====================================================
$id_repartidor = $_GET["id_repartidor"] ?? "";
$fecha = $_GET["fecha"] ?? "";

// =====================================================
// BUSCAR REPARTIDOR
// =====================================================
$repartidor = null;
$pagos = [];
$totalGeneral = 0;
$totalEfectivo = 0;
$totalTransferencia = 0;
$totalCredito = 0;
$totalDebito = 0;

try {
    $sql = "SELECT usuario FROM usuario WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([":id" => $id_repartidor]);
    $repartidor = $stmt->fetch(PDO::FETCH_ASSOC);

    // =============================================
    // BUSCAR PAGOS LIQUIDADOS
    // =============================================
    $sqlPagos = "SELECT p.id, p.monto, p.forma_pago, p.fecha_hora, f.numero_serie
        FROM pago p INNER JOIN factura f ON p.id_factura = f.id
        WHERE p.id_repartidor = :id_repartidor AND DATE(p.fecha_hora) = :fecha AND p.liquidado = 1
        ORDER BY p.fecha_hora ASC
    ";

    $stmtPagos = $conexion->prepare($sqlPagos);
    $stmtPagos->execute([":id_repartidor" => $id_repartidor, ":fecha" => $fecha]);
    $pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

    // =============================================
    // CALCULAR TOTALES
    // =============================================
    foreach ($pagos as $pago) {
        $monto = (float) $pago["monto"];
        $totalGeneral += $monto;

        switch ($pago["forma_pago"]) {
            case "Efectivo":
                $totalEfectivo += $monto;
                break;
            case "Transferencia":
                $totalTransferencia += $monto;
                break;
            case "Credito":
                $totalCredito += $monto;
                break;
            case "Debito":
                $totalDebito += $monto;
                break;
        }
    }
} catch (PDOException $e) {
    $error = "No se pudo obtener la información de la liquidación.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidación confirmada</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container mt-4 mb-5">
        <div class="comprobante">

            <!-- =========================================
                ENCABEZADO
                ========================================= -->
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="encabezado-comprobante">
                        <img src="imagenes/logo.png" alt="Logo de la empresa" class="logo-comprobante">
                        <hr>
                        <h4 class="fw-bold">COMPROBANTE DE LIQUIDACIÓN DIARIA</h4>
                    </div>

                    <!-- =================================
                        DATOS DE LA LIQUIDACIÓN
                        ================================= -->             
                    <div class="datos-comprobante mb-4">
                        <div class="row g-3 align-items-center">
                            <!-- Repartidor -->
                            <div class="col-6">
                                <p class="mb-1 text-muted">
                                    <i class="bi bi-person-fill"></i>
                                    Repartidor
                                </p>
                                <h6 class="mb-0">
                                    <?= htmlspecialchars(
                                        $repartidor["usuario"] ?? "No disponible"
                                    ) ?>
                                </h6>
                            </div>

                            <!-- Fecha de liquidación -->
                            <div class="col-6">
                                <p class="mb-1 text-muted">
                                    <i class="bi bi-calendar-event"></i>
                                    Fecha de liquidación
                                </p>
                                <h6 class="mb-0">
                                    <?= htmlspecialchars($fecha) ?>
                                </h6>
                            </div>
                        </div>
                    </div>

                    <!-- =================================
                        MENSAJE DE ERROR
                        ================================= -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php else: ?>

                        <!-- =================================
                            TABLA DE PAGOS
                            ================================= -->
                        <h5 class="mb-3">
                            <i class="bi bi-receipt"></i>
                            Detalle de pagos liquidados
                        </h5>

                        <?php if (count($pagos) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-dark text-center">
                                        <tr>
                                            <th>ID pago</th>
                                            <th>Factura</th>
                                            <th>Hora</th>
                                            <th>Forma de pago</th>
                                            <th>Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pagos as $pago): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <?= htmlspecialchars($pago["id"]) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($pago["numero_serie"]) ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= htmlspecialchars(date("H:i",strtotime($pago["fecha_hora"]))) ?>
                                                </td>
                                                <td class="text-center">  
                                                    <?php
                                                        $formaPago = $pago["forma_pago"];
                                                        $nombreFormaPago = match ($formaPago) {
                                                            "Credito" => "Crédito",
                                                            "Debito" => "Débito",
                                                            default => $formaPago
                                                        };
                                                    ?>
                                                    <span>
                                                        <?= htmlspecialchars($nombreFormaPago) ?>
                                                    </span>
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    $<?= number_format((float) $pago["monto"], 2, ",", ".") ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                    </tbody>
                                </table>
                            </div>

                        <?php else: ?>
                            <div class="alert alert-secondary text-center">
                                <i class="bi bi-info-circle-fill"></i>
                                No se encontraron pagos liquidados para esta fecha.
                            </div>
                        <?php endif; ?>

                        <!-- =================================
                            RESUMEN Y TOTAL GENERAL
                            ================================= -->
                        <div class="card bg-light border-dark mt-4">
                            <div class="card-body">
                                <p class="text-center mb-3">Cantidad de pagos: <?= count($pagos) ?></p>
                                <hr>

                                <!-- Resumen por forma de pago -->
                                <h5 class="mb-3"><i class="bi bi-bar-chart-fill"></i> Resumen por forma de pago</h5>
                                <div class="totales">
                                    <p>
                                        <strong>Efectivo:</strong>
                                        $<?= number_format($totalEfectivo, 2, ",", ".") ?>
                                    </p>
                                    <p>
                                        <strong>Transferencia:</strong>
                                        $<?= number_format($totalTransferencia, 2, ",", ".") ?>
                                    </p>
                                    <p>
                                        <strong>Débito:</strong>
                                        $<?= number_format($totalDebito, 2, ",", ".") ?>
                                    </p>
                                    <p>
                                        <strong>Crédito:</strong>
                                        $<?= number_format($totalCredito, 2, ",", ".") ?>
                                    </p>
                                </div>
                                <hr>

                                <!-- Total general -->
                                <div class="total-general text-center fw-bold">
                                    <strong>TOTAL LIQUIDADO:</strong>
                                    <div class="text-success">
                                        $<?= number_format($totalGeneral, 2, ",", ".") ?>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- =================================
                            FIRMAS
                            ================================= -->
                        <div class="firmas">
                            <div class="firma">
                                Firma del repartidor
                            </div>
                            <div class="firma">
                                Firma del responsable
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- =====================================
                        BOTONES
                        ===================================== -->
                    <div class="text-center mt-4 no-imprimir">
                        <button type="button" class="btn btn-dark me-2" onclick="window.print()">
                            <i class="bi bi-file-earmark-pdf"></i>
                            Guardar como PDF
                        </button>
                        <a href="liquidacion_diaria.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left-circle"></i>
                            Volver a liquidación diaria
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    

    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>