<?php

session_start();

require_once "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$id_repartidor = $_GET["id_repartidor"] ?? "";
$fecha = $_GET["fecha"] ?? "";
$total = $_GET["total"] ?? "0";


// ==========================================
// BUSCAR REPARTIDOR
// ==========================================

$sql = "
    SELECT usuario
    FROM usuario
    WHERE id = :id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ":id" => $id_repartidor
]);

$repartidor = $stmt->fetch(PDO::FETCH_ASSOC);


// ==========================================
// BUSCAR PAGOS LIQUIDADOS
// ==========================================

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
    WHERE p.id_repartidor = :id_repartidor
    AND DATE(p.fecha_hora) = :fecha
    AND p.liquidado = 1
    ORDER BY p.fecha_hora ASC
";

$stmtPagos = $conexion->prepare($sqlPagos);

$stmtPagos->execute([
    ":id_repartidor" => $id_repartidor,
    ":fecha" => $fecha
]);

$pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);


// ==========================================
// CALCULAR TOTALES
// ==========================================

$totalGeneral = 0;
$totalEfectivo = 0;
$totalTransferencia = 0;
$totalDebito = 0;
$totalOtro = 0;

foreach ($pagos as $pago) {

    $monto = (float)$pago["monto"];

    $totalGeneral += $monto;

    if ($pago["forma_pago"] === "Efectivo") {
        $totalEfectivo += $monto;
    }

    if ($pago["forma_pago"] === "Transferencia") {
        $totalTransferencia += $monto;
    }

    if ($pago["forma_pago"] === "Debito") {
        $totalDebito += $monto;
    }

    if ($pago["forma_pago"] === "Otro") {
        $totalOtro += $monto;
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Liquidación confirmada</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    margin: 0;
    padding: 30px;
}

.comprobante {
    max-width: 850px;
    margin: auto;
    background: white;
    padding: 35px;
    border: 1px solid #ccc;
}

h1 {
    text-align: center;
    margin-bottom: 5px;
}

h2 {
    text-align: center;
    font-size: 18px;
    margin-top: 0;
}

.datos {
    margin-top: 30px;
    margin-bottom: 25px;
}

.datos p {
    margin: 8px 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th,
td {
    border: 1px solid #999;
    padding: 10px;
    text-align: left;
}

th {
    background: #eeeeee;
}

.monto {
    text-align: right;
}

.totales {
    margin-top: 30px;
}

.total-general {
    font-size: 22px;
    font-weight: bold;
    margin-top: 15px;
    border-top: 2px solid black;
    padding-top: 12px;
}

.firmas {
    display: flex;
    justify-content: space-between;
    margin-top: 80px;
}

.firma {
    width: 40%;
    text-align: center;
    border-top: 1px solid black;
    padding-top: 8px;
}

.botones {
    text-align: center;
    margin-top: 35px;
}

button {
    padding: 12px 25px;
    font-size: 16px;
    cursor: pointer;
}

.volver {
    display: block;
    text-align: center;
    margin-top: 20px;
}


/* ==========================================
   AL GUARDAR COMO PDF
   ========================================== */

@media print {

    body {
        background: white;
        padding: 0;
    }

    .comprobante {
        border: none;
        max-width: 100%;
        padding: 10px;
    }

    .botones,
    .volver {
        display: none;
    }
}

</style>

</head>


<body>


<div class="comprobante">

    <h1>
        RES
    </h1>

    <h2>
        Sistema de Gestión de Pagos
    </h2>

    <hr>

    <h1>
        LIQUIDACIÓN DIARIA
    </h1>


    <div class="datos">

        <p>
            <strong>Repartidor:</strong>

            <?= htmlspecialchars(
                $repartidor["usuario"] ?? ""
            ) ?>
        </p>

        <p>
            <strong>Fecha:</strong>

            <?= htmlspecialchars($fecha) ?>
        </p>

    </div>


    <table>

        <thead>

            <tr>

                <th>
                    Pago
                </th>

                <th>
                    Factura
                </th>

                <th>
                    Hora
                </th>

                <th>
                    Forma de pago
                </th>

                <th>
                    Monto
                </th>

            </tr>

        </thead>


        <tbody>

        <?php foreach ($pagos as $pago): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($pago["id"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $pago["numero_serie"]
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        date(
                            "H:i",
                            strtotime($pago["fecha_hora"])
                        )
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars(
                        $pago["forma_pago"]
                    ) ?>
                </td>

                <td class="monto">

                    $<?= number_format(
                        (float)$pago["monto"],
                        2,
                        ",",
                        "."
                    ) ?>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>


    <div class="totales">

        <p>
            <strong>Efectivo:</strong>

            $<?= number_format(
                $totalEfectivo,
                2,
                ",",
                "."
            ) ?>
        </p>

        <p>
            <strong>Transferencia:</strong>

            $<?= number_format(
                $totalTransferencia,
                2,
                ",",
                "."
            ) ?>
        </p>

        <p>
            <strong>Débito:</strong>

            $<?= number_format(
                $totalDebito,
                2,
                ",",
                "."
            ) ?>
        </p>

        <p>
            <strong>Otro:</strong>

            $<?= number_format(
                $totalOtro,
                2,
                ",",
                "."
            ) ?>
        </p>


        <div class="total-general">

            TOTAL LIQUIDADO:

            $<?= number_format(
                $totalGeneral,
                2,
                ",",
                "."
            ) ?>

        </div>

    </div>


    <div class="firmas">

        <div class="firma">
            Firma del repartidor
        </div>

        <div class="firma">
            Firma del responsable
        </div>

    </div>


    <div class="botones">

        <button onclick="window.print()">
            Guardar como PDF
        </button>

    </div>


    <a
        class="volver"
        href="liquidacion_diaria.php"
    >
        Volver a Liquidación diaria
    </a>


</div>


</body>

</html>