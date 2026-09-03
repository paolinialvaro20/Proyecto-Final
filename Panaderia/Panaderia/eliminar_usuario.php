
<?php
//METODO PARA ELIMINAR UN USUARIO


/*
require_once "conexion.php";

// Verificar que venga el ID
if (!isset($_GET["id"])) {
    die("Usuario no especificado.");
}

$id_usuario = $_GET["id"];

// Buscar usuario
$sqlUsuario = "SELECT usuario FROM usuario WHERE id = ?";
$stmtUsuario = $conexion->prepare($sqlUsuario);
$stmtUsuario->execute([$id_usuario]);

$usuarioDatos = $stmtUsuario->fetch();

if (!$usuarioDatos) {
    die("Usuario no encontrado.");
}

// Si confirma eliminación
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sqlEliminar = "DELETE FROM usuario WHERE id = ?";
    $stmtEliminar = $conexion->prepare($sqlEliminar);

    $stmtEliminar->execute([$id_usuario]);

    $mensaje = "Usuario eliminado correctamente.";
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Eliminar usuario</title>
</head>

<body>

<?php require_once "menu.php"; ?>

<h1>Eliminar usuario</h1>

<?php if (isset($mensaje)): ?>

    <p>
        <?= htmlspecialchars($mensaje) ?>
    </p>

<?php else: ?>

    <p>
        ¿Está seguro que desea eliminar al usuario
        <strong>
            <?= htmlspecialchars($usuarioDatos["usuario"]) ?>
        </strong>?
    </p>

    <form method="POST">

        <button type="submit">
            Sí, eliminar usuario
        </button>

        <a href="usuarios.php">
            Cancelar
        </a>

    </form>

<?php endif; ?>

</body>
</html>
*/


//METODO PARA DESACTIVAR EL USUARIO

require_once "conexion.php";

// Verificar que venga el ID
if (!isset($_GET["id"])) {
    die("Usuario no especificado.");
}

$id_usuario = $_GET["id"];

// Buscar usuario
$sqlUsuario = "SELECT id, usuario, activo
               FROM usuario
               WHERE id = ?";

$stmtUsuario = $conexion->prepare($sqlUsuario);
$stmtUsuario->execute([$id_usuario]);

$usuarioDatos = $stmtUsuario->fetch();

if (!$usuarioDatos) {
    die("Usuario no encontrado.");
}


// Cuando confirma
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Desactivar usuario
    $sql = "UPDATE usuario
            SET activo = 0
            WHERE id = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_usuario]);

    $mensaje = "Usuario desactivado correctamente.";
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Desactivar usuario</title>

</head>

<body>

<?php require_once "menu.php"; ?>

<h1>Desactivar usuario</h1>


<?php if (isset($mensaje)): ?>

    <p>
        <?= htmlspecialchars($mensaje) ?>
    </p>

    <a href="usuarios.php">Volver a usuarios</a>

<?php else: ?>

    <p>
        ¿Está seguro que desea desactivar al usuario
        <strong>
            <?= htmlspecialchars($usuarioDatos["usuario"]) ?>
        </strong>?
    </p>

    <form method="POST">

        <button type="submit">
            Sí, desactivar
        </button>

        <a href="listar_usuario.php">
            Cancelar
        </a>

    </form>

<?php endif; ?>

</body>

</html>