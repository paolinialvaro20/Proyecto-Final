<?php
function obtenerRolUsuario(PDO $conexion, $idUsuario): ?string
{
    static $roles = [];
    $idUsuario = (int)$idUsuario;

    if (!array_key_exists($idUsuario, $roles)) {
        $stmt = $conexion->prepare(
            "SELECT LOWER(TRIM(r.nombre))
             FROM usuario u
             INNER JOIN rol r ON r.id = u.id_ROL
             WHERE u.id = ? AND u.activo = 1"
        );
        $stmt->execute([$idUsuario]);
        $rol = $stmt->fetchColumn();
        $roles[$idUsuario] = $rol === false ? null : $rol;
    }

    return $roles[$idUsuario];
}

function usuarioPuedeAdministrar(PDO $conexion, $idUsuario): bool
{
    $rol = obtenerRolUsuario($conexion, $idUsuario);
    return $rol !== null && !in_array($rol, ["repartidor", "cajero", "cajera"], true);
}

function usuarioPuedeGestionarFacturas(PDO $conexion, $idUsuario): bool
{
    $rol = obtenerRolUsuario($conexion, $idUsuario);
    return $rol !== null && $rol !== "repartidor";
}

function exigirPermisoAdministrativo(PDO $conexion, $idUsuario): void
{
    if (!usuarioPuedeAdministrar($conexion, $idUsuario)) {
        http_response_code(403);
        exit("No tenés permiso para acceder a esta función.");
    }
}

function exigirPermisoGestionFacturas(PDO $conexion, $idUsuario): void
{
    if (!usuarioPuedeGestionarFacturas($conexion, $idUsuario)) {
        http_response_code(403);
        exit("No tenés permiso para acceder a esta función.");
    }
}

function exigirPermisoNoCajero(PDO $conexion, $idUsuario): void
{
    $rol = obtenerRolUsuario($conexion, $idUsuario);

    if ($rol === null || in_array($rol, ["cajero", "cajera"], true)) {
        http_response_code(403);
        exit("No tenés permiso para acceder a esta función.");
    }
}
