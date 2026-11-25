<?php
require_once __DIR__ . "/seguridad.php";
require_once "conexion.php";
require_once "registrar_auditoria.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "permisos.php";
require_once "permisos_ui.php";
exigirPermiso($conexion, $_SESSION["id_usuario"], "roles.editar");

$mensaje = "";
$error = "";

$esAdminActual = usuarioPuedeAdministrar($conexion, $_SESSION["id_usuario"]);
$idRolActual = datosPermisosUsuario($conexion, $_SESSION["id_usuario"])["id_rol"];
$clavesAsignables = clavesAsignables($conexion, $_SESSION["id_usuario"]);

// Lista de funciones del sistema (tabla permiso)
try {
    $catalogo = catalogoPermisos($conexion);
} catch (PDOException $e) {
    error_log("No se pudo leer el catálogo de permisos: " . $e->getMessage());
    $catalogo = [];
    $error = "No se pudo cargar la lista de permisos. ¿Se ejecutó database/permisos_por_rol.sql?";
}

function cargarRoles(PDO $conexion): array
{
    return $conexion->query("SELECT id, nombre FROM rol ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
}

try {
    $roles = cargarRoles($conexion);
} catch (PDOException $e) {
    $roles = [];
    $error = "No se pudieron cargar los roles.";
}

// Rol elegido (por la lista de arriba o por el formulario enviado)
$idElegido = $_POST["id"] ?? $_GET["id"] ?? "";
$rolElegido = null;
foreach ($roles as $r) {
    if ((string)$r["id"] === (string)$idElegido) {
        $rolElegido = $r;
        break;
    }
}

$enviado = $_SERVER["REQUEST_METHOD"] === "POST";
$nombreEnviado = "";
$marcadosEnviados = [];

// ---------- Procesar modificación ----------
if ($enviado && $catalogo !== []) {
    $nombreEnviado = trim($_POST["nombre"] ?? "");
    $marcadosEnviados = array_values(array_filter((array)($_POST["permisos"] ?? []), "is_string"));

    if ($rolElegido === null) {
        $error = "Rol no válido.";

    } elseif (rolEsAdministrador($conexion, (int)$rolElegido["id"])) {
        $error = "El rol Administrador no se puede modificar.";

    } elseif (!$esAdminActual && (int)$rolElegido["id"] === (int)$idRolActual) {
        $error = "No podés modificar tu propio rol.";

    } elseif ($nombreEnviado === "") {
        $error = "Debés ingresar el nombre del rol.";

    } elseif (mb_strlen($nombreEnviado) > 20) {
        $error = "El nombre del rol no puede superar los 20 caracteres.";

    } elseif (in_array(mb_strtolower($nombreEnviado), ROLES_ADMIN, true)) {
        $error = "Ese nombre de rol está reservado.";

    } else {
        try {
            // ¿Existe otro rol con el mismo nombre?
            $stmt = $conexion->prepare("SELECT id FROM rol WHERE nombre = :nombre AND id != :id");
            $stmt->execute([":nombre" => $nombreEnviado, ":id" => $rolElegido["id"]]);

            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $error = "Rol ya existente en la base de datos, verifique.";

            } else {
                $idRol = (int)$rolElegido["id"];
                $nombreAnterior = $rolElegido["nombre"];
                $clavesAntes = clavesPermisosDelRol($conexion, $idRol);

                $conexion->beginTransaction();

                // Cambiar el nombre (si cambió)
                if ($nombreAnterior !== $nombreEnviado) {
                    $conexion->prepare("UPDATE rol SET nombre = :nombre WHERE id = :id")
                             ->execute([":nombre" => $nombreEnviado, ":id" => $idRol]);
                }

                // Reemplazar los permisos del rol (tabla rol_permiso)
                $clavesDespues = guardarPermisosRol($conexion, $idRol, $marcadosEnviados, $clavesAsignables);

                $antesOrdenadas = $clavesAntes;
                $despuesOrdenadas = $clavesDespues;
                sort($antesOrdenadas);
                sort($despuesOrdenadas);

                if ($nombreAnterior === $nombreEnviado && $antesOrdenadas === $despuesOrdenadas) {
                    $conexion->rollBack();
                    $error = "No hay cambios para guardar.";

                } else {
                    registrarAuditoria(
                        $conexion,
                        $_SESSION["id_usuario"],
                        "EDITAR",
                        "Rol",
                        $idRol,
                        "Se modificó el rol " . $nombreAnterior
                            . ($nombreAnterior !== $nombreEnviado ? " (ahora " . $nombreEnviado . ")" : ""),
                        "Nombre: " . $nombreAnterior . " | Permisos: " . ($clavesAntes ? implode(", ", $clavesAntes) : "(ninguno)"),
                        "Nombre: " . $nombreEnviado . " | Permisos: " . ($clavesDespues ? implode(", ", $clavesDespues) : "(ninguno)")
                    );

                    $conexion->commit();
                    $mensaje = "Rol actualizado correctamente.";

                    // Refrescar los datos para mostrar lo guardado
                    $roles = cargarRoles($conexion);
                    $rolElegido["nombre"] = $nombreEnviado;
                }
            }

        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            if (isset($e->errorInfo[0]) && $e->errorInfo[0] === "23000") {
                $error = "Rol ya existente en la base de datos, verifique.";
            } else {
                error_log("Error al modificar el rol: " . $e->getMessage());
                $error = "Error al modificar el rol.";
            }
        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("Error al modificar el rol: " . $e->getMessage());
            $error = "Error al modificar el rol.";
        }
    }
}

// ---------- Datos que se muestran en el formulario ----------
$esRolAdmin = false;
$esRolPropio = false;
$nombreForm = "";
$idsForm = [];

if ($rolElegido !== null) {
    $esRolAdmin = rolEsAdministrador($conexion, (int)$rolElegido["id"]);
    $esRolPropio = !$esAdminActual && (int)$rolElegido["id"] === (int)$idRolActual;

    if ($enviado && $error !== "" && $mensaje === "") {
        // Si hubo un error, se conserva lo que el usuario había escrito/marcado
        $nombreForm = $nombreEnviado;
        $idsForm = $marcadosEnviados;
    } else {
        $nombreForm = $rolElegido["nombre"];
        try {
            $idsForm = $esRolAdmin
                ? array_map(fn($p) => (int)$p["id"], $catalogo)   // el administrador tiene todo
                : idsPermisosDelRol($conexion, (int)$rolElegido["id"]);
        } catch (PDOException $e) {
            error_log("No se pudieron leer los permisos del rol: " . $e->getMessage());
            $idsForm = [];
        }
    }
}
$soloLectura = $esRolAdmin || $esRolPropio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar rol</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="fondo-sistema.css?v=1">
    <link rel="stylesheet" href="tema-don-diego.css?v=2">
    <link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
</head>
<body>
    <?php require_once "menu.php"; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-7">
                <div class="card shadow">

                    <!-- Encabezado -->
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Editar rol</h4>
                    </div>
                    <div class="card-body">

                        <!-- Mensaje de éxito -->
                        <?php if ($mensaje !== ""): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill"></i>
                                <?= htmlspecialchars($mensaje) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Mensaje de error -->
                        <?php if ($error !== ""): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Información -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i>
                            Seleccione el rol que desea modificar. Podrá cambiar su nombre y las funciones que habilita.
                        </div>

                        <?php if (count($roles) > 0): ?>

                            <!-- Elegir el rol -->
                            <form method="GET" class="row g-2 align-items-end mb-4">
                                <div class="col-sm-8">
                                    <label for="id_sel" class="form-label"><i class="bi bi-person-badge-fill"></i> Seleccionar rol</label>
                                    <select name="id" id="id_sel" class="form-select" required>
                                        <option value="">Seleccionar rol</option>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?= (int)$rol["id"] ?>"
                                                <?= ($rolElegido !== null && (int)$rolElegido["id"] === (int)$rol["id"]) ? "selected" : "" ?>>
                                                <?= htmlspecialchars($rol["nombre"]) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <button type="submit" class="btn btn-dark w-100">Cargar rol</button>
                                </div>
                            </form>

                            <?php if ($rolElegido !== null && $catalogo !== []): ?>

                                <?php if ($esRolAdmin): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-lock-fill"></i>
                                        El rol <strong>Administrador</strong> siempre tiene todas las funciones y no se puede modificar.
                                    </div>
                                <?php elseif ($esRolPropio): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-lock-fill"></i>
                                        No podés modificar tu propio rol.
                                    </div>
                                <?php endif; ?>

                                <form method="POST">
<?= csrfCampo() ?>
                                    <input type="hidden" name="id" value="<?= (int)$rolElegido["id"] ?>">

                                    <!-- Nombre -->
                                    <div class="mb-4">
                                        <label for="nombre" class="form-label"><i class="bi bi-pencil-fill"></i> Nombre del rol</label>
                                        <input type="text" id="nombre" name="nombre" class="form-control" maxlength="20"
                                            value="<?= htmlspecialchars($nombreForm) ?>"
                                            <?= $soloLectura ? "readonly" : "" ?> required>
                                    </div>

                                    <!-- Funciones que habilita el rol -->
                                    <h6 class="fw-bold mb-3"><i class="bi bi-shield-check"></i> Funciones habilitadas</h6>
                                    <?= permisosCheckboxes($catalogo, $idsForm, $soloLectura, $clavesAsignables) ?>

                                    <?php if (!$soloLectura): ?>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="bi bi-save-fill"></i> Guardar cambios
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </form>

                            <?php endif; ?>

                        <?php else: ?>
                            <div class="alert alert-secondary text-center">
                                <i class="bi bi-people-fill"></i> No hay roles registrados.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>
