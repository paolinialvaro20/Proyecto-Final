<?php

require_once "conexion.php";

// Verificar que venga el ID del usuario
if (!isset($_GET["id"])) {
    die("Usuario no especificado. Poner ?id='numero' en la url");
}

$id_usuario = $_GET["id"];

// Obtener datos del usuario
$sqlUsuario = "SELECT id, usuario, id_ROL, activo
               FROM usuario
               WHERE id = ?";

$stmtUsuario = $conexion->prepare($sqlUsuario);
$stmtUsuario->execute([$id_usuario]);

$usuarioDatos = $stmtUsuario->fetch();

if (!$usuarioDatos) {
    die("Usuario no encontrado.");
}

// Obtener roles disponibles
$sqlRoles = "SELECT id, nombre FROM rol ORDER BY nombre";
$stmtRoles = $conexion->prepare($sqlRoles);
$stmtRoles->execute();

$roles = $stmtRoles->fetchAll();

/*
// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST["usuario"]);
    $id_rol = $_POST["id_rol"];
    $activo = isset($_POST["activo"]) ? 1 : 0;

    // Validar campos
    if ($usuario === "" || $id_rol === "") {

        $mensaje = "Todos los campos son obligatorios.";

    } else {

        // Verificar si OTRO usuario ya tiene ese nombre
        $sqlExiste = "SELECT id
                      FROM usuario
                      WHERE usuario = ?
                      AND id != ?";

        $stmtExiste = $conexion->prepare($sqlExiste);
        $stmtExiste->execute([
            $usuario,
            $id_usuario
        ]);

        if ($stmtExiste->fetch()) {

            $mensaje = "El nombre de usuario ya está registrado.";

        } else {

            // Actualizar usuario
            $sqlActualizar = "UPDATE usuario
                              SET usuario = ?,
                                  id_ROL = ?,
                                  activo = ?
                              WHERE id = ?";

            $stmtActualizar = $conexion->prepare($sqlActualizar);

            $stmtActualizar->execute([
                $usuario,
                $id_rol,
                $activo,
                $id_usuario
            ]);

            $mensaje = "Usuario actualizado correctamente.";

            // Actualizar datos mostrados
            $usuarioDatos["usuario"] = $usuario;
            $usuarioDatos["id_ROL"] = $id_rol;
            $usuarioDatos["activo"] = $activo;
        }
    }
}
*/
// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST["usuario"]);
    $contrasenia = $_POST["contrasenia"];
    $id_rol = $_POST["id_rol"];
    $activo = isset($_POST["activo"]) ? 1 : 0;

    // Validar campos
    if ($usuario === "" || $id_rol === "") {

        $mensaje = "Usuario y rol son obligatorios.";

    } else {

        // Verificar si OTRO usuario ya tiene ese nombre
        $sqlExiste = "SELECT id
                      FROM usuario
                      WHERE usuario = ?
                      AND id != ?";

        $stmtExiste = $conexion->prepare($sqlExiste);
        $stmtExiste->execute([
            $usuario,
            $id_usuario
        ]);

        if ($stmtExiste->fetch()) {

            $mensaje = "El nombre de usuario ya está registrado.";

        } else {

            // Si escribió una nueva contraseña
            if ($contrasenia !== "") {

                $contraseniaHash = password_hash(
                    $contrasenia,
                    PASSWORD_DEFAULT
                );

                $sqlActualizar = "UPDATE usuario
                                  SET usuario = ?,
                                      contrasenia = ?,
                                      id_ROL = ?,
                                      activo = ?
                                  WHERE id = ?";

                $stmtActualizar = $conexion->prepare($sqlActualizar);

                $stmtActualizar->execute([
                    $usuario,
                    $contraseniaHash,
                    $id_rol,
                    $activo,
                    $id_usuario
                ]);

            } else {

                // Si no escribió contraseña, mantiene la actual
                $sqlActualizar = "UPDATE usuario
                                  SET usuario = ?,
                                      id_ROL = ?,
                                      activo = ?
                                  WHERE id = ?";

                $stmtActualizar = $conexion->prepare($sqlActualizar);

                $stmtActualizar->execute([
                    $usuario,
                    $id_rol,
                    $activo,
                    $id_usuario
                ]);
            }

            $mensaje = "Usuario actualizado correctamente.";

            $usuarioDatos["usuario"] = $usuario;
            $usuarioDatos["id_ROL"] = $id_rol;
            $usuarioDatos["activo"] = $activo;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar usuario</title>

</head>

<body>

<?php require_once "menu.php"; ?>

<h1>Editar usuario</h1>


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
            value="<?= htmlspecialchars($usuarioDatos["usuario"]) ?>"
            required
        >

    </p>

    <p>

        <label for="contrasenia">
            Nueva contraseña:
        </label>

        <br>

        <input
            type="password"
            id="contrasenia"
            name="contrasenia"
        >

        <br>

        <small>
            Dejar vacío si no desea cambiar la contraseña.
        </small>

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
                    <?= ($usuarioDatos["id_ROL"] == $rol["id"]) ? "selected" : "" ?>
                >

                    <?= htmlspecialchars($rol["nombre"]) ?>

                </option>

            <?php endforeach; ?>

        </select>

    </p>


    <p>

        <label>

            <input
                type="checkbox"
                name="activo"
                <?= $usuarioDatos["activo"] ? "checked" : "" ?>
            >

            Usuario activo

        </label>

    </p>


    <button type="submit">
        Guardar cambios
    </button>

</form>


</body>

</html>