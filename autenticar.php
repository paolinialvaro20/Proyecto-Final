<?php
session_start();
require_once "conexion.php";
require_once "registrar_auditoria.php";

// Verificar que la solicitud sea POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$usuario = trim($_POST["usuario"] ?? "");
$contrasenia = $_POST["contrasenia"] ?? "";

// Verificar que los campos no estén vacíos
if ($usuario === "" || $contrasenia === "") {
    header("Location: login.php?error=Complete todos los campos");
    exit;
}

// Buscar el usuario
$sql = "SELECT id, usuario, contrasenia, activo, id_ROL
        FROM usuario
        WHERE usuario = :usuario
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->execute([":usuario" => $usuario]);
$datosUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Usuario inexistente
if (!$datosUsuario) {
    registrarAuditoria(
        $conexion,
        null,
        "INICIO DE SESIÓN FALLIDO",
        "Usuario",
        null,
        "Intento de inicio de sesión con un usuario inexistente: " . $usuario,
        null,
        null
    );

    header("Location: login.php?error=Usuario o contraseña incorrectos");
    exit;
}

// Usuario inactivo
if ((int)$datosUsuario["activo"] !== 1) {
    registrarAuditoria(
        $conexion,
        (int)$datosUsuario["id"],
        "INICIO DE SESIÓN FALLIDO",
        "Usuario",
        (int)$datosUsuario["id"],
        "Intento de inicio de sesión con el usuario inactivo: " .
            $datosUsuario["usuario"],
        "Activo",
        "Inactivo"
    );

    header("Location: login.php?error=El usuario está inactivo");
    exit;
}

// Contraseña incorrecta
if (!password_verify($contrasenia, $datosUsuario["contrasenia"])) {
    registrarAuditoria(
        $conexion,
        (int)$datosUsuario["id"],
        "INICIO DE SESIÓN FALLIDO",
        "Usuario",
        (int)$datosUsuario["id"],
        "Intento de inicio de sesión fallido para el usuario: " .
            $datosUsuario["usuario"],
        null,
        null
    );

    header("Location: login.php?error=Usuario o contraseña incorrectos");
    exit;
}

// Regenerar el identificador de sesión
session_regenerate_id(true);

// Guardar los datos del usuario en la sesión
$_SESSION["id_usuario"] = $datosUsuario["id"];
$_SESSION["usuario"] = $datosUsuario["usuario"];
$_SESSION["id_ROL"] = $datosUsuario["id_ROL"];

// Registrar el inicio de sesión exitoso
registrarAuditoria(
    $conexion,
    (int)$datosUsuario["id"],
    "INICIO DE SESIÓN",
    "Usuario",
    (int)$datosUsuario["id"],
    "El usuario " . $datosUsuario["usuario"] .
        " inició sesión correctamente",
    "-",
    "Sesión iniciada"
);

// Redirigir al sistema
header("Location: index.php");
exit;
