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
// FUNCIONES
// =====================================================
function esc($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

function pesos($valor) {
    return "$" . number_format(
        (float)$valor,
        2,
        ",",
        "."
    );
}

// =====================================================
// VARIABLES
// =====================================================
$id_cajero = $_GET["id_cajero"] ?? "";
$fecha = $_GET["fecha"] ?? "";

$pagos = [];
$cajero = null;
$error = "";

$totales = [
    "Efectivo" => 0,
    "Transferencia" => 0,
    "Debito" => 0,
    "Credito" => 0,
    "Cheque" => 0
];

try {

    // =================================================
    // VALIDAR CAJERO Y FECHA
    // =================================================
    $fechaValida = DateTimeImmutable::createFromFormat(
        "!Y-m-d",
        $fecha
    );

    if (
        !ctype_digit((string)$id_cajero) ||
        (int)$id_cajero < 1 ||
        !$fechaValida ||
        $fechaValida->format("Y-m-d") !== $fecha
    ) {
        throw new Exception(
            "Seleccioná un cajero y una fecha válidos."
        );
    }

    // =================================================
    // BUSCAR CAJERO
    // =================================================
    $sqlCajero = "
        SELECT u.id, u.usuario
        FROM usuario u
        INNER JOIN rol r ON r.id = u.id_ROL
        WHERE u.id = :id
          AND LOWER(r.nombre) IN ('cajero', 'cajera')
    ";

    $stmt = $conexion->prepare($sqlCajero);
    $stmt->execute([
        ":id" => $id_cajero
    ]);

    $cajero = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cajero) {
        throw new Exception(
            "No se encontró el cajero."
        );
    }

    // =================================================
    // ESTABLECER RANGO DE FECHAS
    // =================================================
    $inicio = $fecha . " 00:00:00";

    $fin = $fechaValida
        ->modify("+1 day")
        ->format("Y-m-d") . " 00:00:00";

    // =================================================
    // BUSCAR PAGOS LIQUIDADOS
    // =================================================
    $sqlPagos = "
        SELECT
            p.id,
            p.monto,
            p.forma_pago,
            p.fecha_hora,
            f.numero_serie
        FROM pago p
        INNER JOIN factura f
            ON p.id_factura = f.id
        WHERE p.id_repartidor = :id_cajero
          AND p.fecha_hora >= :inicio
          AND p.fecha_hora < :fin
          AND p.liquidado = 1
        ORDER BY p.fecha_hora ASC, p.id ASC
    ";

    $stmtPagos = $conexion->prepare($sqlPagos);

    $stmtPagos->execute([
        ":id_cajero" => $id_cajero,
        ":inicio" => $inicio,
        ":fin" => $fin
    ]);

    $pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

    // =================================================
    // CALCULAR TOTALES
    // =================================================
    foreach ($pagos as $pago) {

        $forma = trim((string)$pago["forma_pago"]);
        $monto = (float)$pago["monto"];

        if (array_key_exists($forma, $totales)) {
            $totales[$forma] += $monto;
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

// =====================================================
// TOTAL GENERAL
// =====================================================
$totalGeneral = array_sum($totales);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Liquidación confirmada - Cajero</title>

    <!-- Bootstrap -->
    <link
        rel="stylesheet"
        href="css/bootstrap-5.3.8/css/bootstrap.min.css"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css"
    >

    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/estilo.css">

    <!-- Estilos para impresión -->
    <style>
        @media print {

            body {
                background: white !important;
            }

            .no-imprimir {
                display: none !important;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
            }

            .comprobante {
                margin: 0 !important;
            }
        }
    </style>
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

                    <img
                        src="imagenes/logo.png"
                        alt="Logo de la empresa"
                        class="logo-comprobante"
                    >

                    <hr>

                    <h4 class="fw-bold">
                        COMPROBANTE DE LIQUIDACIÓN DIARIA
                    </h4>

                </div>

                <!-- =================================
                     MENSAJE DE ERROR
                     ================================= -->
                <?php if ($error !== ""): ?>

                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= esc($error) ?>
                    </div>

                <?php else: ?>

                    <!-- =================================
                         DATOS DE LA LIQUIDACIÓN
                         ================================= -->
                    <div class="datos-comprobante mb-4">

                        <div class="row g-3 align-items-center">

                            <!-- Cajero -->
                            <div class="col-6">

                                <p class="mb-1 text-muted">
                                    <i class="bi bi-person-fill"></i>
                                    Cajero
                                </p>

                                <h6 class="mb-0">
                                    <?= esc($cajero["usuario"]) ?>
                                </h6>

                            </div>

                            <!-- Fecha -->
                            <div class="col-6">

                                <p class="mb-1 text-muted">
                                    <i class="bi bi-calendar-event"></i>
                                    Fecha de liquidación
                                </p>

                                <h6 class="mb-0">
                                    <?= esc($fecha) ?>
                                </h6>

                            </div>

                        </div>

                    </div>


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
                                                <?= esc($pago["id"]) ?>
                                            </td>

                                            <td class="text-center">
                                                <?= esc($pago["numero_serie"]) ?>
                                            </td>

                                            <td class="text-center">
                                                <?= esc(
                                                    date(
                                                        "H:i",
                                                        strtotime($pago["fecha_hora"])
                                                    )
                                                ) ?>
                                            </td>

                                            <td class="text-center">

                                                <?php
                                                $nombreFormaPago = [
                                                    "Debito" => "Débito",
                                                    "Credito" => "Crédito"
                                                ][$pago["forma_pago"]]
                                                ?? $pago["forma_pago"];
                                                ?>

                                                <?= esc($nombreFormaPago) ?>

                                            </td>

                                            <td class="text-end text-nowrap">
                                                <?= pesos($pago["monto"]) ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="alert alert-secondary text-center">
                            <i class="bi bi-info-circle-fill"></i>
                            No se encontraron pagos liquidados
                            para esta fecha.
                        </div>

                    <?php endif; ?>

                    <!-- =================================
                         RESUMEN Y TOTAL GENERAL
                         ================================= -->
                    <div class="card bg-light border-dark mt-4">

                        <div class="card-body">

                            <p class="text-center mb-3">
                                Cantidad de pagos:
                                <?= count($pagos) ?>
                            </p>

                            <hr>

                            <!-- Resumen por forma de pago -->
                            <h5 class="mb-3">
                                <i class="bi bi-bar-chart-fill"></i>
                                Resumen por forma de pago
                            </h5>

                            <div class="totales">

                                <p>
                                    <strong>Efectivo:</strong>
                                    <?= pesos($totales["Efectivo"]) ?>
                                </p>

                                <p>
                                    <strong>Transferencia:</strong>
                                    <?= pesos($totales["Transferencia"]) ?>
                                </p>

                                <p>
                                    <strong>Débito:</strong>
                                    <?= pesos($totales["Debito"]) ?>
                                </p>

                                <p>
                                    <strong>Crédito:</strong>
                                    <?= pesos($totales["Credito"]) ?>
                                </p>

                                <p>
                                    <strong>Cheque:</strong>
                                    <?= pesos($totales["Cheque"]) ?>
                                </p>

                            </div>

                            <hr>

                            <!-- Total general -->
                            <div class="total-general text-center fw-bold">

                                <strong>TOTAL LIQUIDADO:</strong>

                                <div class="text-success">
                                    <?= pesos($totalGeneral) ?>
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- =================================
                         FIRMAS
                         ================================= -->
                    <div class="firmas">

                        <div class="firma">
                            Firma del cajero
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

                    <button
                        type="button"
                        class="btn btn-dark me-2"
                        onclick="window.print()"
                    >
                        <i class="bi bi-file-earmark-pdf"></i>
                        Guardar como PDF
                    </button>

                    <a
                        href="liquidacion_diaria_cajero.php"
                        class="btn btn-secondary"
                    >
                        <i class="bi bi-arrow-left-circle"></i>
                        Volver a liquidación diaria
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- JavaScript de Bootstrap -->
<script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

</body>
</html>