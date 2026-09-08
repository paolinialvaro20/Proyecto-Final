<?php
session_start();

require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "menu.php";

$mensaje = "";

// ID de la factura que se quiere editar
// Se utiliza internamente y no es editable.
$id_factura_editar = $_GET["id"] ?? $_POST["id_factura"] ?? "";


// =====================================================
// OBTENER USUARIOS ACTIVOS
// =====================================================

$sqlUsuarios = "SELECT id, usuario
                FROM usuario
                WHERE activo = 1
                ORDER BY usuario";

$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->execute();

$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// PROCESAR FORMULARIO
// =====================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_factura = $_POST["id_factura"] ?? "";
    $numero_serie = trim($_POST["numero_serie"] ?? "");
    $monto = $_POST["monto"] ?? "";
    $fecha_cobro = $_POST["fecha_cobro"] ?? "";
    $estado = $_POST["estado"] ?? "";
    $id_usuario_asignado = $_POST["id_usuario_asignado"] ?? "";


    // -------------------------------------------------
    // Validar campos obligatorios
    // -------------------------------------------------

    if (
        $id_factura == "" ||
        $numero_serie == "" ||
        $monto == "" ||
        $estado == ""
    ) {

        $mensaje = "Debe completar todos los campos obligatorios.";

    } else {

        // -------------------------------------------------
        // Validar monto
        // -------------------------------------------------

        if (!is_numeric($monto) || $monto < 0) {

            $mensaje = "El monto ingresado no es válido.";

        } else {

            // -------------------------------------------------
            // Validar estado
            // -------------------------------------------------

            $estadosPermitidos = [
                "Cobrado",
                "Pendiente",
                "Anulada"
            ];

            if (!in_array($estado, $estadosPermitidos)) {

                $mensaje = "El estado seleccionado no es válido.";

            } else {

                // -------------------------------------------------
                // Validar usuario asignado
                // -------------------------------------------------

                if ($id_usuario_asignado != "") {

                    $sqlVerificarUsuario = "SELECT id
                                            FROM usuario
                                            WHERE id = ?
                                            AND activo = 1";

                    $stmtVerificarUsuario = $conexion->prepare(
                        $sqlVerificarUsuario
                    );

                    $stmtVerificarUsuario->execute([
                        $id_usuario_asignado
                    ]);

                    $usuarioExiste = $stmtVerificarUsuario->fetch(
                        PDO::FETCH_ASSOC
                    );


                    if (!$usuarioExiste) {

                        $mensaje = "El usuario asignado no existe o está inactivo.";
                    }
                }


                // -------------------------------------------------
                // Continuar si no hubo errores
                // -------------------------------------------------

                if ($mensaje == "") {

                    // -------------------------------------------------
                    // Fecha de cobro
                    // -------------------------------------------------

                    if ($estado == "Cobrado") {

                        /*
                         * Si la factura está cobrada:
                         *
                         * - Si se ingresó una fecha, se utiliza.
                         * - Si está vacía, se utiliza la fecha y hora actual.
                         */

                        if ($fecha_cobro == "") {

                            $fecha_cobro = date("Y-m-d H:i:s");

                        } else {

                            // Convertir formato datetime-local
                            $fecha_cobro = str_replace(
                                "T",
                                " ",
                                $fecha_cobro
                            );

                            // Agregar segundos si no existen
                            if (strlen($fecha_cobro) == 16) {
                                $fecha_cobro .= ":00";
                            }
                        }

                    } else {

                        /*
                         * Si está Pendiente o Anulada,
                         * no debe existir fecha de cobro.
                         */

                        $fecha_cobro = null;
                    }


                    // -------------------------------------------------
                    // Actualizar factura
                    // -------------------------------------------------

                    $sql = "UPDATE factura
                            SET numero_serie = ?,
                                monto = ?,
                                fecha_cobro = ?,
                                estado = ?,
                                id_usuario_asignado = ?
                            WHERE id = ?";

                    $stmt = $conexion->prepare($sql);

                    $resultado = $stmt->execute([
                        $numero_serie,
                        $monto,
                        $fecha_cobro,
                        $estado,
                        ($id_usuario_asignado != "")
                            ? $id_usuario_asignado
                            : null,
                        $id_factura
                    ]);


                    if ($resultado) {

                        $mensaje = "Factura actualizada correctamente.";

                        $id_factura_editar = $id_factura;

                    } else {

                        $mensaje = "Error al actualizar la factura.";
                    }
                }
            }
        }
    }
}


// =====================================================
// OBTENER DATOS DE LA FACTURA
// =====================================================

$facturaEditar = null;

if ($id_factura_editar != "") {

    $sqlFactura = "SELECT
                        id,
                        numero_serie,
                        monto,
                        fecha_registro,
                        fecha_cobro,
                        estado,
                        id_usuario_registro,
                        id_usuario_asignado
                   FROM factura
                   WHERE id = ?";

    $stmtFactura = $conexion->prepare($sqlFactura);

    $stmtFactura->execute([
        $id_factura_editar
    ]);

    $facturaEditar = $stmtFactura->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar factura</title>

</head>

<body>

    <h1>Editar factura</h1>


    <?php if ($mensaje != ""): ?>

        <p>
            <?= htmlspecialchars($mensaje) ?>
        </p>

    <?php endif; ?>


    <!-- =================================================
         SELECCIONAR FACTURA
         ================================================= -->

    <form method="get">

        <p>

            <label for="id">
                Factura a editar:
            </label>

            <select
                id="id"
                name="id"
                onchange="this.form.submit()"
                required
            >

                <option value="">
                    -- Seleccione una factura --
                </option>


                <?php

                $sqlListaFacturas = "SELECT
                                        id,
                                        numero_serie
                                      FROM factura
                                      ORDER BY id DESC";

                $stmtListaFacturas = $conexion->prepare(
                    $sqlListaFacturas
                );

                $stmtListaFacturas->execute();

                $facturas = $stmtListaFacturas->fetchAll(
                    PDO::FETCH_ASSOC
                );

                ?>


                <?php foreach ($facturas as $factura): ?>

                    <option
                        value="<?= $factura["id"] ?>"
                        <?= ($id_factura_editar == $factura["id"])
                            ? "selected"
                            : "" ?>
                    >

                        <?= htmlspecialchars(
                            $factura["numero_serie"]
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </p>

    </form>


    <?php if ($facturaEditar): ?>

        <hr>


        <!-- =================================================
             FORMULARIO DE EDICIÓN
             ================================================= -->

        <form method="post">

            <!--
                ID utilizado internamente para identificar
                la factura.
                No es editable.
            -->

            <input
                type="hidden"
                name="id_factura"
                value="<?= $facturaEditar["id"] ?>"
            >


            <!-- =================================================
                 NÚMERO DE SERIE
                 ================================================= -->

            <p>

                <label for="numero_serie">
                    Número de serie:
                </label>

                <input
                    type="text"
                    id="numero_serie"
                    name="numero_serie"
                    value="<?= htmlspecialchars(
                        $facturaEditar["numero_serie"]
                    ) ?>"
                    maxlength="20"
                    required
                >

            </p>


            <!-- =================================================
                 MONTO
                 ================================================= -->

            <p>

                <label for="monto">
                    Monto:
                </label>

                <input
                    type="number"
                    id="monto"
                    name="monto"
                    value="<?= htmlspecialchars(
                        $facturaEditar["monto"]
                    ) ?>"
                    step="0.01"
                    min="0"
                    required
                >

            </p>


            <!-- =================================================
                 FECHA DE REGISTRO
                 ================================================= -->

            <p>

                <label for="fecha_registro">
                    Fecha y hora de registro:
                </label>

                <input
                    type="text"
                    id="fecha_registro"
                    value="<?= htmlspecialchars(
                        $facturaEditar["fecha_registro"]
                    ) ?>"
                    readonly
                >

            </p>


            <!-- =================================================
                 FECHA DE COBRO
                 ================================================= -->

            <p>

                <label for="fecha_cobro">
                    Fecha y hora de cobro:
                </label>

                <input
                    type="datetime-local"
                    id="fecha_cobro"
                    name="fecha_cobro"
                    value="<?php

                        if (
                            !empty(
                                $facturaEditar["fecha_cobro"]
                            )
                        ) {

                            echo date(
                                "Y-m-d\TH:i",
                                strtotime(
                                    $facturaEditar["fecha_cobro"]
                                )
                            );
                        }

                    ?>"
                >

            </p>


            <!-- =================================================
                 ESTADO
                 ================================================= -->

            <p>

                <label for="estado">
                    Estado:
                </label>

                <select
                    id="estado"
                    name="estado"
                    required
                >

                    <option
                        value="Pendiente"
                        <?= ($facturaEditar["estado"] == "Pendiente")
                            ? "selected"
                            : "" ?>
                    >
                        Pendiente
                    </option>

                    <option
                        value="Cobrado"
                        <?= ($facturaEditar["estado"] == "Cobrado")
                            ? "selected"
                            : "" ?>
                    >
                        Cobrado
                    </option>

                    <option
                        value="Anulada"
                        <?= ($facturaEditar["estado"] == "Anulada")
                            ? "selected"
                            : "" ?>
                    >
                        Anulada
                    </option>

                </select>

            </p>


            <!-- =================================================
                 USUARIO QUE REGISTRÓ
                 ================================================= -->

            <p>

                <label for="id_usuario_registro">
                    Usuario que registró:
                </label>

                <input
                    type="text"
                    id="id_usuario_registro"
                    value="<?php

                        $sqlUsuarioRegistro =
                            "SELECT usuario
                             FROM usuario
                             WHERE id = ?";

                        $stmtUsuarioRegistro =
                            $conexion->prepare(
                                $sqlUsuarioRegistro
                            );

                        $stmtUsuarioRegistro->execute([
                            $facturaEditar[
                                "id_usuario_registro"
                            ]
                        ]);

                        $usuarioRegistro =
                            $stmtUsuarioRegistro->fetch(
                                PDO::FETCH_ASSOC
                            );

                        echo htmlspecialchars(
                            $usuarioRegistro["usuario"]
                            ?? "Usuario no encontrado"
                        );

                    ?>"
                    readonly
                >

            </p>


            <!-- =================================================
                 USUARIO ASIGNADO
                 ================================================= -->

            <p>

                <label for="id_usuario_asignado">
                    Usuario asignado:
                </label>

                <select
                    id="id_usuario_asignado"
                    name="id_usuario_asignado"
                >

                    <option value="">
                        -- Sin usuario asignado --
                    </option>


                    <?php foreach ($usuarios as $usuario): ?>

                        <option
                            value="<?= $usuario["id"] ?>"
                            <?= (
                                $facturaEditar[
                                    "id_usuario_asignado"
                                ] == $usuario["id"]
                            )
                                ? "selected"
                                : "" ?>
                        >

                            <?= htmlspecialchars(
                                $usuario["usuario"]
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </p>


            <!-- =================================================
                 BOTONES
                 ================================================= -->

            <p>

                <button type="submit">
                    Guardar cambios
                </button>

                <a href="index.php">
                    Cancelar
                </a>

            </p>

        </form>

    <?php endif; ?>


    <!-- =================================================
         JAVASCRIPT
         ================================================= -->

    <script>

        const estado = document.getElementById("estado");
        const fechaCobro = document.getElementById("fecha_cobro");

        function actualizarFechaCobro() {

            if (!estado || !fechaCobro) {
                return;
            }

            if (estado.value === "Cobrado") {

                fechaCobro.disabled = false;

            } else {

                fechaCobro.disabled = true;
                fechaCobro.value = "";
            }
        }


        if (estado) {

            estado.addEventListener(
                "change",
                actualizarFechaCobro
            );

            actualizarFechaCobro();
        }

    </script>

</body>

</html>