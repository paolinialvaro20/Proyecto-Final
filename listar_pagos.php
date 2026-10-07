<?php

session_start();

require_once "conexion.php";


// =====================================================
// VERIFICAR QUE HAYA UN USUARIO LOGUEADO
// =====================================================

if (!isset($_SESSION["id_usuario"])) {

    header("Location: login.php");
    exit;
}


require_once "menu.php";

$error = "";


// =====================================================
// OBTENER TODOS LOS PAGOS
// =====================================================

try {

    $sql = "
        SELECT
            p.id,
            p.id_factura,
            p.id_repartidor,
            p.monto,
            p.forma_pago,
            p.fecha_hora,
            p.liquidado,

            f.numero_serie,

            u.usuario AS nombre_repartidor

        FROM pago p

        INNER JOIN factura f
            ON p.id_factura = f.id

        INNER JOIN usuario u
            ON p.id_repartidor = u.id

        ORDER BY p.fecha_hora DESC
    ";


    $stmt = $conexion->prepare($sql);

    $stmt->execute();


    $pagos =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    $pagos = [];

    $error =
        "Error al obtener los pagos.";

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

    <title>Listado de pagos</title>

    <link
        rel="stylesheet"
        href="estilo.css"
    >

</head>


<body>


<h1>Listado de pagos</h1>


<!-- =====================================================
     MOSTRAR ERROR
     ===================================================== -->

<?php if ($error !== ""): ?>

    <p>
        <?= htmlspecialchars($error) ?>
    </p>

<?php endif; ?>



<!-- =====================================================
     TABLA DE PAGOS
     ===================================================== -->

<?php if (count($pagos) > 0): ?>


    <table border="1">


        <thead>

            <tr>

                <th>ID</th>

                <th>Factura</th>

                <th>Repartidor</th>

                <th>Monto</th>

                <th>Forma de pago</th>

                <th>Fecha y hora</th>

                <th>Liquidado</th>

            </tr>

        </thead>


        <tbody>


            <?php foreach ($pagos as $pago): ?>


                <tr>


                    <!-- ID DEL PAGO -->

                    <td>

                        <?= htmlspecialchars(
                            $pago["id"]
                        ) ?>

                    </td>


                    <!-- FACTURA -->

                    <td>

                        <?= htmlspecialchars(
                            $pago["numero_serie"]
                        ) ?>

                    </td>


                    <!-- REPARTIDOR -->

                    <td>

                        <?= htmlspecialchars(
                            $pago["nombre_repartidor"]
                        ) ?>

                    </td>


                    <!-- MONTO -->

                    <td>

                        $<?= number_format(
                            $pago["monto"],
                            2,
                            ",",
                            "."
                        ) ?>

                    </td>


                    <!-- FORMA DE PAGO -->

                    <td>

                        <?= htmlspecialchars(
                            $pago["forma_pago"]
                        ) ?>

                    </td>


                    <!-- FECHA Y HORA -->

                    <td>

                        <?= htmlspecialchars(
                            $pago["fecha_hora"]
                        ) ?>

                    </td>


                    <!-- LIQUIDADO -->

                    <td>

                        <?php if (
                            (int)$pago["liquidado"] === 1
                        ): ?>

                            Sí

                        <?php else: ?>

                            No

                        <?php endif; ?>

                    </td>


                </tr>


            <?php endforeach; ?>


        </tbody>


    </table>


<?php else: ?>


    <p>

        No hay pagos registrados.

    </p>


<?php endif; ?>


<br>


<a href="index.php">

    Volver al inicio

</a>


</body>

</html>