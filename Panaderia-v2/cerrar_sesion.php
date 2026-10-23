<?php
session_start();

require_once "conexion.php";
require_once "registrar_auditoria.php";

// Verificar que exista una sesión iniciada
if (isset($_SESSION["id_usuario"])) {

    $idUsuario = (int) $_SESSION["id_usuario"];
    $nombreUsuario = $_SESSION["usuario"] ?? "Usuario desconocido";

    // Registrar el cierre de sesión
    registrarAuditoria(
        $conexion,
        $idUsuario,
        "CERRAR SESIÓN",
        "Usuario",
        $idUsuario,
        "El usuario " . $nombreUsuario .
            " cerró su sesión correctamente",
        "Sesión iniciada",
        "Sesión cerrada"
    );
}

// Vaciar los datos de la sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Redirigir al login
header("Location: login.php");
exit;
