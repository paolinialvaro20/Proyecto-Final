<?php
// =====================================================
// PERMISOS POR ROL  (relación N-N:  rol  <->  rol_permiso  <->  permiso)
//
// - Tabla `permiso`:      catálogo de funciones del sistema.
// - Tabla `rol_permiso`:  qué permisos tiene cada rol.
// - El rol "Administrador" siempre puede todo (no se puede quitar,
//   así nadie se deja afuera del sistema por error).
// - Los permisos de un rol se eligen al crear o editar el rol.
// - Un rol sin permisos solo puede: ver el inicio, cambiar su contraseña
//   y cerrar sesión.
// =====================================================
const ROLES_ADMIN = ["administrador"];

// ---------- Datos del usuario (cacheados por pedido) ----------
function datosPermisosUsuario(PDO $conexion, $idUsuario): array
{
    static $cache = [];
    $idUsuario = (int)$idUsuario;

    if (isset($cache[$idUsuario])) {
        return $cache[$idUsuario];
    }

    $stmt = $conexion->prepare(
        "SELECT u.id_ROL AS id_rol, LOWER(TRIM(r.nombre)) AS nombre
         FROM usuario u
         INNER JOIN rol r ON r.id = u.id_ROL
         WHERE u.id = ? AND u.activo = 1"
    );
    $stmt->execute([$idUsuario]);
    $fila = $stmt->fetch();

    if (!$fila) {
        return $cache[$idUsuario] = ["rol" => null, "id_rol" => null, "admin" => false, "permisos" => []];
    }

    $admin = in_array($fila["nombre"], ROLES_ADMIN, true);
    $permisos = [];

    if (!$admin) {
        try {
            $stmtP = $conexion->prepare(
                "SELECT p.clave
                 FROM rol_permiso rp
                 INNER JOIN permiso p ON p.id = rp.id_permiso
                 WHERE rp.id_rol = ?"
            );
            $stmtP->execute([(int)$fila["id_rol"]]);
            $permisos = $stmtP->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            // Si faltan las tablas (no se ejecutó permisos_por_rol.sql), nadie salvo el admin tiene permisos.
            error_log("No se pudieron leer los permisos del rol: " . $e->getMessage());
        }
    }

    return $cache[$idUsuario] = [
        "rol" => $fila["nombre"],
        "id_rol" => (int)$fila["id_rol"],
        "admin" => $admin,
        "permisos" => $permisos,
    ];
}

function obtenerRolUsuario(PDO $conexion, $idUsuario): ?string
{
    return datosPermisosUsuario($conexion, $idUsuario)["rol"];
}

function usuarioPuedeAdministrar(PDO $conexion, $idUsuario): bool
{
    return datosPermisosUsuario($conexion, $idUsuario)["admin"];
}

function usuarioTienePermiso(PDO $conexion, $idUsuario, string $permiso): bool
{
    $datos = datosPermisosUsuario($conexion, $idUsuario);
    return $datos["admin"] || in_array($permiso, $datos["permisos"], true);
}

function denegarAcceso(): void
{
    http_response_code(403);
    exit("No tenés permiso para acceder a esta función.");
}

function exigirPermiso(PDO $conexion, $idUsuario, string $permiso): void
{
    if (!usuarioTienePermiso($conexion, $idUsuario, $permiso)) {
        denegarAcceso();
    }
}

// Solo el rol Administrador.
function exigirPermisoAdministrativo(PDO $conexion, $idUsuario): void
{
    if (!usuarioPuedeAdministrar($conexion, $idUsuario)) {
        denegarAcceso();
    }
}

// ---------- Catálogo y asignación de permisos a roles ----------

// Todas las funciones del sistema: [id, clave, nombre, descripcion, grupo]
function catalogoPermisos(PDO $conexion): array
{
    return $conexion->query(
        "SELECT id, clave, nombre, descripcion, grupo FROM permiso ORDER BY id"
    )->fetchAll();
}

// IDs de los permisos que tiene un rol
function idsPermisosDelRol(PDO $conexion, int $idRol): array
{
    $stmt = $conexion->prepare("SELECT id_permiso FROM rol_permiso WHERE id_rol = ?");
    $stmt->execute([$idRol]);
    return array_map("intval", $stmt->fetchAll(PDO::FETCH_COLUMN));
}

// Claves de los permisos de un rol (para mostrar en la auditoría)
function clavesPermisosDelRol(PDO $conexion, int $idRol): array
{
    $stmt = $conexion->prepare(
        "SELECT p.clave FROM rol_permiso rp
         INNER JOIN permiso p ON p.id = rp.id_permiso
         WHERE rp.id_rol = ? ORDER BY p.id"
    );
    $stmt->execute([$idRol]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Claves que el usuario puede asignar a otros roles:
// el administrador, todas; los demás, solo las que ellos mismos tienen.
// (devuelve null = sin restricción)
function clavesAsignables(PDO $conexion, $idUsuario): ?array
{
    $datos = datosPermisosUsuario($conexion, $idUsuario);
    return $datos["admin"] ? null : $datos["permisos"];
}

// Reemplaza los permisos de un rol. Debe llamarse dentro de una transacción.
// $idsSolicitados: IDs de permiso (strings o enteros) recibidos del formulario.
// $clavesAsignables: null = sin restricción; array = solo se pueden tocar esas claves
//                    (los permisos que el usuario no puede administrar se conservan).
// Devuelve la lista de claves que quedaron asignadas.
function guardarPermisosRol(PDO $conexion, int $idRol, array $idsSolicitados, ?array $clavesAsignables = null): array
{
    $clavePorId = [];
    foreach (catalogoPermisos($conexion) as $p) {
        $clavePorId[(int)$p["id"]] = $p["clave"];
    }

    $solicitados = [];
    foreach ($idsSolicitados as $id) {
        if ((is_string($id) || is_int($id)) && ctype_digit((string)$id) && isset($clavePorId[(int)$id])) {
            $solicitados[(int)$id] = true;
        }
    }

    $finales = [];
    if ($clavesAsignables === null) {
        $finales = $solicitados;
    } else {
        // Se conserva lo que el usuario no tiene derecho a administrar...
        foreach (idsPermisosDelRol($conexion, $idRol) as $idActual) {
            if (isset($clavePorId[$idActual]) && !in_array($clavePorId[$idActual], $clavesAsignables, true)) {
                $finales[$idActual] = true;
            }
        }
        // ...y se agrega solo lo pedido que sí puede asignar.
        foreach (array_keys($solicitados) as $id) {
            if (in_array($clavePorId[$id], $clavesAsignables, true)) {
                $finales[$id] = true;
            }
        }
    }

    $conexion->prepare("DELETE FROM rol_permiso WHERE id_rol = ?")->execute([$idRol]);

    $stmtIns = $conexion->prepare("INSERT INTO rol_permiso (id_rol, id_permiso) VALUES (?, ?)");
    $claves = [];
    foreach (array_keys($finales) as $id) {
        $stmtIns->execute([$idRol, $id]);
        $claves[] = $clavePorId[$id];
    }

    return $claves;
}

// ---------- Protección de los administradores ----------
function rolEsAdministrador(PDO $conexion, int $idRol): bool
{
    $stmt = $conexion->prepare("SELECT LOWER(TRIM(nombre)) FROM rol WHERE id = ?");
    $stmt->execute([$idRol]);
    $nombre = $stmt->fetchColumn();
    return $nombre !== false && in_array($nombre, ROLES_ADMIN, true);
}

// ¿El usuario (activo o no) tiene el rol Administrador?
function esUsuarioAdministrador(PDO $conexion, int $idUsuario): bool
{
    $stmt = $conexion->prepare(
        "SELECT COUNT(*) FROM usuario u INNER JOIN rol r ON r.id = u.id_ROL
         WHERE u.id = ? AND LOWER(TRIM(r.nombre)) = 'administrador'"
    );
    $stmt->execute([$idUsuario]);
    return (int)$stmt->fetchColumn() > 0;
}

// Quien no es administrador no puede tocar cuentas de administradores
// ni asignar ese rol (evita escalar privilegios).
function puedeTocarUsuario(PDO $conexion, $idActor, int $idObjetivo): bool
{
    return usuarioPuedeAdministrar($conexion, $idActor)
        || !esUsuarioAdministrador($conexion, $idObjetivo);
}

function puedeAsignarRol(PDO $conexion, $idActor, int $idRol): bool
{
    return usuarioPuedeAdministrar($conexion, $idActor)
        || !rolEsAdministrador($conexion, $idRol);
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
