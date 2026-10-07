<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$error = "";

// Procesar modificación
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"] ?? "";
    $nombre = trim($_POST["nombre"] ?? "");
    if ($id === "" || !is_numeric($id)) {
        $error = "Rol no válido.";
    } elseif ($nombre === "") {
        $error = "Debés ingresar el nombre del rol.";
    } else {

        try {
            // Verificar si ya existe otro rol con ese nombre
            $sql = "SELECT id FROM rol WHERE nombre = :nombre AND id != :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([":nombre" => $nombre, ":id" => $id]);
            $rolExistente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($rolExistente) {
                $error = "Rol ya existente en la base de datos, verifique";
            } else {
                // Actualizar el rol
                $sql = "UPDATE rol SET nombre = :nombre WHERE id = :id";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([":nombre" => $nombre, ":id" => $id]);
                $mensaje = "Rol actualizado correctamente.";
            }

        } catch (PDOException $e) {
            $error = "Error al modificar el rol.";
        }
    }
}

// Obtener todos los roles
try {
    $sql = "SELECT id, nombre FROM rol ORDER BY id";
    $stmt = $conexion->query($sql);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $roles = [];
    $error = "No se pudieron cargar los roles.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar rol</title>
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
                <div class="card shadow">

                    <!-- Encabezado -->
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0">
                            <i class="bi bi-pencil-square"></i> Editar rol</h4>
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
                            Seleccione el rol que desea modificar e
                            ingrese su nuevo nombre.
                        </div>


                        <?php if (count($roles) > 0): ?>
                            <form method="POST">

                                <!-- Seleccionar rol -->
                                <div class="mb-3">
                                    <label for="id" class="form-label><i class="bi bi-person-badge-fill"></i> Seleccionar rol</label>
                                    <select name="id" id="id" class="form-select" required>
                                        <option value="">Seleccionar rol</option>

                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?= htmlspecialchars($rol["id"]) ?>">
                                                <?= htmlspecialchars($rol["nombre"]) ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>

                                <!-- Nuevo nombre -->
                                <div class="mb-4">
                                    <label for="nombre" class="form-label">
                                        <i class="bi bi-pencil-fill"></i> Nuevo nombre
                                    </label>
                                    <input type="text" id="nombre" name="nombre" class="form-control" maxlength="100" 
                                    placeholder="Ingrese el nuevo nombre" required>
                                </div>

                                <!-- Botón -->
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-save-fill"></i>Guardar cambios
                                    </button>
                                </div>
                            </form>

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