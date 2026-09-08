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

// Procesar modificación
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = $_POST["id"] ?? "";
    $nombre = trim($_POST["nombre"] ?? "");

    if ($id === "" || !is_numeric($id)) {

        $error = "Rol no válido.";

    } elseif ($nombre === "") {

        $error = "Debés ingresar el nombre del rol.";

    } else {

        try {

            // Verificar si ya existe otro rol con ese nombre
            $sql = "SELECT id FROM rol WHERE nombre = :nombre AND id != :id";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                ":nombre" => $nombre,
                ":id" => $id
            ]);

            $rolExistente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($rolExistente) {

                $error = "Rol ya existente en la base de datos, verifique";

            } else {

                // Actualizar el rol
                $sql = "UPDATE rol
                        SET nombre = :nombre
                        WHERE id = :id";

                $stmt = $conexion->prepare($sql);

                $stmt->execute([
                    ":nombre" => $nombre,
                    ":id" => $id
                ]);

                $mensaje = "Rol actualizado correctamente.";
            }

        } catch (PDOException $e) {

            $error = "Error al modificar el rol.";
        }
    }
}


// Obtener todos los roles
try {

    $sql = "SELECT id, nombre FROM rol ORDER BY id";

    $stmt = $conexion->query($sql);

    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $roles = [];
    $error = "No se pudieron cargar los roles.";
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar rol</title>

</head>

<body>

    <h1>Editar rol</h1>


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


    <h2>Roles registrados</h2>


    <?php if (count($roles) > 0): ?>

        <form method="POST">

            <label for="id">
                Seleccionar rol:
            </label>

            <select name="id" id="id" required>

                <option value="">
                    -- Seleccionar rol --
                </option>

                <?php foreach ($roles as $rol): ?>

                    <option value="<?= htmlspecialchars($rol["id"]) ?>">

                        <?= htmlspecialchars($rol["nombre"]) ?>

                    </option>

                <?php endforeach; ?>

            </select>


            <br><br>


            <label for="nombre">
                Nuevo nombre:
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                maxlength="100"
                required
            >


            <br><br>


            <button type="submit">
                Guardar cambios
            </button>

        </form>

    <?php else: ?>

        <p>
            No hay roles registrados.
        </p>

    <?php endif; ?>

</body>

</html>