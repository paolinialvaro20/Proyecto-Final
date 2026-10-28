<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// Filtros del listado (solo consultas, no modifica registros)
$buscar = trim((string)($_GET["buscar"] ?? ""));
$rolFiltro = trim((string)($_GET["rol"] ?? ""));
$estadoFiltro = (string)($_GET["estado"] ?? "");
if (!in_array($estadoFiltro, ["", "1", "0"], true)) {
    $estadoFiltro = "";
}

// Roles disponibles para el selector
$roles = $conexion->query("SELECT id, nombre FROM rol ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Consultar usuarios con parámetros seguros
$sql = "SELECT usuario.id, usuario.usuario, rol.nombre AS rol, usuario.activo
        FROM usuario INNER JOIN rol ON usuario.id_rol = rol.id WHERE 1=1";
$parametros = [];
if ($buscar !== "") {
    $sql .= " AND (usuario.usuario LIKE :buscar OR CAST(usuario.id AS CHAR) LIKE :buscar_id)";
    $parametros[":buscar"] = "%" . $buscar . "%";
    $parametros[":buscar_id"] = "%" . $buscar . "%";
}
if ($rolFiltro !== "" && ctype_digit($rolFiltro)) {
    $sql .= " AND usuario.id_rol = :rol";
    $parametros[":rol"] = (int)$rolFiltro;
}
if ($estadoFiltro !== "") {
    $sql .= " AND usuario.activo = :estado";
    $parametros[":estado"] = (int)$estadoFiltro;
}
$sql .= " ORDER BY usuario.id DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute($parametros);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hayFiltros = $buscar !== "" || $rolFiltro !== "" || $estadoFiltro !== "";
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

                <!-- Filtros de búsqueda -->
                <form method="get" action="listar_usuario.php" class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label for="buscar" class="form-label">Buscar usuario o ID</label>
                        <input type="search" class="form-control" id="buscar" name="buscar"
                               value="<?= htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Nombre de usuario o ID">
                    </div>
                    <div class="col-md-4">
                        <label for="rol" class="form-label">Rol</label>
                        <select name="rol" id="rol" class="form-select">
                            <option value="">Todos los roles</option>
                            <?php foreach ($roles as $opcionRol): ?>
                                <option value="<?= (int)$opcionRol['id'] ?>"
                                    <?= $rolFiltro === (string)$opcionRol['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($opcionRol['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" <?= $estadoFiltro === '1' ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= $estadoFiltro === '0' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar usuarios
                        </button>
                        <a href="listar_usuario.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar filtros
                        </a>
                    </div>
                </form>

                <?php if (count($usuarios) > 0): ?>
                    <p class="text-muted small"><?= count($usuarios) ?> usuario(s) encontrado(s)</p>
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
                        <?= $hayFiltros ? 'No se encontraron usuarios con esos filtros.' : 'No hay usuarios registrados.' ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

      
    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>
