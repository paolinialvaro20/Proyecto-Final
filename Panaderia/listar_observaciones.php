<?php
session_start();

require_once "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "menu.php";

$id_factura = $_GET["id_factura"] ?? "";


// Obtener todas las facturas
$sqlFacturas = "SELECT id, numero_serie
                FROM factura
                ORDER BY id DESC";

$stmtFacturas = $conexion->prepare($sqlFacturas);
$stmtFacturas->execute();

$facturas = $stmtFacturas->fetchAll(PDO::FETCH_ASSOC);


// Variables para las observaciones
$observaciones = [];
$facturaSeleccionada = null;


// Si se seleccionó una factura
if ($id_factura != "") {

    // Obtener los datos de la factura seleccionada
    $sqlFactura = "SELECT id, numero_serie
                   FROM factura
                   WHERE id = ?";

    $stmtFactura = $conexion->prepare($sqlFactura);
    $stmtFactura->execute([$id_factura]);

    $facturaSeleccionada = $stmtFactura->fetch(PDO::FETCH_ASSOC);


    // Obtener las observaciones de esa factura
    if ($facturaSeleccionada) {

        $sqlObservaciones = "SELECT
                                o.detalle,
                                o.fecha_hora,
                                u.usuario
                             FROM observacion o
                             INNER JOIN usuario u
                                ON o.id_USUARIO = u.id
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

    <title>Observaciones</title>

</head>

<body>
    

    <h1>Observaciones de factura</h1>
    <link rel="stylesheet" href="estilo.css">


    <form method="get">

        <p>

            <label for="id_factura">
                Seleccionar factura:
            </label>

            <select
                id="id_factura"
                name="id_factura"
                onchange="this.form.submit()"
                required
            >

                <option value="">
                    -- Seleccione una factura --
                </option>

                <?php foreach ($facturas as $factura): ?>

                    <option
                        value="<?= $factura["id"] ?>"
                        <?= ($id_factura == $factura["id"]) ? "selected" : "" ?>
                    >

                        <?= htmlspecialchars($factura["numero_serie"]) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </p>

    </form>


    <?php if ($facturaSeleccionada): ?>

        <h2>
            Factura:
            <?= htmlspecialchars($facturaSeleccionada["numero_serie"]) ?>
        </h2>


        <?php if (count($observaciones) > 0): ?>

            <table border="1">

                <thead>

                    <tr>

                        <th>Detalle</th>
                        <th>Fecha y hora</th>
                        <th>Usuario</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($observaciones as $observacion): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($observacion["detalle"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($observacion["fecha_hora"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($observacion["usuario"]) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <p>
                Esta factura no tiene observaciones.
            </p>

        <?php endif; ?>

    <?php endif; ?>

</body>

</html>