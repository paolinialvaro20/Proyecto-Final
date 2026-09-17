<?php

session_start();

require_once "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// Obtener los roles disponibles
$sqlRoles = "SELECT id, nombre FROM rol ORDER BY nombre";

$stmtRoles = $conexion->prepare($sqlRoles);
$stmtRoles->execute();

$roles = $stmtRoles->fetchAll();


// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST["usuario"]);
    $contrasenia = $_POST["contrasenia"];
    $id_rol = $_POST["id_rol"];


    // Validaciones básicas
    if ($usuario === "" || $contrasenia === "" || $id_rol === "") {

        $mensaje = "Todos los campos son obligatorios.";

    } else {

        // Comprobar si el usuario ya existe
        $sqlExiste = "SELECT id
                      FROM usuario
                      WHERE usuario = ?";

        $stmtExiste = $conexion->prepare($sqlExiste);
        $stmtExiste->execute([$usuario]);


        if ($stmtExiste->fetch()) {

            $mensaje = "El nombre de usuario ya existe.";

        } else {

            // Encriptar contraseña
            $contraseniaHash = password_hash(
                $contrasenia,
                PASSWORD_DEFAULT
            );


            // Crear usuario
            $sql = "INSERT INTO usuario
                        (usuario, contrasenia, activo, id_ROL)
                    VALUES
                        (?, ?, 1, ?)";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                $usuario,
                $contraseniaHash,
                $id_rol
            ]);


            $mensaje = "Usuario creado correctamente.";

            // Vaciar valores del formulario
            $usuario = "";
        }
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

    <title>Crear usuario</title>

    <link rel="stylesheet" href="estilo.css">

</head>

<body>

<?php require_once "menu.php"; ?>


<h1>Crear usuario</h1>


<?php if (isset($mensaje)): ?>

    <p>
        <?= htmlspecialchars($mensaje) ?>
    </p>

<?php endif; ?>


<form method="POST">

    <p>

        <label for="usuario">
            Usuario:
        </label>

        <br>

        <input
            type="text"
            id="usuario"
            name="usuario"
            value="<?= htmlspecialchars($usuario ?? '') ?>"
            required
        >

    </p>


    <p>

        <label for="contrasenia">
            Contraseña:
        </label>

        <br>

        <input
            type="password"
            id="contrasenia"
            name="contrasenia"
            required
        >

    </p>


    <p>

        <label for="id_rol">
            Rol:
        </label>

        <br>

        <select
            id="id_rol"
            name="id_rol"
            required
        >

            <option value="">
                Seleccione un rol
            </option>


            <?php foreach ($roles as $rol): ?>

                <option
                    value="<?= htmlspecialchars($rol["id"]) ?>"
                    <?= (
                        isset($_POST["id_rol"])
                        &&
                        $_POST["id_rol"] == $rol["id"]
                    )
                        ? "selected"
                        : ""
                    ?>
                >

                    <?= htmlspecialchars($rol["nombre"]) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </p>


    <button type="submit">
        Crear usuario
    </button>

</form>


</body>

</html>