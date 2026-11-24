<?php
// =====================================================
// PERMISOS POR ROL (denegar por defecto)
// Un rol nuevo NO obtiene ningún permiso hasta que se lo agregue
// explícitamente en las listas de abajo.
// =====================================================
const ROLES_ADMIN      = ["administrador"];
const ROLES_FACTURAS   = ["administrador", "cajero", "cajera"];   // crear / editar / cambiar estado
const ROLES_LIQUIDAN   = ["administrador"];                       // confirmar liquidaciones
const ROLES_CAJERO     = ["cajero", "cajera"];

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

function rolEn(PDO $conexion, $idUsuario, array $permitidos): bool
{
    $rol = obtenerRolUsuario($conexion, $idUsuario);
    return $rol !== null && in_array($rol, $permitidos, true);
}

function usuarioPuedeAdministrar(PDO $conexion, $idUsuario): bool
{
    return rolEn($conexion, $idUsuario, ROLES_ADMIN);
}

function usuarioPuedeGestionarFacturas(PDO $conexion, $idUsuario): bool
{
    return rolEn($conexion, $idUsuario, ROLES_FACTURAS);
}

function usuarioPuedeLiquidar(PDO $conexion, $idUsuario): bool
{
    return rolEn($conexion, $idUsuario, ROLES_LIQUIDAN);
}

function denegarAcceso(): void
{
    http_response_code(403);
    exit("No tenés permiso para acceder a esta función.");
}

function exigirPermisoAdministrativo(PDO $conexion, $idUsuario): void
{
    if (!usuarioPuedeAdministrar($conexion, $idUsuario)) {
        denegarAcceso();
    }
}

function exigirPermisoGestionFacturas(PDO $conexion, $idUsuario): void
{
    if (!usuarioPuedeGestionarFacturas($conexion, $idUsuario)) {
        denegarAcceso();
    }
}

function exigirPermisoLiquidar(PDO $conexion, $idUsuario): void
{
    if (!usuarioPuedeLiquidar($conexion, $idUsuario)) {
        denegarAcceso();
    }
}

// Se mantiene por compatibilidad: ahora equivale a "puede liquidar".
function exigirPermisoNoCajero(PDO $conexion, $idUsuario): void
{
    exigirPermisoLiquidar($conexion, $idUsuario);
}

// Cualquier usuario activo con un rol válido (para pantallas de consulta).
function exigirUsuarioActivo(PDO $conexion, $idUsuario): void
{
    if (obtenerRolUsuario($conexion, $idUsuario) === null) {
        header("Location: cerrar_sesion.php");
        exit;
    }
}

// Cantidad de administradores activos, sin contar a $excluirId.
function otrosAdministradoresActivos(PDO $conexion, int $excluirId): int
{
    $stmt = $conexion->prepare(
        "SELECT COUNT(*)
         FROM usuario u INNER JOIN rol r ON r.id = u.id_ROL
         WHERE u.activo = 1 AND u.id <> ? AND LOWER(TRIM(r.nombre)) = 'administrador'"
    );
    $stmt->execute([$excluirId]);
    return (int)$stmt->fetchColumn();
}

function esAdministradorActivo(PDO $conexion, int $idUsuario): bool
{
    $stmt = $conexion->prepare(
        "SELECT COUNT(*)
         FROM usuario u INNER JOIN rol r ON r.id = u.id_ROL
         WHERE u.activo = 1 AND u.id = ? AND LOWER(TRIM(r.nombre)) = 'administrador'"
    );
    $stmt->execute([$idUsuario]);
    return (int)$stmt->fetchColumn() > 0;
}

function rolEsAdministrador(PDO $conexion, int $idRol): bool
{
    $stmt = $conexion->prepare("SELECT LOWER(TRIM(nombre)) FROM rol WHERE id = ?");
    $stmt->execute([$idRol]);
    $nombre = $stmt->fetchColumn();
    return $nombre !== false && in_array($nombre, ROLES_ADMIN, true);
}
