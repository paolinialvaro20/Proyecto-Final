<?php

session_start();

require_once "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

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

$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Facturas</title>

    <link rel="stylesheet" href="estilo.css">

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