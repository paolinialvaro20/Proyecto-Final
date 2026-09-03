```php
<?php

require_once "conexion.php";

$sql = "SELECT
            factura.id,
            factura.numero_serie,
            factura.monto,
            factura.fecha_registro,
            factura.fecha_cobro,
            factura.estado,
            usuario.usuario AS usuario
        FROM factura
        INNER JOIN usuario
            ON factura.id_USUARIO_registro = usuario.id
        ORDER BY factura.fecha_registro DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();

$facturas = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Facturas</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f5f5;
        }

        h1 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #eee;
        }

        .Pendiente {
            color: #b36b00;
        }

        .Cobrado {
            color: green;
        }

        .Anulada {
            color: red;
        }
    </style>

</head>

<body>

<?php require_once "menu.php"; ?>

<h1>Listado de facturas</h1>

<table>

    <thead>
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

        <?php if (count($facturas) > 0): ?>

            <?php foreach ($facturas as $factura): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($factura["id"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($factura["numero_serie"]) ?>
                    </td>

                    <td>
                        $<?= number_format($factura["monto"], 2, ',', '.') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($factura["fecha_registro"]) ?>
                    </td>

                    <td>
                        <?= $factura["fecha_cobro"]
                            ? htmlspecialchars($factura["fecha_cobro"])
                            : "-" ?>
                    </td>

                    <td class="<?= htmlspecialchars($factura["estado"]) ?>">
                        <?= htmlspecialchars($factura["estado"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($factura["usuario"]) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>
                <td colspan="7">
                    No hay facturas registradas.
                </td>
            </tr>

        <?php endif; ?>

    </tbody>

</table>

</body>

</html>