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
// VARIABLES
// =====================================================
$error = "";
$pagos = [];
$repartidores = [];
$id_repartidor_seleccionado = $_GET["id_repartidor"] ?? "";
$fecha_seleccionada = $_GET["fecha"] ?? date("Y-m-d");

// Totales
$total_general = 0;
$total_efectivo = 0;
$total_transferencia = 0;
$total_credito = 0;
$total_debito = 0;

// =====================================================
// OBTENER REPARTIDORES ACTIVOS
// =====================================================
try {
    $sqlRepartidores = "SELECT u.id, u.usuario FROM usuario u INNER JOIN rol r ON u.id_ROL = r.id
        WHERE u.activo = 1 AND r.nombre = 'Repartidor'
        ORDER BY u.usuario
    ";
    $stmtRepartidores = $conexion->prepare($sqlRepartidores);
    $stmtRepartidores->execute();
    $repartidores = $stmtRepartidores->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "No se pudieron cargar los repartidores.";
}

// =====================================================
// BUSCAR PAGOS PENDIENTES DE LIQUIDACIÓN
// =====================================================
if (
    $id_repartidor_seleccionado !== ""
    && $fecha_seleccionada !== ""
) {
    try {
        $sqlPagos = "SELECT p.id, p.id_factura, p.id_repartidor, p.monto, p.forma_pago, p.fecha_hora, p.liquidado, f.numero_serie, u.usuario AS nombre_repartidor
            FROM pago p INNER JOIN factura f ON p.id_factura = f.id INNER JOIN usuario u ON p.id_repartidor = u.id
            WHERE p.id_repartidor = :id_repartidor AND DATE(p.fecha_hora) = :fecha AND p.liquidado = 0
            ORDER BY p.fecha_hora ASC
        ";
        $stmtPagos = $conexion->prepare($sqlPagos);
        $stmtPagos->execute([":id_repartidor" => $id_repartidor_seleccionado, ":fecha" => $fecha_seleccionada]);
        $pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

        // =============================================
        // CALCULAR TOTALES POR FORMA DE PAGO
        // =============================================
        foreach ($pagos as $pago) {
            $monto = (float) $pago["monto"];
            $total_general += $monto;
            switch ($pago["forma_pago"]) {
                case "Efectivo":
                    $total_efectivo += $monto;
                    break;
                case "Transferencia":
                    $total_transferencia += $monto;
                    break;
                case "Credito":
                    $total_credito += $monto;
                    break;
                case "Debito":
                    $total_debito += $monto;
                    break;
            }
        }
    } catch (PDOException $e) {
        $pagos = [];
        $error = "Error al obtener los pagos del repartidor.";
    }
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
    <?php require_once "menu.php"; ?>
    <div class="container mt-5 mb-5">

        <!-- ================================================
            FORMULARIO DE BÚSQUEDA
            ================================================ -->
        <div class="card shadow mb-4">
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0"><i class="bi bi-cash-stack"></i> Liquidación diaria por repartidor</h4>
            </div>
            <div class="card-body">
                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                <form method="GET" action="liquidacion_diaria.php">
                    <div class="row g-3 align-items-end">

                        <!-- Repartidor -->
                        <div class="col-md-6">
                            <label for="id_repartidor" class="form-label"><i class="bi bi-person-fill"></i> Repartidor</label>
                            <select name="id_repartidor" id="id_repartidor" class="form-select" required>
                                <option value="">-- Seleccionar repartidor --</option>
                                <?php foreach ($repartidores as $repartidor): ?>
                                    <option
                                        value="<?= htmlspecialchars($repartidor["id"]) ?>"
                                        <?= (string)$id_repartidor_seleccionado ===
                                            (string)$repartidor["id"] ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($repartidor["usuario"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Fecha -->
                        <div class="col-md-4">
                            <label for="fecha" class="form-label"><i class="bi bi-calendar-event"></i> Fecha de liquidación</label>
                            <input type="date" name="fecha" id="fecha" class="form-control" value="<?= htmlspecialchars($fecha_seleccionada) ?>" required>
                        </div>

                        <!-- Botón buscar -->
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <!-- ================================================
            RESULTADOS
            ================================================ -->
        <?php if (
            $id_repartidor_seleccionado !== ""
            && $fecha_seleccionada !== ""
        ): ?>

            <div class="card shadow mb-4">
                <div class="card-header bg-dark text-white text-center">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Pagos pendientes de liquidación</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong> <i class="bi bi-calendar-check"></i> Fecha:</strong>
                            <?= htmlspecialchars($fecha_seleccionada) ?>
                        </div>

                        <?php if (count($pagos) > 0): ?>
                            <div class="col-md-6">
                                <strong><i class="bi bi-person-badge"></i> Repartidor:</strong>
                                <?= htmlspecialchars($pagos[0]["nombre_repartidor"]) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($pagos) > 0): ?>

                        <!-- =================================
                            TABLA DE PAGOS
                            ================================= -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>ID pago</th>
                                        <th>Número de factura</th>
                                        <th>Fecha y hora</th>
                                        <th>Forma de pago</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagos as $pago): ?>
                                        <tr>
                                            <td class="text-center"><?= htmlspecialchars($pago["id"]) ?></td>
                                            <td><?= htmlspecialchars($pago["numero_serie"]) ?></td>
                                            <td><?= htmlspecialchars($pago["fecha_hora"]) ?></td>
                                            <td class="text-center">
                                                <?php
                                                $formaPago = $pago["forma_pago"];
                                                $claseBadge = match ($formaPago) {
                                                    "Efectivo" => "bg-success",
                                                    "Transferencia" => "bg-primary",
                                                    "Credito" => "bg-info text-dark",
                                                    "Debito" => "bg-warning text-dark",
                                                    default => "bg-secondary"
                                                };

                                                $nombreFormaPago = match ($formaPago) {
                                                    "Credito" => "Crédito",
                                                    "Debito" => "Débito",
                                                    default => $formaPago
                                                };
                                                ?>

                                                <span class="badge <?= $claseBadge ?>"><?= htmlspecialchars($nombreFormaPago) ?></span>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                $<?= number_format($pago["monto"], 2, ",", ".") ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- =================================
                            RESUMEN DE PAGOS
                            ================================= -->
                        <h5 class="mt-4 mb-3"><i class="bi bi-bar-chart-fill"></i> Resumen de la liquidación</h5>
                        <div class="row g-3">

                            <!-- Efectivo -->
                            <div class="col-sm-6 col-lg-3">
                                <div class="card border-success h-100">
                                    <div class="card-body">
                                        <div class="text-success mb-2">
                                            <i class="bi bi-cash fs-3"></i>
                                        </div>
                                        <h6 class="text-muted">Total efectivo</h6>
                                        <h5 class="mb-0 text-success">
                                            $<?= number_format($total_efectivo, 2, ",", ".") ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>

                            <!-- Transferencia -->
                            <div class="col-sm-6 col-lg-3">
                                <div class="card border-primary h-100">
                                    <div class="card-body">
                                        <div class="text-primary mb-2">
                                            <i class="bi bi-bank fs-3"></i>
                                        </div>
                                        <h6 class="text-muted">Total transferencia</h6>
                                        <h5 class="mb-0 text-primary">
                                            $<?= number_format($total_transferencia, 2, ",", ".") ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>

                            <!-- Crédito -->
                            <div class="col-sm-6 col-lg-3">
                                <div class="card border-info h-100">
                                    <div class="card-body">
                                        <div class="text-info mb-2">
                                            <i class="bi bi-credit-card fs-3"></i>
                                        </div>
                                        <h6 class="text-muted">Total crédito</h6>
                                        <h5 class="mb-0 text-info">
                                            $<?= number_format($total_credito, 2, ",", ".") ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>

                            <!-- Débito -->
                            <div class="col-sm-6 col-lg-3">
                                <div class="card border-warning h-100">
                                    <div class="card-body">
                                        <div class="text-warning mb-2">
                                            <i class="bi bi-credit-card-2-front fs-3"></i>
                                        </div>
                                        <h6 class="text-muted">Total débito</h6>
                                        <h5 class="mb-0 text-dark">
                                            $<?= number_format($total_debito, 2, ",", ".") ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total general -->
                        <div class="card bg-light border-dark mt-4">
                            <div class="card-body text-center">
                                <p class="text-muted mb-1">
                                    <i class="bi bi-calculator"></i>
                                    Cantidad de pagos: <?= count($pagos) ?>
                                </p>
                                <h4 class="mb-0">
                                    TOTAL A LIQUIDAR:
                                    <span class="text-success">
                                        $<?= number_format($total_general, 2, ",", ".") ?>
                                    </span>
                                </h4>
                            </div>
                        </div>

                        <!-- =================================
                            CONFIRMAR LIQUIDACIÓN
                            ================================= -->
                        <form
                            method="POST"
                            action="confirmar_liquidacion.php"
                            class="mt-4 text-center"
                            onsubmit="return confirm('¿Está seguro de confirmar esta liquidación? Los pagos seleccionados quedarán liquidados.');"
                        >
                            <input
                                type="hidden"
                                name="id_repartidor"
                                value="<?= htmlspecialchars($id_repartidor_seleccionado) ?>"
                            >
                            <input
                                type="hidden"
                                name="fecha"
                                value="<?= htmlspecialchars($fecha_seleccionada) ?>"
                            >
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle-fill"></i>
                                Confirmar liquidación
                            </button>
                        </form>

                    <?php else: ?>
                        <div class="alert alert-secondary text-center mb-0">
                            <i class="bi bi-info-circle-fill"></i>
                            No hay pagos pendientes de liquidación para este
                            repartidor en la fecha seleccionada.
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>
    </div>
    

    <!-- JavaScript de Bootstrap -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>