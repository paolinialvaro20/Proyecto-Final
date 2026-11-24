
<?php
require_once __DIR__ . "/seguridad.php";
require_once "conexion.php";

// Verificar sesión
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "permisos.php";
exigirPermisoAdministrativo($conexion, $_SESSION["id_usuario"]);

// Obtener filtros
$fecha_desde = trim($_GET["fecha_desde"] ?? "");
$fecha_hasta = trim($_GET["fecha_hasta"] ?? "");
$usuario = trim($_GET["usuario"] ?? "");
$accion_filtro = trim($_GET["accion"] ?? "");

// Validar fechas
function fechaValida($fecha) {
    if ($fecha === "") return true;

    $d = DateTime::createFromFormat("!Y-m-d", $fecha);
    return $d && $d->format("Y-m-d") === $fecha;
}

$error = "";

if (!fechaValida($fecha_desde) || !fechaValida($fecha_hasta)) {
    $error = "Las fechas ingresadas no son válidas.";
} elseif (
    $fecha_desde !== "" &&
    $fecha_hasta !== "" &&
    $fecha_desde > $fecha_hasta
) {
    $error = "La fecha desde no puede ser posterior a la fecha hasta.";
}

// Obtener usuarios para el selector
$sqlUsuarios = "SELECT DISTINCT u.id, u.usuario
                FROM usuario u
                INNER JOIN auditoria a ON a.id_usuario = u.id
                ORDER BY u.usuario";

$usuarios = $conexion->query($sqlUsuarios)
                     ->fetchAll(PDO::FETCH_ASSOC);

// Obtener acciones existentes
$sqlAcciones = "SELECT DISTINCT accion
                FROM auditoria
                WHERE accion IS NOT NULL
                ORDER BY accion";

$acciones = $conexion->query($sqlAcciones)
                    ->fetchAll(PDO::FETCH_COLUMN);

// Consulta principal
$sql = "SELECT a.id, u.usuario, a.accion, a.entidad,
               a.id_entidad, a.descripcion,
               a.valor_anterior, a.valor_nuevo,
               a.fecha_hora
        FROM auditoria a
        LEFT JOIN usuario u ON a.id_usuario = u.id
        WHERE 1=1";

$parametros = [];

if ($fecha_desde !== "" && $error === "") {
    $sql .= " AND a.fecha_hora >= :fecha_desde";
    $parametros[":fecha_desde"] = $fecha_desde . " 00:00:00";
}

if ($fecha_hasta !== "" && $error === "") {
    $sql .= " AND a.fecha_hora < :fecha_hasta";
    $siguienteDia = (new DateTime($fecha_hasta))
                    ->modify("+1 day")
                    ->format("Y-m-d");
    $parametros[":fecha_hasta"] = $siguienteDia . " 00:00:00";
}

if ($usuario !== "") {
    $sql .= " AND a.id_usuario = :usuario";
    $parametros[":usuario"] = $usuario;
}

if ($accion_filtro !== "") {
    $sql .= " AND a.accion = :accion";
    $parametros[":accion"] = $accion_filtro;
}

$sql .= " ORDER BY a.fecha_hora DESC, a.id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($parametros);

$registros = $error === ""
    ? $stmt->fetchAll(PDO::FETCH_ASSOC)
    : [];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría del sistema</title>

    <link rel="stylesheet"
          href="css/bootstrap-5.3.8/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">

    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="fondo-sistema.css?v=1">
    <link rel="stylesheet" href="tema-don-diego.css?v=2">
    <style>
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm;
    }

    body {
        background: white !important;
        font-size: 10px;
    }

    nav,
    .navbar,
    form,
    button,
    .btn {
        display: none !important;
    }

    .container {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    table {
        width: 100% !important;
        font-size: 9px;
    }

    td, th {
        padding: 4px !important;
        white-space: normal !important;
        overflow-wrap: anywhere;
    }

    thead {
        display: table-header-group;
    }

    tr {
        break-inside: avoid;
    }
}
</style>
</head>

<body>

<?php require_once "menu.php"; ?>

<div class="container mt-5">

    <div class="mt-4 mb-2">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Volver al inicio
        </a>
    </div>

    <div class="card shadow mb-5">

        <div class="card-header bg-dark text-white text-center">
            <h4 class="mb-0">
                <i class="bi bi-journal-text"></i>
                Registro de auditoría
            </h4>
        </div>

        <div class="card-body">

            <p class="text-muted">
                Registro de las acciones realizadas por los usuarios del sistema.
            </p>

            <!-- FILTROS -->
            <div class="card bg-light mb-4">
                <div class="card-body">

                    <h5 class="mb-3">
                        <i class="bi bi-funnel-fill"></i>
                        Filtrar registros
                    </h5>

                    <form method="GET" class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label">Fecha desde</label>
                            <input type="date"
                                   name="fecha_desde"
                                   class="form-control"
                                   value="<?= htmlspecialchars($fecha_desde) ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fecha hasta</label>
                            <input type="date"
                                   name="fecha_hasta"
                                   class="form-control"
                                   value="<?= htmlspecialchars($fecha_hasta) ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Usuario</label>
                            <select name="usuario" class="form-select">
                                <option value="">Todos los usuarios</option>

                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= (int)$u["id"] ?>"
                                        <?= $usuario === (string)$u["id"] ? "selected" : "" ?>>
                                        <?= htmlspecialchars($u["usuario"]) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Acción</label>
                            <select name="accion" class="form-select">
                                <option value="">Todas las acciones</option>

                                <?php foreach ($acciones as $a): ?>
                                    <option value="<?= htmlspecialchars($a) ?>"
                                        <?= $accion_filtro === $a ? "selected" : "" ?>>
                                        <?= htmlspecialchars($a) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="col-12 d-flex gap-2 flex-wrap">

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                                Buscar
                            </button>

                            <a href="<?= htmlspecialchars($_SERVER["SCRIPT_NAME"]) ?>"
                               class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                                Limpiar filtros
                            </a>

                        </div>

                    </form>

                </div>
            </div>

            <?php if ($error !== ""): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <div class="mb-3">
    <button type="button"
            onclick="window.print()"
            class="btn btn-danger">
        <i class="bi bi-file-earmark-pdf"></i>
        Guardar auditoría en PDF
    </button>
</div>

            <div class="d-flex justify-content-end mb-3">
                <span class="badge bg-dark">
                    Total de registros: <?= count($registros) ?>
                </span>
            </div>

            <?php if (count($registros) > 0): ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-dark text-center">
                            <tr>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Entidad</th>
                                <th>Descripción</th>
                                <th>Valor anterior</th>
                                <th>Valor nuevo</th>
                                <th>Fecha y hora</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($registros as $registro): ?>

                            <?php
                            $accion = mb_strtoupper(
                                trim($registro["accion"] ?? ""),
                                "UTF-8"
                            );

                            switch ($accion) {
                                case "CREAR":
                                case "AGREGAR":
                                case "REGISTRAR":
                                case "ACTIVAR":
                                    $claseAccion = "bg-success";
                                    break;

                                case "EDITAR":
                                case "CAMBIAR ESTADO":
                                case "CAMBIAR CONTRASEÑA":
                                    $claseAccion = "bg-primary";
                                    break;

                                case "ELIMINAR":
                                case "DESACTIVAR":
                                case "INICIO DE SESIÓN FALLIDO":
                                    $claseAccion = "bg-danger";
                                    break;

                                case "LIQUIDACION REPARTIDOR":
                                case "LIQUIDACION CAJERO":
                                case "LIQUIDAR RP":
                                case "LIQUIDAR CJ":
                                    $claseAccion = "bg-info text-dark";
                                    break;

                                case "INICIO DE SESIÓN":
                                case "CERRAR SESIÓN":
                                    $claseAccion = "bg-secondary";
                                    break;

                                default:
                                    $claseAccion = "bg-dark";
                                    break;
                            }

                            $fecha = new DateTime($registro["fecha_hora"]);
                            ?>

                            <tr>

                                <td class="text-center text-nowrap">
                                    <i class="bi bi-person-fill"></i>
                                    <?= htmlspecialchars($registro["usuario"] ?? "Usuario no disponible") ?>
                                </td>

                                <td class="text-center text-nowrap">
                                    <span class="badge <?= $claseAccion ?>">
                                        <?= htmlspecialchars($registro["accion"] ?? "") ?>
                                    </span>
                                </td>

                                <td class="text-center fw-bold text-uppercase">
                                    <?= htmlspecialchars($registro["entidad"] ?? "-") ?>
                                </td>

                                <td>
                                    <?= !empty($registro["descripcion"])
                                        ? htmlspecialchars($registro["descripcion"])
                                        : "-" ?>
                                </td>

                                <td>
                                    <?= !empty($registro["valor_anterior"])
                                        ? htmlspecialchars($registro["valor_anterior"])
                                        : "-" ?>
                                </td>

                                <td>
                                    <?= !empty($registro["valor_nuevo"])
                                        ? htmlspecialchars($registro["valor_nuevo"])
                                        : "-" ?>
                                </td>

                                <td class="text-center text-nowrap">
                                    <i class="bi bi-calendar-event"></i>
                                    <?= htmlspecialchars($fecha->format("d/m/Y H:i:s")) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php elseif ($error === ""): ?>

                <div class="alert alert-info text-center mb-0">
                    <i class="bi bi-info-circle-fill"></i>
                    No se encontraron registros con los filtros seleccionados.
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

</body>
</html>
