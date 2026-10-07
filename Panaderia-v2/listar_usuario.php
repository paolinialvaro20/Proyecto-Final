<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// Obtener los usuarios registrados
$sql = "SELECT  usuario.id, usuario.usuario, rol.nombre AS rol, usuario.activo
        FROM usuario INNER JOIN rol ON usuario.id_rol = rol.id
        ORDER BY usuario.id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de usuarios</title>
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
                <h4 class="mb-0"><i class="bi bi-people-fill"></i> Listado de usuarios</h4>
            </div>

            <div class="card-body">

                <?php if (count($usuarios) > 0): ?>
                    <!-- Tabla responsive -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">

                            <!-- Encabezado de la tabla -->
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>

                            <!-- Datos -->
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <!-- ID -->
                                        <td class="text-center"><?= htmlspecialchars($usuario["id"]) ?></td>

                                        <!-- Usuario -->
                                        <td><i class="bi bi-person-fill"></i> <?= htmlspecialchars($usuario["usuario"]) ?></td>

                                        <!-- Rol -->
                                        <td><i class="bi bi-person-badge-fill"></i> <?= htmlspecialchars($usuario["rol"]) ?></td>

                                        <!-- Estado -->
                                        <td class="text-center">
                                            <?php if ($usuario["activo"] == 1): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Activo</span>
                                            
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Inactivo</span>

                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>

                <?php else: ?>
                    <!-- Si no hay usuarios -->
                    <div class="alert alert-secondary text-center mb-0">
                        <i class="bi bi-info-circle-fill"></i>
                        No hay usuarios registrados.
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    
    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>
