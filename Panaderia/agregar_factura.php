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

// Usuario que inició sesión
$id_usuario_registro = $_SESSION["id_usuario"];

// Obtener los datos del usuario que inició sesión
$sqlUsuario = "SELECT usuario
               FROM usuario
               WHERE id = ?";

$stmtUsuario = $conexion->prepare($sqlUsuario);
$stmtUsuario->execute([$id_usuario_registro]);

$usuarioRegistro = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

// Fecha y hora actual para mostrar en pantalla
$fechaRegistro = date("Y-m-d H:i:s");


// Obtener usuarios activos para el desplegable
$sqlUsuarios = "SELECT id, usuario
                FROM usuario
                WHERE activo = 1
                ORDER BY usuario";

$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->execute();

$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);


// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $numero_serie = trim($_POST["numero_serie"]);
    $monto = $_POST["monto"];
    $id_usuario_asignado = $_POST["id_usuario_asignado"];

    // Determinar estado y fecha de cobro
    if (isset($_POST["cobrado"])) {

        $estado = "Cobrado";
        $fecha_cobro = date("Y-m-d H:i:s");

    } else {

        $estado = "Pendiente";
        $fecha_cobro = null;
    }


    $sql = "INSERT INTO factura
            (
                numero_serie,
                monto,
                fecha_registro,
                fecha_cobro,
                estado,
                id_usuario_registro,
                id_usuario_asignado
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )";

    $stmt = $conexion->prepare($sql);

    if ($stmt->execute([
        $numero_serie,
        $monto,
        $fechaRegistro,
        $fecha_cobro,
        $estado,
        $id_usuario_registro,
        $id_usuario_asignado
    ])) {

        $mensaje = "Factura agregada correctamente.";

    } else {

        $mensaje = "Error al guardar la factura.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Nueva factura</title>

</head>

<body>
     <link rel="stylesheet" href="estilo.css">

    <h1>Nueva factura</h1>

    <?php if ($mensaje != ""): ?>

        <p>
            <?= htmlspecialchars($mensaje) ?>
        </p>

    <?php endif; ?>


    <form method="post">


        <p>

            <label for="usuario_registro">
                Usuario que registra:
            </label>

            <input
                type="text"
                id="usuario_registro"
                value="<?= htmlspecialchars($usuarioRegistro["usuario"]) ?>"
                readonly
            >

        </p>


        <p>

            <label for="fecha_registro">
                Fecha y hora de registro:
            </label>

            <input
                type="text"
                id="fecha_registro"
                value="<?= htmlspecialchars($fechaRegistro) ?>"
                readonly
            >

        </p>


        <p>

            <label for="numero_serie">
                Número de serie:
            </label>

            <input
                type="text"
                id="numero_serie"
                name="numero_serie"
                maxlength="20"
                required
            >

        </p>


        <p>

            <label for="monto">
                Monto:
            </label>

            <input
                type="number"
                id="monto"
                name="monto"
                step="0.01"
                min="0"
                required
            >

        </p>


        <p>

            <label for="id_usuario_asignado">
                Usuario asignado:
            </label>

            <select
                id="id_usuario_asignado"
                name="id_usuario_asignado"
                required
            >

                <option value="">
                    -- Seleccione un usuario --
                </option>

                <?php foreach ($usuarios as $usuario): ?>

                    <option value="<?= $usuario["id"] ?>">

                        <?= htmlspecialchars($usuario["usuario"]) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </p>


        <p>

            <label>

                <input
                    type="checkbox"
                    name="cobrado"
                    value="1"
                >

                Cobrado

            </label>

        </p>


        <p>

            <button type="submit">
                Guardar factura
            </button>

            <a href="index.php">
                Cancelar
            </a>

        </p>


    </form>

</body>

</html>