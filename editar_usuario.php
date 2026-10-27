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

// ID del usuario que se quiere editar
// Se utiliza internamente, no es editable.
$id_usuario_editar = $_GET["id"] ?? $_POST["id_usuario"] ?? "";

// =====================================================
// OBTENER USUARIOS
// =====================================================
$sqlUsuarios = "SELECT id, usuario FROM usuario WHERE activo = 1 ORDER BY usuario";
$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// =====================================================
// OBTENER ROLES DESDE LA TABLA rol
// =====================================================
$sqlRoles = "SELECT id, nombre FROM rol ORDER BY nombre";
$stmtRoles = $conexion->prepare($sqlRoles);
$stmtRoles->execute();
$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

// =====================================================
// PROCESAR FORMULARIO
// =====================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST["id_usuario"] ?? "";
    $usuario = trim($_POST["usuario"] ?? "");
    $contrasenia = $_POST["contrasenia"] ?? "";
    $activo = isset($_POST["activo"]) ? 1 : 0;
    $id_ROL = $_POST["id_ROL"] ?? "";

    // Verificar campos obligatorios
    if ($id_usuario == "" || $usuario == "" || $id_ROL == "") {
        $mensaje = "Debe completar todos los campos obligatorios.";
    } else {
        // =================================================
        // VERIFICAR QUE EL USUARIO NO ESTÉ REPETIDO
        // =================================================
        $sqlVerificar = "SELECT id FROM usuario WHERE usuario = ? AND id != ?";
        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->execute([$usuario, $id_usuario]);
        $usuarioExistente = $stmtVerificar->fetch(PDO::FETCH_ASSOC);

        if ($usuarioExistente) {
            $mensaje = "Ya existe otro usuario con ese nombre.";
        } else {
            // =================================================
            // VERIFICAR QUE EL ROL EXISTA
            // =================================================
            $sqlVerificarRol = "SELECT id FROM rol WHERE id = ?";
            $stmtVerificarRol = $conexion->prepare($sqlVerificarRol);
            $stmtVerificarRol->execute([$id_ROL]);
            $rolExiste = $stmtVerificarRol->fetch(PDO::FETCH_ASSOC);

            if (!$rolExiste) {
                $mensaje = "El rol seleccionado no existe.";
            } else {
                // =================================================
                // ACTUALIZAR USUARIO
                // =================================================
                /*
                 * Si se escribe una nueva contraseña,
                 * se actualiza.
                 *
                 * Si se deja vacía, se conserva
                 * la contraseña actual.
                 */
                if ($contrasenia != "") {
                    $contraseniaHash = password_hash($contrasenia, PASSWORD_DEFAULT);
                    $sql = "UPDATE usuario SET usuario = ?, contrasenia = ?, activo = ?, id_ROL = ? WHERE id = ?";
                    $stmt = $conexion->prepare($sql);
                    $resultado = $stmt->execute([$usuario, $contraseniaHash, $activo, $id_ROL, $id_usuario]);
                } else {
                    $sql = "UPDATE usuario SET usuario = ?, activo = ?, id_ROL = ? WHERE id = ?";
                    $stmt = $conexion->prepare($sql);
                    $resultado = $stmt->execute([$usuario, $activo, $id_ROL, $id_usuario]);
                }

                if ($resultado) {
                    $mensaje = "Usuario actualizado correctamente.";
                    $id_usuario_editar = $id_usuario;
                } else {
                    $mensaje = "Error al actualizar el usuario.";
                }
            }
        }
    }
}


// =====================================================
// OBTENER DATOS DEL USUARIO SELECCIONADO
// =====================================================
$usuarioEditar = null;

if ($id_usuario_editar != "") {
    $sqlUsuario = "SELECT id, usuario, activo, id_ROL FROM usuario WHERE id = ?";
    $stmtUsuario = $conexion->prepare($sqlUsuario);
    $stmtUsuario->execute([$id_usuario_editar]);
    $usuarioEditar = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <?php require_once "menu.php"; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">

                <!-- Seleccionar usuario -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-person-gear"></i> Editar usuario</h4>
                    </div>

                    <div class="card-body">
                        
                        <!-- Información -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i>
                            Seleccione el usuario cuyos datos desea modificar.
                        </div>

                        <form method="get">
                            <label for="id" class="form-label"><i class="bi bi-person-fill"></i> Usuario a editar</label>
                            <select id="id" name="id" class="form-select" onchange="this.form.submit()" required>
                                <option value="">Seleccione un usuario</option>

                                <?php foreach ($usuarios as $usuario): ?>
                                    <option
                                        value="<?= $usuario["id"] ?>"
                                        <?= ($id_usuario_editar == $usuario["id"])
                                            ? "selected"
                                            : ""
                                        ?>
                                    >
                                        <?= htmlspecialchars($usuario["usuario"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
                
                <!-- Mensaje -->
                <?php if ($mensaje != ""): ?>
                    <div class="alert 
                            <?= $mensaje === "Usuario actualizado correctamente."
                                ? "alert-success"
                                : "alert-danger"
                            ?>
                            alert-dismissible fade show"
                            role="alert">

                            <i class="bi
                                <?= $mensaje === "Usuario actualizado correctamente."
                                    ? "bi-check-circle-fill"
                                    : "bi-exclamation-triangle-fill"
                                ?>"></i>
                            <?= htmlspecialchars($mensaje) ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                <?php if ($usuarioEditar): ?>
                    <!-- Formulario de edición -->
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Datos del usuario</h5>
                        </div>
                        
                        <div class="card-body">
                            <form method="post">

                                <!-- ID oculto -->
                                <input type="hidden" name="id_usuario" value="<?= $usuarioEditar["id"] ?>">

                                <!-- Usuario -->
                                <div class="mb-3">

                                    <label for="usuario" class="form-label"><i class="bi bi-person-fill"></i> Usuario</label>
                                    <input type="text" id="usuario" name="usuario" class="form-control"
                                        value="<?= htmlspecialchars($usuarioEditar["usuario"]) ?>" maxlength="32" required>
                                </div>

                                <!-- Contraseña -->
                                <div class="mb-3">

                                    <label for="contrasenia" class="form-label"><i class="bi bi-lock-fill"></i> Nueva contraseña</label>
                                    <input type="password" id="contrasenia" name="contrasenia" class="form-control" maxlength="255" 
                                    placeholder="Ingrese una nueva contraseña">
                                    <div class="form-text">Deje vacío este campo para conservar la contraseña actual.</div>
                                </div>


                                <!-- Rol -->
                                <div class="mb-3">
                                    <label for="id_ROL" class="form-label"><i class="bi bi-person-badge-fill"></i> Rol</label>
                                    <select id="id_ROL" name="id_ROL" class="form-select" required>
                                        <option value="">Seleccione un rol</option>

                                        <?php foreach ($roles as $rol): ?>
                                            <option
                                                value="<?= $rol["id"] ?>"
                                                <?= ($usuarioEditar["id_ROL"] == $rol["id"])
                                                    ? "selected"
                                                    : ""
                                                ?>
                                            >
                                                <?= htmlspecialchars($rol["nombre"]) ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>

                                <!-- Estado -->
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1"
                                            <?= ($usuarioEditar["activo"] == 1)
                                                ? "checked"
                                                : ""
                                            ?>
                                        >
                                        <label class="form-check-label" for="activo">Usuario activo</label>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Guardar cambios</button>
                                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle-fill"></i> Cancelar</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    
    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>