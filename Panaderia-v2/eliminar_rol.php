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

// Procesar eliminación
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["id"] ?? "";
    if ($id === "" || !is_numeric($id)) {
        $error = "Rol no válido.";

    } else {
        try {

            // Verificar que el rol exista
            $sql = "SELECT id, nombre FROM rol WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([":id" => $id]);
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$rol) {
                $error = "El rol seleccionado no existe.";

            } else {
                // Eliminar el rol
                $sql = "DELETE FROM rol WHERE id = :id";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([":id" => $id]);
                $mensaje = "Rol eliminado correctamente.";
            }

        } catch (PDOException $e) {
            // Error por clave foránea
            if ($e->getCode() === "23000") {
                $error = "No se puede eliminar este rol porque está siendo utilizado por un usuario.";
            } else {
                $error = "Error al eliminar el rol.";
            }
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
    <title>Eliminar rol</title>
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
                        <h4 class="mb-0"><i class="bi bi-trash-fill"></i> Eliminar rol</h4>
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

                        <!-- Advertencia -->
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i> 
                            Seleccione el rol que desea eliminar. 
                            Tenga en cuenta que esta acción no se puede deshacer.
                        </div>

                        <?php if (count($roles) > 0): ?>
                            <form method="POST">
                                
                            <!-- Seleccionar rol -->
                                <div class="mb-4">
                                    <label for="id" class="form-label"><i class="bi bi-person-badge-fill"></i> Seleccionar rol</label>
                                    <select name="id" id="id" class="form-select" required>
                                          <option value="">Seleccionar rol</option>

                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?= htmlspecialchars($rol["id"]) ?>">
                                                <?= htmlspecialchars($rol["nombre"]) ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>

                                <!-- Botones -->
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="submit" class="btn btn-danger px-4"
                                     onclick="return confirm('¿Está seguro de que desea eliminar este rol?');"
                                    >
                                        <i class="bi bi-trash-fill"></i> Eliminar rol
                                    </button>
                                    <a href="index.php" class="btn btn-secondary px-4"><i class="bi bi-x-circle-fill"></i> Cancelar</a>
                                </div>
                            </form>

                        <?php else: ?>
                            <div class="alert alert-secondary text-center"><i class="bi bi-people-fill"></i> No hay roles registrados.</div>
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

