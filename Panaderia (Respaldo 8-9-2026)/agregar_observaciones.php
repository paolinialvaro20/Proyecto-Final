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
$error = "";

// Valores de los campos
$id_factura_seleccionada = "";
$detalle_ingresado = "";

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_factura = $_POST["id_factura"] ?? "";
    $detalle = trim($_POST["detalle"] ?? "");

    // Guardar los valores por si ocurre un error
    $id_factura_seleccionada = $id_factura;
    $detalle_ingresado = $detalle;

    if ($id_factura === "" || !is_numeric($id_factura)) {

        $error = "Debés seleccionar una factura.";

    } elseif ($detalle === "") {

        $error = "Debés ingresar una observación.";

    } else {

        try {

            // Verificar que la factura exista
            $sql = "SELECT id, numero_serie
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

                // Obtener fecha y hora actual del sistema
                $fecha_hora = date("Y-m-d H:i:s");

                // Insertar la observación
                $sql = "INSERT INTO observacion
                        (
                            detalle,
                            fecha_hora,
                            id_FACTURA_PENDIENTE,
                            id_USUARIO
                        )
                        VALUES
                        (
                            :detalle,
                            :fecha_hora,
                            :id_factura,
                            :id_usuario
                        )";

                $stmt = $conexion->prepare($sql);

                $stmt->execute([
                    ":detalle" => $detalle,
                    ":fecha_hora" => $fecha_hora,
                    ":id_factura" => $id_factura,
                    ":id_usuario" => $_SESSION["id_usuario"]
                ]);

                $mensaje = "Observación agregada correctamente.";

                // Limpiar los campos después de guardar correctamente
                $id_factura_seleccionada = "";
                $detalle_ingresado = "";
            }

        } catch (PDOException $e) {

            $error = "Error al guardar la observación.";
        }
    }
}


// Obtener las facturas
try {

    $sql = "SELECT id, numero_serie, monto, estado
            FROM factura
            ORDER BY id DESC";

    $stmt = $conexion->query($sql);

    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $facturas = [];
    $error = "No se pudieron cargar las facturas.";
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Agregar observación</title>

</head>

<body>

    <h1>Agregar observación</h1>


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


    <form method="POST">

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

                </option>

            <?php endforeach; ?>

        </select>


        <br><br>


        <label for="detalle">
            Observación:
        </label>

        <br>

        <textarea
            id="detalle"
            name="detalle"
            rows="5"
            cols="50"
            maxlength="255"
            required
        ><?= htmlspecialchars($detalle_ingresado) ?></textarea>


        <br><br>


        <button type="submit">
            Agregar observación
        </button>

    </form>

</body>

</html>