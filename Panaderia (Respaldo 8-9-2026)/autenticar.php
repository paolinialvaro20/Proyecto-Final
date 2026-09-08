<?php

session_start();

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$usuario = trim($_POST["usuario"] ?? "");
$contrasenia = $_POST["contrasenia"] ?? "";

if ($usuario === "" || $contrasenia === "") {
    header("Location: login.php?error=Complete todos los campos");
    exit;
}

$sql = "SELECT id, usuario, contrasenia, activo, id_ROL
        FROM usuario
        WHERE usuario = :usuario
        LIMIT 1";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ":usuario" => $usuario
]);

$datosUsuario = $stmt->fetch();

if (!$datosUsuario) {
    header("Location: login.php?error=Usuario o contraseña incorrectos");
    exit;
}

if ((int)$datosUsuario["activo"] !== 1) {
    header("Location: login.php?error=El usuario está inactivo");
    exit;
}

if (!password_verify($contrasenia, $datosUsuario["contrasenia"])) {
    header("Location: login.php?error=Usuario o contraseña incorrectos");
    exit;
}

session_regenerate_id(true);

$_SESSION["id_usuario"] = $datosUsuario["id"];
$_SESSION["usuario"] = $datosUsuario["usuario"];
$_SESSION["id_ROL"] = $datosUsuario["id_ROL"];

header("Location: index.php");
exit;