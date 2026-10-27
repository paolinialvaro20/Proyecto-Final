
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

$id_cajero = $_GET["id_cajero"] ?? "";
$fecha = $_GET["fecha"] ?? "";

$pagos = [];
$cajero = null;
$error = "";

$totales = [
    "Efectivo" => 0,
    "Transferencia" => 0,
    "Debito" => 0,
    "Credito" => 0
];

try {
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

    // Buscar cajero
    $sqlCajero = "
        SELECT u.id, u.usuario
        FROM usuario u
        INNER JOIN rol r ON r.id = u.id_ROL
        WHERE u.id = :id
          AND LOWER(r.nombre) IN ('cajero', 'cajera')
    ";

    $stmt = $conexion->prepare($sqlCajero);
    $stmt->execute([":id" => $id_cajero]);
    $cajero = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cajero) {
        throw new Exception("No se encontró el cajero.");
    }

    $inicio = $fecha . " 00:00:00";
    $fin = $fechaValida
        ->modify("+1 day")
        ->format("Y-m-d") . " 00:00:00";

    // Buscar pagos liquidados
    $sql = "
        SELECT
            p.id,
            f.numero_serie,
            p.fecha_hora,
            p.forma_pago,
            p.monto
        FROM pago p
        INNER JOIN factura f
            ON f.id = p.id_factura
        WHERE p.id_repartidor = :id_cajero
          AND p.fecha_hora >= :inicio
          AND p.fecha_hora < :fin
          AND p.liquidado = 1
        ORDER BY p.fecha_hora, p.id
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":id_cajero" => $id_cajero,
        ":inicio" => $inicio,
        ":fin" => $fin
    ]);

    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pagos as $pago) {
        $forma = $pago["forma_pago"];

        if (array_key_exists($forma, $totales)) {
            $totales[$forma] += (float)$pago["monto"];
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

$totalGeneral = array_sum($totales);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Liquidación confirmada - Cajero</title>

    <link rel="stylesheet"
          href="css/bootstrap-5.3.8/css/bootstrap.min.css">

    <style>
        body {
            background: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        .comprobante {
            max-width: 850px;
            margin: 35px auto;
            padding: 30px;
            background: white;
            border: 1px solid #ddd;
        }

        .logo {
            display: block;
            width: 110px;
            height: auto;
            margin: 0 auto 15px;
        }

        h1, h2 {
            text-align: center;
            color: #cc0000;
        }

        .total-general {
            font-size: 25px;
        }

        .firmas {
            display: flex;
            justify-content: space-around;
            gap: 30px;
            margin-top: 80px;
        }

        .firma {
            flex: 1;
            text-align: center;
            border-top: 1px solid black;
            padding-top: 10px;
        }

        @media print {
            body {
                background: white;
            }

            .comprobante {
                border: none;
                margin: 0;
                max-width: 100%;
            }

            .no-imprimir {
                display: none !important;
            }
        }
    </style>
</head>

<body>

<div class="comprobante">

    <img
        src="imagenes/logo don diego recortado.png"
        class="logo"
        alt="Logo Don Diego"
    >

    <h1>Panadería Don Diego</h1>
    <h2>Liquidación diaria de cajero</h2>

    <hr>

    <?php if ($error !== ""): ?>

        <div class="alert alert-danger">
            <?= esc($error) ?>
        </div>

    <?php else: ?>

        <p>
            <strong>Cajero:</strong>
            <?= esc($cajero["usuario"]) ?>
        </p>

        <p>
            <strong>Fecha:</strong>
            <?= esc($fecha) ?>
        </p>

        <p>
            <strong>Estado:</strong>
            <span class="text-success">
                Liquidación confirmada
            </span>
        </p>

        <hr>

        <h4>Detalle de pagos</h4>

        <?php if (count($pagos) > 0): ?>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Factura</th>
                            <th>Hora</th>
                            <th>Forma de pago</th>
                            <th>Monto</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?= esc($pago["id"]) ?></td>
                                <td><?= esc($pago["numero_serie"]) ?></td>
                                <td>
                                    <?= esc(date(
                                        "H:i",
                                        strtotime($pago["fecha_hora"])
                                    )) ?>
                                </td>
                                <td>
                                    <?= esc([
                                        "Debito" => "Débito",
                                        "Credito" => "Crédito"
                                    ][$pago["forma_pago"]]
                                    ?? $pago["forma_pago"]) ?>
                                </td>
                                <td>
                                    <?= pesos($pago["monto"]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="alert alert-secondary">
                No se encontraron pagos liquidados
                para esta fecha.
            </div>

        <?php endif; ?>

        <div class="card bg-light mt-4">
            <div class="card-body">

                <h4>Resumen por forma de pago</h4>

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

                <hr>

                <p>
                    Cantidad de pagos:
                    <?= count($pagos) ?>
                </p>

                <div class="total-general text-center">
                    <strong>TOTAL LIQUIDADO</strong>
                    <div class="text-success">
                        <?= pesos($totalGeneral) ?>
                    </div>
                </div>

            </div>
        </div>

        <div class="firmas">
            <div class="firma">
                Firma del cajero
            </div>

            <div class="firma">
                Firma del responsable
            </div>
        </div>

    <?php endif; ?>

    <div class="text-center mt-5 no-imprimir">

        <button
            type="button"
            class="btn btn-dark"
            onclick="window.print()"
        >
            Guardar como PDF
        </button>

        <a
            href="liquidacion_diaria_cajero.php"
            class="btn btn-secondary"
        >
            Volver a liquidación diaria
        </a>

    </div>

</div>

</body>
</html>
