<?php

session_start();

require_once "conexion.php";
require_once "registrar_auditoria.php";


// =====================================================
// VERIFICAR SESIÓN
// =====================================================

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "menu.php";

$mensaje = "";
$error = "";

$id_factura_seleccionada = "";
$id_usuario_cobro_seleccionado = "";
$forma_pago_seleccionada = "";


// =====================================================
// PROCESAR PAGO
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_factura =
        $_POST["id_factura"] ?? "";

    $id_usuario_cobro =
        $_POST["id_usuario_cobro"] ?? "";

    $forma_pago =
        $_POST["forma_pago"] ?? "";


    // Mantener valores si hay error
    $id_factura_seleccionada =
        $id_factura;

    $id_usuario_cobro_seleccionado =
        $id_usuario_cobro;

    $forma_pago_seleccionada =
        $forma_pago;


    // =================================================
    // FORMAS DE PAGO PERMITIDAS
    // =================================================

    $formas_pago_validas = [
        "Efectivo",
        "Transferencia",
        "Debito",
        "Otro"
    ];


    // =================================================
    // VALIDACIONES
    // =================================================

    if (
        $id_factura === "" ||
        !is_numeric($id_factura)
    ) {

        $error =
            "Debés seleccionar una factura.";

    } elseif (
        $id_usuario_cobro === "" ||
        !is_numeric($id_usuario_cobro)
    ) {

        $error =
            "Debés seleccionar quién realizó el cobro.";

    } elseif (
        !in_array(
            $forma_pago,
            $formas_pago_validas,
            true
        )
    ) {

        $error =
            "Debés seleccionar una forma de pago válida.";

    } else {

        try {

            // =================================================
            // INICIAR TRANSACCIÓN
            // =================================================

            $conexion->beginTransaction();


            // =================================================
            // BUSCAR FACTURA
            // =================================================

            $sqlFactura = "
                SELECT
                    f.id,
                    f.numero_serie,
                    f.monto,
                    f.estado,
                    f.id_usuario_asignado,
                    u.usuario AS usuario_asignado

                FROM factura f

                LEFT JOIN usuario u
                    ON f.id_usuario_asignado = u.id

                WHERE f.id = :id
            ";


            $stmtFactura =
                $conexion->prepare($sqlFactura);


            $stmtFactura->execute([
                ":id" => $id_factura
            ]);


            $factura =
                $stmtFactura->fetch(PDO::FETCH_ASSOC);


            if (!$factura) {

                throw new Exception(
                    "La factura seleccionada no existe."
                );
            }


            // =================================================
            // DEBE ESTAR PENDIENTE
            // =================================================

            if ($factura["estado"] !== "Pendiente") {

                throw new Exception(
                    "Solamente se pueden cobrar facturas pendientes."
                );
            }


            // =================================================
            // VERIFICAR QUE EL USUARIO QUE COBRÓ EXISTA
            // Y ESTÉ ACTIVO
            // =================================================

            $sqlUsuario = "
                SELECT
                    id,
                    usuario,
                    activo

                FROM usuario

                WHERE id = :id
            ";


            $stmtUsuario =
                $conexion->prepare($sqlUsuario);


            $stmtUsuario->execute([
                ":id" => $id_usuario_cobro
            ]);


            $usuarioCobro =
                $stmtUsuario->fetch(PDO::FETCH_ASSOC);


            if (!$usuarioCobro) {

                throw new Exception(
                    "El usuario que realizó el cobro no existe."
                );
            }


            if ((int)$usuarioCobro["activo"] !== 1) {

                throw new Exception(
                    "El usuario seleccionado está inactivo."
                );
            }


            // =================================================
            // VERIFICAR QUE NO EXISTA YA UN PAGO
            // =================================================

            $sqlExistePago = "
                SELECT id
                FROM pago
                WHERE id_factura = :id_factura
                LIMIT 1
            ";


            $stmtExistePago =
                $conexion->prepare($sqlExistePago);


            $stmtExistePago->execute([
                ":id_factura" => $id_factura
            ]);


            if (
                $stmtExistePago->fetch(PDO::FETCH_ASSOC)
            ) {

                throw new Exception(
                    "Esta factura ya tiene un pago registrado."
                );
            }


            // =================================================
            // REGISTRAR PAGO
            // =================================================
            //
            // IMPORTANTE:
            //
            // Por ahora usamos id_repartidor porque así se
            // llama actualmente tu columna.
            //
            // Pero guardamos allí al usuario que REALMENTE
            // recibió el pago.
            // =================================================

            $sqlPago = "
                INSERT INTO pago
                (
                    id_factura,
                    id_repartidor,
                    monto,
                    forma_pago,
                    fecha_hora,
                    liquidado
                )

                VALUES
                (
                    :id_factura,
                    :id_usuario_cobro,
                    :monto,
                    :forma_pago,
                    NOW(),
                    0
                )
            ";


            $stmtPago =
                $conexion->prepare($sqlPago);


            $stmtPago->execute([

                ":id_factura" =>
                    $id_factura,

                ":id_usuario_cobro" =>
                    $id_usuario_cobro,

                ":monto" =>
                    $factura["monto"],

                ":forma_pago" =>
                    $forma_pago

            ]);


            $id_pago =
                $conexion->lastInsertId();


            // =================================================
            // CAMBIAR FACTURA A COBRADO
            // =================================================

            $sqlActualizarFactura = "
                UPDATE factura

                SET
                    estado = 'Cobrado',
                    fecha_cobro = NOW()

                WHERE id = :id
            ";


            $stmtActualizarFactura =
                $conexion->prepare(
                    $sqlActualizarFactura
                );


            $stmtActualizarFactura->execute([
                ":id" => $id_factura
            ]);


            // =================================================
            // DATOS PARA AUDITORÍA
            // =================================================

            $asignadoA =
                $factura["usuario_asignado"]
                ?? "Sin asignar";


            $valorNuevo =
                "Factura: " .
                $factura["numero_serie"] .

                " | Asignada a: " .
                $asignadoA .

                " | Cobrado por: " .
                $usuarioCobro["usuario"] .

                " | Monto: $" .
                $factura["monto"] .

                " | Forma de pago: " .
                $forma_pago .

                " | Liquidado: No";


            // =================================================
            // AUDITORÍA
            // =================================================

            registrarAuditoria(

                $conexion,

                $_SESSION["id_usuario"],

                "REGISTRAR PAGO",

                "Pago",

                $id_pago,

                "Se registró el pago de la factura " .
                $factura["numero_serie"] .
                " cobrado por " .
                $usuarioCobro["usuario"],

                "Factura pendiente de cobro",

                $valorNuevo
            );


            // =================================================
            // CONFIRMAR TRANSACCIÓN
            // =================================================

            $conexion->commit();


            $mensaje =
                "Pago registrado correctamente. " .
                "Cobrado por: " .
                $usuarioCobro["usuario"] .
                ".";


            // Limpiar formulario
            $id_factura_seleccionada = "";

            $id_usuario_cobro_seleccionado = "";

            $forma_pago_seleccionada = "";


        } catch (Exception $e) {

            if ($conexion->inTransaction()) {

                $conexion->rollBack();
            }

            $error =
                $e->getMessage();
        }
    }
}


// =====================================================
// OBTENER FACTURAS PENDIENTES
// =====================================================

try {

    $sqlFacturas = "
        SELECT
            f.id,
            f.numero_serie,
            f.monto,
            f.id_usuario_asignado,
            u.usuario AS usuario_asignado

        FROM factura f

        LEFT JOIN usuario u
            ON f.id_usuario_asignado = u.id

        WHERE f.estado = 'Pendiente'

        ORDER BY f.id DESC
    ";


    $stmtFacturas =
        $conexion->prepare($sqlFacturas);


    $stmtFacturas->execute();


    $facturas =
        $stmtFacturas->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    $facturas = [];

    $error =
        "No se pudieron cargar las facturas pendientes.";
}


// =====================================================
// OBTENER USUARIOS ACTIVOS QUE PUEDEN RECIBIR PAGOS
// =====================================================

try {

    $sqlUsuarios = "
        SELECT
            id,
            usuario

        FROM usuario

        WHERE activo = 1

        ORDER BY usuario
    ";


    $stmtUsuarios =
        $conexion->prepare($sqlUsuarios);


    $stmtUsuarios->execute();


    $usuariosCobro =
        $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    $usuariosCobro = [];

    $error =
        "No se pudieron cargar los usuarios.";
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

    <title>Registrar pago</title>

    <link
        rel="stylesheet"
        href="estilo.css"
    >

</head>


<body>


<h1>Registrar pago</h1>


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



<?php if (count($facturas) > 0): ?>


<form method="POST">


    <!-- =================================================
         FACTURA
         ================================================= -->

    <p>

        <label for="id_factura">

            Factura pendiente:

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

                    value="<?=
                        htmlspecialchars(
                            $factura["id"]
                        )
                    ?>"

                    <?=
                        (
                            $id_factura_seleccionada
                            ==
                            $factura["id"]
                        )
                        ? "selected"
                        : ""
                    ?>

                >


                    <?=
                        htmlspecialchars(
                            $factura["numero_serie"]
                        )
                    ?>

                    -

                    $<?=
                        number_format(
                            $factura["monto"],
                            2,
                            ",",
                            "."
                        )
                    ?>

                    -

                    Asignada a:

                    <?=
                        htmlspecialchars(
                            $factura["usuario_asignado"]
                            ?? "Sin asignar"
                        )
                    ?>


                </option>


            <?php endforeach; ?>


        </select>

    </p>



    <!-- =================================================
         QUIÉN REALMENTE COBRÓ
         ================================================= -->

    <p>

        <label for="id_usuario_cobro">

            Cobrado por:

        </label>


        <select
            name="id_usuario_cobro"
            id="id_usuario_cobro"
            required
        >


            <option value="">

                -- Seleccionar usuario --

            </option>


            <?php foreach ($usuariosCobro as $usuario): ?>


                <option

                    value="<?=
                        htmlspecialchars(
                            $usuario["id"]
                        )
                    ?>"

                    <?=
                        (
                            $id_usuario_cobro_seleccionado
                            ==
                            $usuario["id"]
                        )
                        ? "selected"
                        : ""
                    ?>

                >


                    <?=
                        htmlspecialchars(
                            $usuario["usuario"]
                        )
                    ?>


                </option>


            <?php endforeach; ?>


        </select>

    </p>



    <!-- =================================================
         FORMA DE PAGO
         ================================================= -->

    <p>

        <label for="forma_pago">

            Forma de pago:

        </label>


        <select
            name="forma_pago"
            id="forma_pago"
            required
        >


            <option value="">

                -- Seleccionar forma de pago --

            </option>


            <option
                value="Efectivo"
                <?= (
                    $forma_pago_seleccionada === "Efectivo"
                ) ? "selected" : "" ?>
            >
                Efectivo
            </option>


            <option
                value="Transferencia"
                <?= (
                    $forma_pago_seleccionada === "Transferencia"
                ) ? "selected" : "" ?>
            >
                Transferencia
            </option>


            <option
                value="Debito"
                <?= (
                    $forma_pago_seleccionada === "Debito"
                ) ? "selected" : "" ?>
            >
                Débito
            </option>


            <option
                value="Otro"
                <?= (
                    $forma_pago_seleccionada === "Otro"
                ) ? "selected" : "" ?>
            >
                Otro
            </option>


        </select>

    </p>



    <p>

        <button type="submit">

            Registrar pago

        </button>


        <a href="index.php">

            Cancelar

        </a>

    </p>


</form>


<?php else: ?>


    <p>

        No hay facturas pendientes para cobrar.

    </p>


<?php endif; ?>


</body>

</html>