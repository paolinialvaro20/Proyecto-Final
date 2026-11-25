<?php
require_once __DIR__ . "/seguridad.php";
require_once "conexion.php";
require_once "permisos.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}
exigirPermiso($conexion, $_SESSION["id_usuario"], "roles.listar");

// Obtener los roles registrados
$sql = "SELECT  id, nombre FROM rol ORDER BY id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Funciones habilitadas de cada rol (tabla rol_permiso + permiso)
$funcionesPorRol = [];
try {
    $stmtFunc = $conexion->query(
        "SELECT rp.id_rol, p.nombre
         FROM rol_permiso rp
         INNER JOIN permiso p ON p.id = rp.id_permiso
         ORDER BY p.id"
    );
    foreach ($stmtFunc->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $funcionesPorRol[(int)$fila["id_rol"]][] = $fila["nombre"];
    }
} catch (PDOException $e) {
    error_log("No se pudieron leer los permisos de los roles: " . $e->getMessage());
}
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
    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="fondo-sistema.css?v=1">
    <link rel="stylesheet" href="tema-don-diego.css?v=2">
<link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
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
                                    <th>Funciones habilitadas</th>
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

                                        <!-- Funciones habilitadas -->
                                        <td>
                                            <?php if (rolEsAdministrador($conexion, (int)$rol["id"])): ?>
                                                <span class="badge text-bg-dark">Todas</span>
                                            <?php elseif (!empty($funcionesPorRol[(int)$rol["id"]])): ?>
                                                <?php foreach ($funcionesPorRol[(int)$rol["id"]] as $funcion): ?>
                                                    <span class="badge text-bg-secondary mb-1"><?= htmlspecialchars($funcion) ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">Ninguna</span>
                                            <?php endif; ?>
                                        </td>
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
