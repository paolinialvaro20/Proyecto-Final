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
$id_usuario_actual = $_SESSION["id_usuario"];


// Procesar eliminación
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_usuario_eliminar = $_POST["id_usuario"] ?? "";

    if ($id_usuario_eliminar == "") {

        $mensaje = "Debe seleccionar un usuario.";

    } elseif ($id_usuario_eliminar == $id_usuario_actual) {

        $mensaje = "No puede eliminar el usuario con el que inició sesión.";

    } else {

        // Desactivar usuario en lugar de eliminarlo físicamente
        $sql = "UPDATE usuario
                SET activo = 0
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);

        if ($stmt->execute([$id_usuario_eliminar])) {

            if ($stmt->rowCount() > 0) {

                $mensaje = "Usuario eliminado correctamente.";

            } else {

                $mensaje = "No se encontró el usuario seleccionado.";
            }

        } else {

            $mensaje = "Error al eliminar el usuario.";
        }
    }
}


// Obtener usuarios activos
$sqlUsuarios = "SELECT id, usuario
                FROM usuario
                WHERE activo = 1
                ORDER BY usuario";

$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->execute();

$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Eliminar usuario</title>
    <link rel="stylesheet" href="estilo.css">

</head>

<body>

    <h1>Eliminar usuario</h1>


    <?php if ($mensaje != ""): ?>

        <p>
            <?= htmlspecialchars($mensaje) ?>
        </p>

    <?php endif; ?>


    <form method="post" onsubmit="return confirmarEliminacion();">

        <p>

            <label for="id_usuario">
                Usuario:
            </label>

            <select
                id="id_usuario"
                name="id_usuario"
                required
            >

                <option value="">
                    -- Seleccione un usuario --
                </option>

                <?php foreach ($usuarios as $usuario): ?>

                    <?php if ($usuario["id"] != $id_usuario_actual): ?>

                        <option value="<?= $usuario["id"] ?>">

                            <?= htmlspecialchars($usuario["usuario"]) ?>

                        </option>

                    <?php endif; ?>

                <?php endforeach; ?>

            </select>

        </p>


        <p>

            <button type="submit">
                Eliminar usuario
            </button>

            <a href="index.php">
                Cancelar
            </a>

        </p>

    </form>


    <script>

        function confirmarEliminacion() {

            return confirm(
                "¿Está seguro de que desea eliminar este usuario?"
            );

        }

    </script>

</body>

</html>