<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panadería</title>
</head>

<body>

<h1>Sistema de Panadería</h1>

<p>
    Usuario:
    <?= htmlspecialchars($_SESSION["usuario"]) ?>
</p>

<p>
    ID del rol:
    <?= htmlspecialchars($_SESSION["id_ROL"]) ?>
</p>

<a href="cerrar_sesion.php">Cerrar sesión</a>

</body>
</html>