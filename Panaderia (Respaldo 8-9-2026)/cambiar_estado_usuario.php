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

// Procesar cambio de estado
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = $_POST["id"] ?? "";
    $activo = $_POST["activo"] ?? "";

    if ($id === "" || !is_numeric($id)) {

        $error = "Usuario no válido.";

    } elseif ($activo !== "0" && $activo !== "1") {

        $error = "Estado no válido.";

    } else {

        try {

            // Verificar que el usuario exista
            $sql = "SELECT id, usuario, activo
                    FROM usuario
                    WHERE id = :id";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                ":id" => $id
            ]);

            $usuarioSeleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuarioSeleccionado) {

                $error = "El usuario seleccionado no existe.";

            } else {

                // Cambiar estado
                $sql = "UPDATE usuario
                        SET activo = :activo
                        WHERE id = :id";

                $stmt = $conexion->prepare($sql);

                $stmt->execute([
                    ":activo" => $activo,
                    ":id" => $id
                ]);

                if ($activo === "1") {

                    $mensaje = "Usuario activado correctamente.";

                } else {

                    $mensaje = "Usuario desactivado correctamente.";
                }
            }

        } catch (PDOException $e) {

            $error = "Error al cambiar el estado del usuario.";
        }
    }
}


// Obtener todos los usuarios
try {

    $sql = "SELECT id, usuario, activo
            FROM usuario
            ORDER BY id";

    $stmt = $conexion->query($sql);

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $usuarios = [];
    $error = "No se pudieron cargar los usuarios.";
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cambiar estado de usuario</title>

</head>

<body>

    <h1>Cambiar estado de usuario</h1>


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


    <h2>Usuarios registrados</h2>


    <?php if (count($usuarios) > 0): ?>

        <form method="POST">

            <label for="id">
                Seleccionar usuario:
            </label>

            <select name="id" id="id" required>

                <option value="">
                    -- Seleccionar usuario --
                </option>

                <?php foreach ($usuarios as $usuario): ?>

                    <option value="<?= htmlspecialchars($usuario["id"]) ?>">

                        <?= htmlspecialchars($usuario["usuario"]) ?>

                        -

                        <?php if ($usuario["activo"] == 1): ?>

                            Activo

                        <?php else: ?>

                            Inactivo

                        <?php endif; ?>

                    </option>

                <?php endforeach; ?>

            </select>


            <br><br>


            <label for="activo">
                Nuevo estado:
            </label>

            <select name="activo" id="activo" required>

                <option value="">
                    -- Seleccionar estado --
                </option>

                <option value="1">
                    Activo
                </option>

                <option value="0">
                    Inactivo
                </option>

            </select>


            <br><br>


            <button type="submit">
                Cambiar estado
            </button>

        </form>

    <?php else: ?>

        <p>
            No hay usuarios registrados.
        </p>

    <?php endif; ?>

</body>

</html>