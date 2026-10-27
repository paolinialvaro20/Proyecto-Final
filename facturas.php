<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT factura.id, factura.numero_serie, factura.monto, factura.fecha_registro, factura.fecha_cobro, factura.estado, usuario.usuario AS usuario 
        FROM factura INNER JOIN usuario ON factura.id_usuario_registro = usuario.id";

$filtros = [
    "numero_factura" => trim($_GET["numero_factura"] ?? ""),
    "numero_serie" => trim($_GET["numero_serie"] ?? ""),
    "estado" => trim($_GET["estado"] ?? ""),
    "fecha_registro_desde" => trim($_GET["fecha_registro_desde"] ?? ""),
    "fecha_registro_hasta" => trim($_GET["fecha_registro_hasta"] ?? ""),
    "fecha_cobro_desde" => trim($_GET["fecha_cobro_desde"] ?? ""),
    "fecha_cobro_hasta" => trim($_GET["fecha_cobro_hasta"] ?? ""),
    "id_usuario" => trim($_GET["id_usuario"] ?? ""),
];

$errores = [];
$condiciones = [];
$parametros = [];
$estadosPermitidos = ["Cobrado", "Pendiente", "Anulada"];
$validarFecha = static function (string $fecha): bool {
    $fechaValidada = DateTime::createFromFormat("!Y-m-d", $fecha);
    return $fechaValidada !== false && $fechaValidada->format("Y-m-d") === $fecha;
};

if ($filtros["numero_factura"] !== "") {
    if (ctype_digit($filtros["numero_factura"])) {
        $condiciones[] = "factura.id = ?";
        $parametros[] = (int)$filtros["numero_factura"];
    } else {
        $errores[] = "El número de factura debe ser un número entero.";
    }
}

if ($filtros["numero_serie"] !== "") {
    $condiciones[] = "factura.numero_serie LIKE ?";
    $parametros[] = "%" . $filtros["numero_serie"] . "%";
}

if ($filtros["estado"] !== "") {
    if (in_array($filtros["estado"], $estadosPermitidos, true)) {
        $condiciones[] = "factura.estado = ?";
        $parametros[] = $filtros["estado"];
    } else {
        $errores[] = "El estado seleccionado no es válido.";
    }
}

foreach ([
    "fecha_registro" => "Fecha de registro",
    "fecha_cobro" => "Fecha de cobro",
] as $campoFecha => $etiquetaFecha) {
    $desde = $filtros[$campoFecha . "_desde"];
    $hasta = $filtros[$campoFecha . "_hasta"];

    foreach (["desde" => $desde, "hasta" => $hasta] as $limite => $fecha) {
        if ($fecha !== "" && !$validarFecha($fecha)) {
            $errores[] = "La fecha " . strtolower($limite) . " de " . $etiquetaFecha . " no es válida.";
        }
    }

    if ($desde !== "" && $hasta !== "" && $validarFecha($desde) && $validarFecha($hasta) && $desde > $hasta) {
        $errores[] = "El rango de " . strtolower($etiquetaFecha) . " es inválido: la fecha desde no puede ser posterior a la fecha hasta.";
    }

    if ($desde !== "" && $validarFecha($desde)) {
        $condiciones[] = "factura." . $campoFecha . " >= ?";
        $parametros[] = $desde . " 00:00:00";
    }
    if ($hasta !== "" && $validarFecha($hasta)) {
        $condiciones[] = "factura." . $campoFecha . " < DATE_ADD(?, INTERVAL 1 DAY)";
        $parametros[] = $hasta;
    }
}

if ($filtros["id_usuario"] !== "") {
    if (ctype_digit($filtros["id_usuario"])) {
        $condiciones[] = "factura.id_usuario_registro = ?";
        $parametros[] = (int)$filtros["id_usuario"];
    } else {
        $errores[] = "El usuario seleccionado no es válido.";
    }
}

$usuariosStmt = $conexion->query(
    "SELECT DISTINCT usuario.id, usuario.usuario
     FROM usuario
     INNER JOIN factura ON factura.id_usuario_registro = usuario.id
     ORDER BY usuario.usuario"
);
$usuarios = $usuariosStmt->fetchAll(PDO::FETCH_ASSOC);
$facturas = [];

if (!$errores) {
    if ($condiciones) {
        $sql .= " WHERE " . implode(" AND ", $condiciones);
    }
    $sql .= " ORDER BY factura.fecha_registro DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturas</title>
    <link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">
    <link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <?php require_once "menu.php"; ?>

    <div class="container mt-5">

        <!-- =====================================================
             CARD - FACTURAS REGISTRADAS
             ===================================================== -->
        <div class="card shadow mb-5">

            <!-- Encabezado -->
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0"><i class="bi bi-list-ul"></i> Listado de facturas</h4>
            </div>

            <div class="card-body">

                <form method="get" class="border rounded bg-light p-3 mb-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="numero_factura" class="form-label">Número de factura (ID)</label>
                            <input type="search" class="form-control" id="numero_factura" name="numero_factura"
                                value="<?= htmlspecialchars($filtros["numero_factura"]) ?>" placeholder="Ej.: 125" inputmode="numeric">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="numero_serie" class="form-label">Número de serie</label>
                            <input type="search" class="form-control" id="numero_serie" name="numero_serie"
                                value="<?= htmlspecialchars($filtros["numero_serie"]) ?>" placeholder="Buscar serie">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Todos los estados</option>
                                <?php foreach ($estadosPermitidos as $estado): ?>
                                    <option value="<?= htmlspecialchars($estado) ?>" <?= $filtros["estado"] === $estado ? "selected" : "" ?>>
                                        <?= htmlspecialchars($estado) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="id_usuario" class="form-label">Usuario que registró</label>
                            <select class="form-select" id="id_usuario" name="id_usuario">
                                <option value="">Todos los usuarios</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= htmlspecialchars((string)$usuario["id"]) ?>"
                                        <?= $filtros["id_usuario"] === (string)$usuario["id"] ? "selected" : "" ?>>
                                        <?= htmlspecialchars($usuario["usuario"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-lg-6">
                            <span class="form-label d-block">Fecha de registro</span>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="fecha_registro_desde" class="form-label small text-muted">Desde</label>
                                    <input type="date" class="form-control" id="fecha_registro_desde" name="fecha_registro_desde"
                                        value="<?= htmlspecialchars($filtros["fecha_registro_desde"]) ?>">
                                </div>
                                <div class="col-6">
                                    <label for="fecha_registro_hasta" class="form-label small text-muted">Hasta</label>
                                    <input type="date" class="form-control" id="fecha_registro_hasta" name="fecha_registro_hasta"
                                        value="<?= htmlspecialchars($filtros["fecha_registro_hasta"]) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <span class="form-label d-block">Fecha de cobro</span>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="fecha_cobro_desde" class="form-label small text-muted">Desde</label>
                                    <input type="date" class="form-control" id="fecha_cobro_desde" name="fecha_cobro_desde"
                                        value="<?= htmlspecialchars($filtros["fecha_cobro_desde"]) ?>">
                                </div>
                                <div class="col-6">
                                    <label for="fecha_cobro_hasta" class="form-label small text-muted">Hasta</label>
                                    <input type="date" class="form-control" id="fecha_cobro_hasta" name="fecha_cobro_hasta"
                                        value="<?= htmlspecialchars($filtros["fecha_cobro_hasta"]) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                            <a href="facturas.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpiar filtros
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar facturas
                            </button>
                        </div>
                    </div>
                </form>

                <?php if ($errores): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>No se pudieron aplicar los filtros:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errores as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!$errores): ?>
                    <p class="text-muted">
                        <?= count($facturas) ?> factura<?= count($facturas) === 1 ? "" : "s" ?> encontrada<?= count($facturas) === 1 ? "" : "s" ?>.
                    </p>
                <?php endif; ?>

                <?php if (count($facturas) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Número de serie</th>
                                    <th>Monto</th>
                                    <th>Fecha de registro</th>
                                    <th>Fecha de cobro</th>
                                    <th>Estado</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($facturas as $factura): ?>
                                    <tr>
                                        <!-- ID -->
                                        <td class="text-center"><?= htmlspecialchars($factura["id"]) ?></td>

                                        <!-- Número de serie -->
                                        <td><?= htmlspecialchars($factura["numero_serie"]) ?></td>

                                        <!-- Monto -->
                                        <td class="text-end">$<?= number_format($factura["monto"], 2, ',', '.') ?></td>

                                        <!-- Fecha de registro -->
                                        <td><?= htmlspecialchars($factura["fecha_registro"]) ?></td>

                                        <!-- Fecha de cobro -->
                                        <td>
                                            <?php if ($factura["fecha_cobro"] !== null): ?>
                                                <?= htmlspecialchars($factura["fecha_cobro"]) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin cobrar</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Estado -->
                                        <td class="text-center">
                                            <?php if ($factura["estado"] == "Cobrado"): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Cobrado</span>

                                            <?php elseif ($factura["estado"] == "Pendiente"): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock-fill"></i> Pendiente</span>

                                            <?php elseif ($factura["estado"] == "Anulada"): ?>
                                                <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Anulada</span>

                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($factura["estado"]) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Usuario -->
                                        <td>
                                            <?= htmlspecialchars($factura["usuario"]) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>

                <?php elseif (!$errores): ?>
                    <div class="alert alert-secondary text-center mb-0">
                        <i class="bi bi-receipt"></i>
                        <?php if (array_filter($filtros, static fn($valor) => $valor !== "")): ?>
                            No se encontraron facturas con los filtros seleccionados.
                        <?php else: ?>
                            No hay facturas registradas.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    
    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>