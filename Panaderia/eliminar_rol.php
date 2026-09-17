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

// Procesar eliminación
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = $_POST["id"] ?? "";

    if ($id === "" || !is_numeric($id)) {

        $error = "Rol no válido.";

    } else {

        try {

            // Verificar que el rol exista
            $sql = "SELECT id, nombre FROM rol WHERE id = :id";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                ":id" => $id
            ]);

            $rol = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$rol) {

                $error = "El rol seleccionado no existe.";

            } else {

                // Eliminar el rol
                $sql = "DELETE FROM rol WHERE id = :id";

                $stmt = $conexion->prepare($sql);

                $stmt->execute([
                    ":id" => $id
                ]);

                $mensaje = "Rol eliminado correctamente.";
            }

        } catch (PDOException $e) {

            // Error por clave foránea
            if ($e->getCode() === "23000") {

                $error = "No se puede eliminar este rol porque está siendo utilizado por un usuario.";

            } else {

                $error = "Error al eliminar el rol.";
            }
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

    <title>Eliminar rol</title>
    <link rel="stylesheet" href="estilo.css">

</head>

<body>

    <h1>Eliminar rol</h1>


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


            <button type="submit">
                Eliminar rol
            </button>

        </form>

    <?php else: ?>

        <p>
            No hay roles registrados.
        </p>

    <?php endif; ?>

</body>

</html>