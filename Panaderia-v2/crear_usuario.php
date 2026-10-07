<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// Obtener los roles disponibles
$sqlRoles = "SELECT id, nombre FROM rol ORDER BY nombre";

$stmtRoles = $conexion->prepare($sqlRoles);
$stmtRoles->execute();
$roles = $stmtRoles->fetchAll();

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $contrasenia = $_POST["contrasenia"];
    $id_rol = $_POST["id_rol"];

    // Validaciones básicas
    if ($usuario === "" || $contrasenia === "" || $id_rol === "") {
        $mensaje = "Todos los campos son obligatorios.";

    } else {
        // Comprobar si el usuario ya existe
        $sqlExiste = "SELECT id FROM usuario WHERE usuario = ?";

        $stmtExiste = $conexion->prepare($sqlExiste);
        $stmtExiste->execute([$usuario]);

        if ($stmtExiste->fetch()) {
            $mensaje = "El nombre de usuario ya existe.";

        } else {
            // Encriptar contraseña
            $contraseniaHash = password_hash($contrasenia, PASSWORD_DEFAULT);

            // Crear usuario
            $sql = "INSERT INTO usuario (usuario, contrasenia, activo, id_ROL) 
                    VALUES(?, ?, 1, ?)";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([$usuario, $contraseniaHash, $id_rol]);
            $mensaje = "Usuario creado correctamente.";

            // Vaciar valores del formulario
            $usuario = "";
        }
    }
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
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center">
                        <h3 class="mb-0"><i class="bi bi-person-plus-fill"></i> Crear usuario</h3>
                    </div>

                    <div class="card-body">

                        <?php if (isset($mensaje)): ?>
                            <div class="alert 
                                <?= $mensaje === "Usuario creado correctamente." 
                                    ? "alert-success" 
                                    : "alert-danger" 
                                ?> 
                                alert-dismissible fade show" role="alert">

                                <i class="bi 
                                    <?= $mensaje === "Usuario creado correctamente." 
                                        ? "bi-check-circle-fill" 
                                        : "bi-exclamation-triangle-fill" 
                                    ?>">
                                </i>
                                <?= htmlspecialchars($mensaje) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">

                            <!-- Usuario -->
                            <div class="mb-3">
                                <label for="usuario" class="form-label"><i class="bi bi-person-fill"></i> Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" value="<?= htmlspecialchars($usuario ?? '') 
                                ?>" placeholder="Ingrese el nombre de usuario" required>
                            </div>

                            <!-- Contraseña -->
                            <div class="mb-3">
                                <label for="contrasenia" class="form-label"><i class="bi bi-lock-fill"></i> Contraseña</label>
                                <input type="password"  class="form-control" id="contrasenia"  name="contrasenia" 
                                placeholder="Ingrese una contraseña" required>
                            </div>

                            <!-- Rol -->
                            <div class="mb-4">
                                <label for="id_rol" class="form-label"><i class="bi bi-person-badge-fill"></i> Rol</label>
                                <select id="id_rol"  name="id_rol" class="form-select" required>
                                    <option value="">Seleccione un rol</option>

                                    <?php foreach ($roles as $rol): ?>
                                        <option
                                            value="<?= htmlspecialchars($rol["id"]) ?>"
                                            <?= (isset($_POST["id_rol"]) && $_POST["id_rol"] == $rol["id"])
                                                ? "selected" : ""
                                            ?>
                                        >
                                            <?= htmlspecialchars($rol["nombre"]) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <!-- Botón -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Crear usuario</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>