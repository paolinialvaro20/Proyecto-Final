<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

function esc($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

function pesos($valor) {
    return "$" . number_format((float)$valor, 2, ",", ".");
}

$id_liquidacion = $_GET["id_liquidacion"] ?? "";
$error = "";
$liquidacion = null;
$pagos = [];
$fechaPagos = "No disponible";

try {
    if (!ctype_digit((string)$id_liquidacion) || (int)$id_liquidacion < 1) {
        throw new Exception("El identificador de la liquidación no es válido.");
    }

    // Obtener la liquidación, el cajero, el rol y quien la registró.
    $sql = "
        SELECT
            l.*,
            u.usuario AS nombre_liquidado,
            r.nombre AS nombre_rol,
            ur.usuario AS nombre_registro
        FROM liquidacion l
        INNER JOIN usuario u ON u.id = l.id_usuario_liquidado
        INNER JOIN rol r ON r.id = u.id_ROL
        INNER JOIN usuario ur ON ur.id = l.id_usuario_registro
        WHERE l.id = :id
          AND LOWER(r.nombre) IN ('cajero', 'cajera')
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([":id" => $id_liquidacion]);
    $liquidacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$liquidacion) {
        throw new Exception("No se encontró la liquidación del cajero.");
    }

    // Recuperar exclusivamente los pagos asociados a esta liquidación.
    $sqlPagos = "
        SELECT
            p.id,
            p.monto,
            p.forma_pago,
            p.fecha_hora,
            f.numero_serie
        FROM pago p
        INNER JOIN factura f ON f.id = p.id_factura
        WHERE p.id_liquidacion = :id_liquidacion
        ORDER BY p.fecha_hora ASC, p.id ASC
    ";
    $stmt = $conexion->prepare($sqlPagos);
    $stmt->execute([":id_liquidacion" => $id_liquidacion]);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($pagos) {
        $fechaPagos = date("Y-m-d", strtotime($pagos[0]["fecha_hora"]));
    }

    if (count($pagos) !== (int)$liquidacion["cantidad_pagos"]) {
        throw new Exception("La cantidad de pagos asociados no coincide con el resumen de la liquidación.");
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidación confirmada - Cajero</title>
    <link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        @media print {
            body { background: white !important; }
            .no-imprimir { display: none !important; }
            .container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .card { box-shadow: none !important; }
            .comprobante { margin: 0 !important; }
        }
    </style>
    <link rel="stylesheet" href="fondo-sistema.css?v=1">
    <link rel="stylesheet" href="tema-don-diego.css?v=2">
<link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
</head>
<body>
<div class="container mt-4 mb-5">
    <div class="comprobante">
        <div class="card shadow">
            <div class="card-body p-4">
                <div class="encabezado-comprobante text-center">
                    <img src="imagenes/logo.png" alt="Logo de la empresa" class="logo-comprobante">
                    <hr>
                    <h4 class="fw-bold">COMPROBANTE DE LIQUIDACIÓN</h4>
                </div>

                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= esc($error) ?>
                    </div>
                <?php else: ?>
                    <div class="datos-comprobante mb-4">
                        <div class="row g-3">
                            
                            <!-- Cajero -->
                            <div class="col-md-6">
                                <p class="mb-1 text-muted"><i class="bi bi-person-fill"></i> Cajero</p>
                                <h6><?= esc($liquidacion["nombre_liquidado"] ?? "No disponible") ?></h6>
                            </div>

                            <!-- Fecha -->
                            <div class="col-md-6">
                                <p class="mb-1 text-muted"><i class="bi bi-calendar-event"></i> Fecha de liquidación</p>
                                <h6><?= esc($fechaPagos) ?></h6>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3"><i class="bi bi-receipt"></i> Detalle de pagos liquidados</h5>
                    <?php if ($pagos): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>ID pago</th>
                                        <th>Factura</th>
                                        <th>Fecha y hora</th>
                                        <th>Forma de pago</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($pagos as $pago): ?>
                                    <?php
                                    $forma = [
                                        "Debito" => "Débito",
                                        "Credito" => "Crédito"
                                    ][$pago["forma_pago"]] ?? $pago["forma_pago"];
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= esc($pago["id"]) ?></td>
                                        <td class="text-center"><?= esc($pago["numero_serie"]) ?></td>
                                        <td class="text-center"><?= esc($pago["fecha_hora"]) ?></td>
                                        <td class="text-center"><?= esc($forma) ?></td>
                                        <td class="text-end text-nowrap"><?= pesos($pago["monto"]) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center">No hay pagos asociados a esta liquidación.</div>
                    <?php endif; ?>

                    <div class="card bg-light border-dark mt-4">
                        <div class="card-body">
                            <p class="text-center mb-3">
                                Cantidad de pagos: <?= esc($liquidacion["cantidad_pagos"]) ?>
                            </p>
                            <hr>
                            <h5 class="mb-3"><i class="bi bi-bar-chart-fill"></i> Resumen por forma de pago</h5>
                            <div class="totales">
                                <p><strong>Efectivo:</strong> <?= pesos($liquidacion["total_efectivo"]) ?></p>
                                <p><strong>Transferencia:</strong> <?= pesos($liquidacion["total_transferencia"]) ?></p>
                                <p><strong>Débito:</strong> <?= pesos($liquidacion["total_debito"]) ?></p>
                                <p><strong>Crédito:</strong> <?= pesos($liquidacion["total_credito"]) ?></p>
                                <p><strong>Cheque:</strong> <?= pesos($liquidacion["total_cheque"]) ?></p>
                            </div>
                            <hr>
                            <div class="total-general text-center fw-bold">
                                <strong>TOTAL LIQUIDADO:</strong>
                                <div class="text-success"><?= pesos($liquidacion["total_general"]) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="firmas">
                        <div class="firma">Firma del cajero</div>
                        <div class="firma">Firma del responsable</div>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4 no-imprimir">
                    <button type="button" class="btn btn-dark me-2" onclick="window.print()">
                        <i class="bi bi-file-earmark-pdf"></i> Guardar como PDF
                    </button>
                    <a href="liquidacion_diaria_cajero.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Volver a liquidación diaria
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>
