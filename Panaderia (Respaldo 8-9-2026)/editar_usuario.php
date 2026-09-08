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

// ID del usuario que se quiere editar
// Se utiliza internamente, no es editable.
$id_usuario_editar = $_GET["id"] ?? $_POST["id_usuario"] ?? "";


// =====================================================
// OBTENER USUARIOS
// =====================================================

$sqlUsuarios = "SELECT id, usuario
                FROM usuario
                WHERE activo = 1
                ORDER BY usuario";

$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->execute();

$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// OBTENER ROLES DESDE LA TABLA rol
// =====================================================

$sqlRoles = "SELECT id, nombre
             FROM rol
             ORDER BY nombre";

$stmtRoles = $conexion->prepare($sqlRoles);
$stmtRoles->execute();

$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);


// =====================================================
// PROCESAR FORMULARIO
// =====================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_usuario = $_POST["id_usuario"] ?? "";
    $usuario = trim($_POST["usuario"] ?? "");
    $contrasenia = $_POST["contrasenia"] ?? "";
    $activo = isset($_POST["activo"]) ? 1 : 0;
    $id_ROL = $_POST["id_ROL"] ?? "";


    // Verificar campos obligatorios
    if ($id_usuario == "" || $usuario == "" || $id_ROL == "") {

        $mensaje = "Debe completar todos los campos obligatorios.";

    } else {

        // =================================================
        // VERIFICAR QUE EL USUARIO NO ESTÉ REPETIDO
        // =================================================

        $sqlVerificar = "SELECT id
                         FROM usuario
                         WHERE usuario = ?
                         AND id != ?";

        $stmtVerificar = $conexion->prepare($sqlVerificar);

        $stmtVerificar->execute([
            $usuario,
            $id_usuario
        ]);

        $usuarioExistente = $stmtVerificar->fetch(PDO::FETCH_ASSOC);


        if ($usuarioExistente) {

            $mensaje = "Ya existe otro usuario con ese nombre.";

        } else {

            // =================================================
            // VERIFICAR QUE EL ROL EXISTA
            // =================================================

            $sqlVerificarRol = "SELECT id
                                FROM rol
                                WHERE id = ?";

            $stmtVerificarRol = $conexion->prepare($sqlVerificarRol);

            $stmtVerificarRol->execute([
                $id_ROL
            ]);

            $rolExiste = $stmtVerificarRol->fetch(PDO::FETCH_ASSOC);


            if (!$rolExiste) {

                $mensaje = "El rol seleccionado no existe.";

            } else {

                // =================================================
                // ACTUALIZAR USUARIO
                // =================================================

                /*
                 * Si se escribe una nueva contraseña,
                 * se actualiza.
                 *
                 * Si se deja vacía, se conserva
                 * la contraseña actual.
                 */

                if ($contrasenia != "") {

                    $contraseniaHash = password_hash(
                        $contrasenia,
                        PASSWORD_DEFAULT
                    );


                    $sql = "UPDATE usuario
                            SET usuario = ?,
                                contrasenia = ?,
                                activo = ?,
                                id_ROL = ?
                            WHERE id = ?";

                    $stmt = $conexion->prepare($sql);

                    $resultado = $stmt->execute([
                        $usuario,
                        $contraseniaHash,
                        $activo,
                        $id_ROL,
                        $id_usuario
                    ]);

                } else {

                    $sql = "UPDATE usuario
                            SET usuario = ?,
                                activo = ?,
                                id_ROL = ?
                            WHERE id = ?";

                    $stmt = $conexion->prepare($sql);

                    $resultado = $stmt->execute([
                        $usuario,
                        $activo,
                        $id_ROL,
                        $id_usuario
                    ]);
                }


                if ($resultado) {

                    $mensaje = "Usuario actualizado correctamente.";

                    $id_usuario_editar = $id_usuario;

                } else {

                    $mensaje = "Error al actualizar el usuario.";
                }
            }
        }
    }
}


// =====================================================
// OBTENER DATOS DEL USUARIO SELECCIONADO
// =====================================================

$usuarioEditar = null;

if ($id_usuario_editar != "") {

    $sqlUsuario = "SELECT id,
                          usuario,
                          activo,
                          id_ROL
                   FROM usuario
                   WHERE id = ?";

    $stmtUsuario = $conexion->prepare($sqlUsuario);

    $stmtUsuario->execute([
        $id_usuario_editar
    ]);

    $usuarioEditar = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
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

    <h1>Editar usuario</h1>


    <?php if ($mensaje != ""): ?>

        <p>
            <?= htmlspecialchars($mensaje) ?>
        </p>

    <?php endif; ?>


    <!-- =================================================
         SELECCIONAR USUARIO
         ================================================= -->

    <form method="get">

        <p>

            <label for="id">
                Usuario a editar:
            </label>

            <select
                id="id"
                name="id"
                onchange="this.form.submit()"
                required
            >

                <option value="">
                    -- Seleccione un usuario --
                </option>


                <?php foreach ($usuarios as $usuario): ?>

                    <option
                        value="<?= $usuario["id"] ?>"
                        <?= ($id_usuario_editar == $usuario["id"]) ? "selected" : "" ?>
                    >

                        <?= htmlspecialchars($usuario["usuario"]) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </p>

    </form>


    <?php if ($usuarioEditar): ?>

        <hr>


        <!-- =================================================
             FORMULARIO DE EDICIÓN
             ================================================= -->

        <form method="post">

            <!--
                ID utilizado internamente para identificar
                el registro que se va a actualizar.
                No es editable.
            -->
            <input
                type="hidden"
                name="id_usuario"
                value="<?= $usuarioEditar["id"] ?>"
            >


            <!-- =================================================
                 USUARIO
                 ================================================= -->

            <p>

                <label for="usuario">
                    Usuario:
                </label>

                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    value="<?= htmlspecialchars($usuarioEditar["usuario"]) ?>"
                    maxlength="32"
                    required
                >

            </p>


            <!-- =================================================
                 CONTRASEÑA
                 ================================================= -->

            <p>

                <label for="contrasenia">
                    Nueva contraseña:
                </label>

                <input
                    type="password"
                    id="contrasenia"
                    name="contrasenia"
                    maxlength="255"
                >

            </p>

            <p>

                <small>
                    Deje vacío este campo para conservar la contraseña actual.
                </small>

            </p>


            <!-- =================================================
                 ESTADO
                 ================================================= -->

            <p>

                <label for="activo">
                    Activo:
                </label>

                <input
                    type="checkbox"
                    id="activo"
                    name="activo"
                    value="1"
                    <?= ($usuarioEditar["activo"] == 1) ? "checked" : "" ?>
                >

            </p>


            <!-- =================================================
                 ROL
                 ================================================= -->

            <p>

                <label for="id_ROL">
                    Rol:
                </label>

                <select
                    id="id_ROL"
                    name="id_ROL"
                    required
                >

                    <option value="">
                        -- Seleccione un rol --
                    </option>


                    <?php foreach ($roles as $rol): ?>

                        <option
                            value="<?= $rol["id"] ?>"
                            <?= ($usuarioEditar["id_ROL"] == $rol["id"]) ? "selected" : "" ?>
                        >

                            <?= htmlspecialchars($rol["nombre"]) ?>

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


</body>

</html>