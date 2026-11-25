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
exigirPermiso($conexion, $_SESSION["id_usuario"], "roles.crear");

$mensaje = "";
$error = "";
$nombre = "";
$marcados = [];

// Permisos que quien crea el rol puede asignar (el administrador: todos)
$clavesAsignables = clavesAsignables($conexion, $_SESSION["id_usuario"]);

// Lista de funciones del sistema (tabla permiso)
try {
    $catalogo = catalogoPermisos($conexion);
} catch (PDOException $e) {
    error_log("No se pudo leer el catálogo de permisos: " . $e->getMessage());
    $catalogo = [];
    $error = "No se pudo cargar la lista de permisos. ¿Se ejecutó database/permisos_por_rol.sql?";
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST" && $catalogo !== []) {
    $nombre = trim($_POST["nombre"] ?? "");
    $marcados = array_values(array_filter((array)($_POST["permisos"] ?? []), "is_string"));

    if ($nombre === "") {
        $error = "Debés ingresar el nombre del rol.";

    } elseif (mb_strlen($nombre) > 20) {
        $error = "El nombre del rol no puede superar los 20 caracteres.";

    } elseif (in_array(mb_strtolower($nombre), ROLES_ADMIN, true)) {
        $error = "Ese nombre de rol está reservado.";

    } else {
        try {
            // Iniciar transacción
            $conexion->beginTransaction();

            // Crear el rol
            $stmt = $conexion->prepare("INSERT INTO rol (nombre) VALUES (:nombre)");
            $stmt->execute([":nombre" => $nombre]);
            $idRolNuevo = (int)$conexion->lastInsertId();

            // Asignar los permisos elegidos (tabla rol_permiso)
            $claves = guardarPermisosRol($conexion, $idRolNuevo, $marcados, $clavesAsignables);

            // Registrar el movimiento en auditoría
            registrarAuditoria(
                $conexion,
                $_SESSION["id_usuario"],
                "CREAR",
                "Rol",
                $idRolNuevo,
                "Se creó el rol " . $nombre,
                null,
                "Nombre: " . $nombre . " | Permisos: " . ($claves ? implode(", ", $claves) : "(ninguno)")
            );

            // Confirmar la creación, los permisos y la auditoría
            $conexion->commit();

            $mensaje = "Rol agregado correctamente.";
            $nombre = "";
            $marcados = [];

        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            // Error por clave duplicada
            if (isset($e->errorInfo[0]) && $e->errorInfo[0] === "23000") {
                $error = "Rol ya existente en la base de datos, verifique.";
            } else {
                error_log("Error al crear el rol: " . $e->getMessage());
                $error = "Error al agregar el rol.";
            }

        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log("Error al crear el rol: " . $e->getMessage());
            $error = "Error al agregar el rol.";
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
                        <h4 class="mb-0"><i class="bi bi-person-badge-fill"></i> Agregar nuevo rol</h4>
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
                            Ingrese el nombre del nuevo rol y marque las funciones que va a poder usar.
                        </div>

                        <?php if ($catalogo !== []): ?>
                        <!-- Formulario -->
                        <form method="POST">
<?= csrfCampo() ?>
                            <div class="mb-4">
                                <label for="nombre" class="form-label"><i class="bi bi-person-badge"></i> Nombre del rol</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" maxlength="20"
                                    value="<?= htmlspecialchars($nombre) ?>"
                                    placeholder="Ingrese el nombre del rol" required>
                            </div>

                            <!-- Funciones que habilita el rol -->
                            <h6 class="fw-bold mb-3"><i class="bi bi-shield-check"></i> Funciones habilitadas</h6>
                            <?= permisosCheckboxes($catalogo, $marcados, false, $clavesAsignables) ?>

                            <!-- Botón -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-plus-circle-fill"></i> Agregar rol</button>
                            </div>
                        </form>
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
