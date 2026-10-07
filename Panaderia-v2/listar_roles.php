<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// Obtener los roles registrados
$sql = "SELECT  id, nombre FROM rol ORDER BY id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de roles</title>
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

        <!-- Card principal -->
        <div class="card shadow mb-5">

            <!-- Encabezado -->
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0"><i class="bi bi-person-badge-fill"></i> Listado de roles</h4>
            </div>

            <div class="card-body">

                <?php if (count($roles) > 0): ?>
                    <!-- Tabla responsive -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">

                            <!-- Encabezado de la tabla -->
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre del rol</th>
                                </tr>
                            </thead>

                            <!-- Datos -->
                            <tbody>
                                <?php foreach ($roles as $rol): ?>
                                    <tr>
                                        <!-- ID -->
                                        <td class="text-center"><?= htmlspecialchars($rol["id"]) ?></td>

                                        <!-- Nombre del rol -->
                                        <td><i class="bi bi-person-badge-fill"></i> <?= htmlspecialchars($rol["nombre"]) ?></td>
                                   </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>

                <?php else: ?>
                    <!-- Si no hay roles -->
                    <div class="alert alert-secondary text-center mb-0">
                        <i class="bi bi-info-circle-fill"></i>
                        No hay roles registrados.
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    
    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>
