<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "conexion.php";
require_once "menu.php";

$mensaje = "";
$error = "";

$id_factura_seleccionada = "";
$estado_seleccionado = "";


/* =========================================================
   CAMBIAR ESTADO DE LA FACTURA
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_factura = $_POST["id_factura"] ?? "";
    $estado = $_POST["estado"] ?? "";

    $id_factura_seleccionada = $id_factura;
    $estado_seleccionado = $estado;

    $estados_validos = [
        "Pendiente",
        "Cobrado",
        "Anulada"
    ];


    /* Verificar factura */

    if ($id_factura === "" || !is_numeric($id_factura)) {

        $error = "Debés seleccionar una factura.";

    } elseif (!in_array($estado, $estados_validos, true)) {

        $error = "El estado seleccionado no es válido.";

    } else {

        try {

            /* Buscar la factura */

            $sql = "SELECT id, estado
                    FROM factura
                    WHERE id = :id";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                ":id" => $id_factura
            ]);

            $factura = $stmt->fetch(PDO::FETCH_ASSOC);


            if (!$factura) {

                $error = "La factura seleccionada no existe.";

            } else {


                /* =====================================================
                   SI LA FACTURA PASA A COBRADO
                   SE GUARDA LA FECHA Y HORA ACTUAL DE MYSQL
                   ===================================================== */

                if ($estado === "Cobrado") {

                    $sql = "UPDATE factura
                            SET estado = :estado,
                                fecha_cobro = NOW()
                            WHERE id = :id";

                    $stmt = $conexion->prepare($sql);

                    $stmt->execute([
                        ":estado" => $estado,
                        ":id" => $id_factura
                    ]);

                }


                /* =====================================================
                   SI PASA A PENDIENTE O ANULADA
                   SE ELIMINA LA FECHA DE COBRO
                   ===================================================== */

                else {

                    $sql = "UPDATE factura
                            SET estado = :estado,
                                fecha_cobro = NULL
                            WHERE id = :id";

                    $stmt = $conexion->prepare($sql);

                    $stmt->execute([
                        ":estado" => $estado,
                        ":id" => $id_factura
                    ]);
                }


                /* Verificar que realmente se modificó */

                if ($stmt->rowCount() > 0) {

                    $mensaje = "Estado de la factura actualizado correctamente.";

                } else {

                    /*
                     * rowCount() puede devolver 0 si el valor ya era
                     * el mismo. De todas formas, la operación pudo
                     * ejecutarse correctamente.
                     */

                    $mensaje = "Estado de la factura actualizado correctamente.";
                }


                /* Limpiar formulario después de actualizar */

                $id_factura_seleccionada = "";
                $estado_seleccionado = "";
            }

        } catch (PDOException $e) {

            $error = "Error al actualizar la factura: " . $e->getMessage();
        }
    }
}


/* =========================================================
   OBTENER LAS FACTURAS
   INNER JOIN CON USUARIO
   ========================================================= */

try {

    $sql = "SELECT
                factura.id,
                factura.numero_serie,
                factura.monto,
                factura.fecha_registro,
                factura.fecha_cobro,
                factura.estado,
                usuario.usuario AS nombre_usuario

            FROM factura

            INNER JOIN usuario
                ON factura.id_usuario_registro = usuario.id

            ORDER BY factura.id DESC";


    $stmt = $conexion->query($sql);

    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    $facturas = [];

    $error = "Error al obtener las facturas: " . $e->getMessage();
}

?>


<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cambiar estado de factura</title>

</head>


<body>


<h1>Cambiar estado de factura</h1>
 <link rel="stylesheet" href="estilo.css">


<?php if ($mensaje !== ""): ?>

    <p>
        <?= htmlspecialchars($mensaje) ?>
    </p>

<?php endif; ?>


<?php if ($error !== ""): ?>

    <p>
        <?= htmlspecialchars($error) ?>
    </p>

<?php endif; ?>


<form method="POST" action="cambiar_estado_factura.php">


    <p>

        <label for="id_factura">
            Factura:
        </label>


        <select
            name="id_factura"
            id="id_factura"
            required
        >

            <option value="">
                -- Seleccionar factura --
            </option>


            <?php foreach ($facturas as $factura): ?>

                <option
                    value="<?= htmlspecialchars($factura["id"]) ?>"
                    <?= ($id_factura_seleccionada == $factura["id"]) ? "selected" : "" ?>
                >

                    <?= htmlspecialchars($factura["numero_serie"]) ?>

                    -

                    $<?= htmlspecialchars($factura["monto"]) ?>

                    -

                    <?= htmlspecialchars($factura["estado"]) ?>

                    -

                    Registrada por:

                    <?= htmlspecialchars($factura["nombre_usuario"]) ?>

                </option>

            <?php endforeach; ?>


        </select>

    </p>


    <p>

        <label for="estado">
            Nuevo estado:
        </label>


        <select
            name="estado"
            id="estado"
            required
        >

            <option value="">
                -- Seleccionar estado --
            </option>


            <option
                value="Pendiente"
                <?= ($estado_seleccionado === "Pendiente") ? "selected" : "" ?>
            >
                Pendiente
            </option>


            <option
                value="Cobrado"
                <?= ($estado_seleccionado === "Cobrado") ? "selected" : "" ?>
            >
                Cobrado
            </option>


            <option
                value="Anulada"
                <?= ($estado_seleccionado === "Anulada") ? "selected" : "" ?>
            >
                Anulada
            </option>


        </select>

    </p>


    <p>

        <button type="submit">
            Cambiar estado
        </button>

    </p>


</form>



<h2>Facturas registradas</h2>


<table border="1">


    <thead>

        <tr>

            <th>ID</th>

            <th>Número de serie</th>

            <th>Monto</th>

            <th>Fecha de registro</th>

            <th>Fecha de cobro</th>

            <th>Estado</th>

            <th>Registrada por</th>

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
                        $<?= htmlspecialchars($factura["monto"]) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars($factura["fecha_registro"]) ?>
                    </td>


                    <td>


                        <?php if ($factura["fecha_cobro"] !== null): ?>

                            <?= htmlspecialchars($factura["fecha_cobro"]) ?>

                        <?php else: ?>

                            Sin cobrar

                        <?php endif; ?>


                    </td>


                    <td>
                        <?= htmlspecialchars($factura["estado"]) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars($factura["nombre_usuario"]) ?>
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