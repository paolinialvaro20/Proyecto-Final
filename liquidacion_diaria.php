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


// =====================================================
// VARIABLES
// =====================================================

$error = "";

$pagos = [];

$id_repartidor_seleccionado =
    $_GET["id_repartidor"] ?? "";

$fecha_seleccionada =
    $_GET["fecha"] ?? date("Y-m-d");


// Totales
$total_general = 0;
$total_efectivo = 0;
$total_transferencia = 0;
$total_debito = 0;
$total_otro = 0;


// =====================================================
// OBTENER SOLAMENTE USUARIOS CON ROL REPARTIDOR
// =====================================================

try {

    $sqlRepartidores = "
        SELECT
            u.id,
            u.usuario

        FROM usuario u

        INNER JOIN rol r
            ON u.id_ROL = r.id

        WHERE u.activo = 1
        AND r.nombre = 'Repartidor'

        ORDER BY u.usuario
    ";

    $stmtRepartidores =
        $conexion->prepare($sqlRepartidores);

    $stmtRepartidores->execute();

    $repartidores =
        $stmtRepartidores->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $repartidores = [];

    $error =
        "No se pudieron cargar los repartidores.";
}



// =====================================================
// BUSCAR PAGOS
// =====================================================

if (
    $id_repartidor_seleccionado !== ""
    &&
    $fecha_seleccionada !== ""
) {

    try {


        // =================================================
        // OBTENER PAGOS NO LIQUIDADOS
        // =================================================

        $sqlPagos = "
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

            WHERE p.id_repartidor = :id_repartidor

            AND DATE(p.fecha_hora) = :fecha

            AND p.liquidado = 0

            ORDER BY p.fecha_hora ASC
        ";


        $stmtPagos =
            $conexion->prepare($sqlPagos);


        $stmtPagos->execute([

            ":id_repartidor" =>
                $id_repartidor_seleccionado,

            ":fecha" =>
                $fecha_seleccionada

        ]);


        $pagos =
            $stmtPagos->fetchAll(PDO::FETCH_ASSOC);


        // =================================================
        // CALCULAR TOTALES
        // =================================================

        foreach ($pagos as $pago) {


            $monto =
                (float)$pago["monto"];


            // Total general
            $total_general += $monto;


            // ---------------------------------------------
            // EFECTIVO
            // ---------------------------------------------

            if (
                $pago["forma_pago"] === "Efectivo"
            ) {

                $total_efectivo += $monto;

            }


            // ---------------------------------------------
            // TRANSFERENCIA
            // ---------------------------------------------

            elseif (
                $pago["forma_pago"] === "Transferencia"
            ) {

                $total_transferencia += $monto;

            }


            // ---------------------------------------------
            // DÉBITO
            // ---------------------------------------------

            elseif (
                $pago["forma_pago"] === "Debito"
            ) {

                $total_debito += $monto;

            }


            // ---------------------------------------------
            // OTRO
            // ---------------------------------------------

            else {

                $total_otro += $monto;

            }

        }


    } catch (PDOException $e) {


        $pagos = [];


        $error =
            "Error al obtener los pagos del repartidor.";

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

    <title>Liquidación diaria</title>

    <link
        rel="stylesheet"
        href="estilo.css"
    >

</head>


<body>


<h1>Liquidación diaria por repartidor</h1>



<!-- =====================================================
     MOSTRAR ERROR
     ===================================================== -->

<?php if ($error !== ""): ?>

    <p>

        <?= htmlspecialchars($error) ?>

    </p>

<?php endif; ?>



<!-- =====================================================
     FORMULARIO DE BÚSQUEDA
     ===================================================== -->

<form
    method="GET"
    action="liquidacion_diaria.php"
>


    <!-- =================================================
         REPARTIDOR
         ================================================= -->

    <p>

        <label for="id_repartidor">

            Repartidor:

        </label>


        <select
            name="id_repartidor"
            id="id_repartidor"
            required
        >


            <option value="">

                -- Seleccionar repartidor --

            </option>


            <?php foreach ($repartidores as $repartidor): ?>


                <option

                    value="<?=
                        htmlspecialchars(
                            $repartidor["id"]
                        )
                    ?>"

                    <?=
                        (
                            $id_repartidor_seleccionado
                            ==
                            $repartidor["id"]
                        )
                        ? "selected"
                        : ""
                    ?>

                >


                    <?=
                        htmlspecialchars(
                            $repartidor["usuario"]
                        )
                    ?>


                </option>


            <?php endforeach; ?>


        </select>

    </p>



    <!-- =================================================
         FECHA
         ================================================= -->

    <p>


        <label for="fecha">

            Fecha:

        </label>


        <input

            type="date"

            name="fecha"

            id="fecha"

            value="<?=
                htmlspecialchars(
                    $fecha_seleccionada
                )
            ?>"

            required

        >


    </p>



    <!-- =================================================
         BOTÓN BUSCAR
         ================================================= -->

    <p>


        <button type="submit">

            Buscar pagos

        </button>


    </p>


</form>



<!-- =====================================================
     RESULTADOS
     ===================================================== -->


<?php if (
    $id_repartidor_seleccionado !== ""
    &&
    $fecha_seleccionada !== ""
): ?>


    <hr>


    <h2>

        Pagos pendientes de liquidación

    </h2>


    <p>

        <strong>Fecha:</strong>

        <?= htmlspecialchars(
            $fecha_seleccionada
        ) ?>

    </p>



    <!-- =================================================
         SI HAY PAGOS
         ================================================= -->


    <?php if (count($pagos) > 0): ?>


        <p>

            <strong>Repartidor:</strong>

            <?= htmlspecialchars(
                $pagos[0]["nombre_repartidor"]
            ) ?>

        </p>


        <table border="1">


            <thead>


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


                        <!-- ID PAGO -->

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



                        <!-- FECHA Y HORA -->

                        <td>

                            <?= htmlspecialchars(
                                $pago["fecha_hora"]
                            ) ?>

                        </td>



                        <!-- FORMA DE PAGO -->

                        <td>

                            <?= htmlspecialchars(
                                $pago["forma_pago"]
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


                    </tr>


                <?php endforeach; ?>


            </tbody>


        </table>



        <!-- =================================================
             RESUMEN DE LA LIQUIDACIÓN
             ================================================= -->


        <h2>

            Resumen

        </h2>


        <p>

            Cantidad de pagos:

            <strong>

                <?= count($pagos) ?>

            </strong>

        </p>



        <p>

            Total efectivo:

            <strong>

                $<?= number_format(
                    $total_efectivo,
                    2,
                    ",",
                    "."
                ) ?>

            </strong>

        </p>



        <p>

            Total transferencia:

            <strong>

                $<?= number_format(
                    $total_transferencia,
                    2,
                    ",",
                    "."
                ) ?>

            </strong>

        </p>



        <p>

            Total débito:

            <strong>

                $<?= number_format(
                    $total_debito,
                    2,
                    ",",
                    "."
                ) ?>

            </strong>

        </p>



        <p>

            Total otros:

            <strong>

                $<?= number_format(
                    $total_otro,
                    2,
                    ",",
                    "."
                ) ?>

            </strong>

        </p>



        <hr>



        <h2>

        TOTAL A LIQUIDAR: 
 
$<?= number_format( 
    $total_general, 
    2, 
    ",", 
    "." 
) ?> 
 
</h2>


<!-- BOTÓN PARA CONFIRMAR LA LIQUIDACIÓN -->

<form
    method="POST"
    action="confirmar_liquidacion.php"
    onsubmit="return confirm('¿Está seguro de confirmar esta liquidación?');"
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

    <br>

    <button type="submit">
        Confirmar liquidación
    </button>

</form>


    <?php else: ?>


        <!-- =================================================
             NO HAY PAGOS
             ================================================= -->


        <p>

            No hay pagos pendientes de liquidación
            para este repartidor en la fecha seleccionada.

        </p>


    <?php endif; ?>


<?php endif; ?>



<br>


<a href="index.php">

    Volver al inicio

</a>


</body>

</html>