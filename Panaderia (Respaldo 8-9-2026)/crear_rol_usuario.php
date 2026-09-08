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

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");

    if ($nombre === "") {

        $error = "Debés ingresar el nombre del rol.";

    } else {

        
try {

    $sql = "INSERT INTO rol (nombre) VALUES (:nombre)";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ":nombre" => $nombre
    ]);

    $mensaje = "Rol agregado correctamente.";

} catch (PDOException $e) {

    // Error de clave duplicada
    if ($e->errorInfo[0] === "23000") {

        $error = "Rol ya existente en la base de datos, verifique";

    } else {

        $error = "Error al agregar el rol.";

    }
}

    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Agregar rol</title>
</head>

<body>

    <h1>Agregar nuevo rol</h1>

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

        <label for="nombre">
            Nombre del rol:
        </label>

        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="100"
            required
        >

        <button type="submit">
            Agregar rol
        </button>

    </form>

</body>

</html>