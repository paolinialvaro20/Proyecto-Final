<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// Filtros del listado de pagos
$serie = trim((string)($_GET['serie'] ?? ''));
$repartidor = trim((string)($_GET['repartidor'] ?? ''));
$forma = (string)($_GET['forma_pago'] ?? '');
$liquidado = (string)($_GET['liquidado'] ?? '');
$fecha_desde = (string)($_GET['fecha_desde'] ?? '');
$fecha_hasta = (string)($_GET['fecha_hasta'] ?? '');
$monto_min = trim((string)($_GET['monto_min'] ?? ''));
$monto_max = trim((string)($_GET['monto_max'] ?? ''));

$formas_validas = ['Efectivo', 'Transferencia', 'Debito', 'Credito'];
if (!in_array($forma, $formas_validas, true)) $forma = '';
if (!in_array($liquidado, ['', '0', '1'], true)) $liquidado = '';
foreach (['fecha_desde', 'fecha_hasta'] as $campo) {
    $valor = $$campo;
    $fecha_obj = DateTime::createFromFormat('!Y-m-d', $valor);
    if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $valor) $$campo = '';
}

$sql = "SELECT p.id, p.id_factura, p.id_repartidor, p.monto, p.forma_pago,
               p.fecha_hora, p.liquidado, f.numero_serie, u.usuario AS nombre_repartidor
        FROM pago p
        INNER JOIN factura f ON p.id_factura = f.id
        INNER JOIN usuario u ON p.id_repartidor = u.id
        WHERE 1=1";
$params = [];
if ($serie !== '') {
    $sql .= ' AND f.numero_serie LIKE :serie';
    $params[':serie'] = '%' . $serie . '%';
}
if ($repartidor !== '') {
    $sql .= ' AND u.usuario LIKE :repartidor';
    $params[':repartidor'] = '%' . $repartidor . '%';
}
if ($forma !== '') {
    $sql .= ' AND p.forma_pago = :forma';
    $params[':forma'] = $forma;
}
if ($liquidado !== '') {
    $sql .= ' AND p.liquidado = :liquidado';
    $params[':liquidado'] = (int)$liquidado;
}
if ($fecha_desde !== '') {
    $sql .= ' AND p.fecha_hora >= :fecha_desde';
    $params[':fecha_desde'] = $fecha_desde . ' 00:00:00';
}
if ($fecha_hasta !== '') {
    $sql .= ' AND p.fecha_hora < DATE_ADD(:fecha_hasta, INTERVAL 1 DAY)';
    $params[':fecha_hasta'] = $fecha_hasta;
}
if ($monto_min !== '' && is_numeric($monto_min) && (float)$monto_min >= 0) {
    $sql .= ' AND p.monto >= :monto_min';
    $params[':monto_min'] = (float)$monto_min;
}
if ($monto_max !== '' && is_numeric($monto_max) && (float)$monto_max >= 0) {
    $sql .= ' AND p.monto <= :monto_max';
    $params[':monto_max'] = (float)$monto_max;
}
$sql .= ' ORDER BY p.fecha_hora DESC';
$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$hay_filtros = count($_GET) > 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar pagos</title>
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

        <!-- Botón para volver -->
        <div class="mt-4 mb-2">
            <a href="index.php" class="btn btn-secondary"><i class="bi bi-receipt"></i> Volver al listado de facturas</a>
        </div>
        <div class="card shadow mb-5">

            <!-- Encabezado -->
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0"><i class="bi bi-cash-stack"></i> Listado de pagos registrados</h4>
            </div>
            <div class="card-body">
                <form method="get" action="listar_pagos.php" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label" for="serie">Número de serie de factura</label>
                        <input class="form-control" type="search" name="serie" id="serie" placeholder="Buscar factura" value="<?= htmlspecialchars($serie, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="repartidor">Repartidor</label>
                        <input class="form-control" type="search" name="repartidor" id="repartidor" placeholder="Nombre del repartidor" value="<?= htmlspecialchars($repartidor, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="forma_pago">Forma de pago</label>
                        <select class="form-select" name="forma_pago" id="forma_pago">
                            <option value="">Todas</option>
                            <?php foreach ($formas_validas as $opcion): ?>
                                <option value="<?= htmlspecialchars($opcion, ENT_QUOTES, 'UTF-8') ?>" <?= $forma === $opcion ? 'selected' : '' ?>><?= htmlspecialchars($opcion === 'Debito' ? 'Débito' : ($opcion === 'Credito' ? 'Crédito' : $opcion), ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="monto_min">Monto mínimo</label>
                        <input class="form-control" type="number" min="0" step="0.01" name="monto_min" id="monto_min" value="<?= htmlspecialchars($monto_min, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="monto_max">Monto máximo</label>
                        <input class="form-control" type="number" min="0" step="0.01" name="monto_max" id="monto_max" value="<?= htmlspecialchars($monto_max, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="liquidado">Liquidación</label>
                        <select class="form-select" name="liquidado" id="liquidado">
                            <option value="">Todos</option>
                            <option value="1" <?= $liquidado === '1' ? 'selected' : '' ?>>Liquidado</option>
                            <option value="0" <?= $liquidado === '0' ? 'selected' : '' ?>>Pendiente</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="fecha_desde">Fecha desde</label>
                        <input class="form-control" type="date" name="fecha_desde" id="fecha_desde" value="<?= htmlspecialchars($fecha_desde, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="fecha_hasta">Fecha hasta</label>
                        <input class="form-control" type="date" name="fecha_hasta" id="fecha_hasta" value="<?= htmlspecialchars($fecha_hasta, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar pagos</button>
                        <a href="listar_pagos.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Limpiar filtros</a>
                    </div>
                </form>
                <?php if (count($pagos) > 0): ?>
                    <p class="text-muted small"><?= count($pagos) ?> pago(s) encontrado(s)</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Factura</th>
                                    <th>Repartidor</th>
                                    <th>Monto</th>
                                    <th>Forma de pago</th>
                                    <th>Fecha y hora</th>
                                    <th>Liquidación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $pago): ?>
                                    <?php
                                    // Configurar la presentación de la forma de pago
                                    switch ($pago["forma_pago"]) {
                                        case "Efectivo":
                                            $clase = "bg-success";
                                            $icono = "bi-cash";
                                            $nombre = "Efectivo";
                                            break;
                                        case "Transferencia":
                                            $clase = "bg-primary";
                                            $icono = "bi-bank";
                                            $nombre = "Transferencia";
                                            break;
                                        case "Debito":
                                            $clase = "bg-info text-dark";
                                            $icono = "bi-credit-card";
                                            $nombre = "Débito";
                                            break;
                                        case "Credito":
                                            $clase = "bg-secondary";
                                            $icono = "bi-credit-card-2-front";
                                            $nombre = "Crédito";
                                            break;
                                        default:
                                            $clase = "bg-dark";
                                            $icono = "bi-wallet2";
                                            $nombre = $pago["forma_pago"];
                                            break;
                                    }
                                    ?>
                                    <tr>
                                        <!-- ID del pago -->
                                        <td class="text-center">
                                            <?= htmlspecialchars((string)$pago["id"]) ?>
                                        </td>

                                        <!-- Número de factura -->
                                        <td class="text-center">
                                            <i class="bi bi-receipt"></i>
                                            <?= htmlspecialchars($pago["numero_serie"]) ?>
                                        </td>

                                        <!-- Repartidor -->
                                        <td>
                                            <i class="bi bi-person-fill"></i>
                                            <?= htmlspecialchars($pago["nombre_repartidor"]) ?>
                                        </td>

                                        <!-- Monto -->
                                        <td class="text-end text-nowrap">
                                            $ <?= number_format((float)$pago["monto"], 2, ",", ".") ?>
                                        </td>

                                        <!-- Forma de pago -->
                                        <td class="text-center">
                                            <span class="badge <?= $clase ?>">
                                                <i class="bi <?= $icono ?>"></i>
                                                <?= htmlspecialchars($nombre) ?>
                                            </span>
                                        </td>

                                        <!-- Fecha y hora -->
                                        <td class="text-center text-nowrap">
                                            <i class="bi bi-calendar-event"></i>
                                            <?php
                                            $fecha = new DateTime($pago["fecha_hora"]);
                                            echo htmlspecialchars($fecha->format("d/m/Y H:i"));
                                            ?>
                                        </td>

                                        <!-- Estado de liquidación -->
                                        <td class="text-center">
                                            <?php if ((int)$pago["liquidado"] === 1): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Liquidado</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock-fill"></i> Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i>
                        <?= $hay_filtros ? 'No se encontraron pagos con esos filtros.' : 'No hay pagos registrados en el sistema.' ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    
    
    <!-- JavaScript de Bootstrap -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>