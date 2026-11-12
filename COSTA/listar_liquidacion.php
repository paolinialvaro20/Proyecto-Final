<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

function esc($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

function pesos($valor) {
    return "$ " . number_format((float)$valor, 2, ",", ".");
}

$error = "";
$liquidaciones = [];

try {
    $sql = "SELECT l.id, l.fecha_hora, l.cantidad_pagos, l.total_general,
            ul.usuario AS usuario_liquidado,
            r.nombre AS rol_liquidado,
            ur.usuario AS usuario_registro
            FROM liquidacion l
            INNER JOIN usuario ul ON ul.id = l.id_usuario_liquidado
            INNER JOIN rol r ON r.id = ul.id_ROL
            INNER JOIN usuario ur ON ur.id = l.id_usuario_registro
            ORDER BY l.fecha_hora DESC, l.id DESC";

    $consulta = $conexion->query($sql);
    $liquidaciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "No se pudieron cargar las liquidaciones.";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Listar liquidaciones</title>

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

        <!-- Botón para volver -->
        <div class="mt-4 mb-2">
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-receipt"></i>
                Volver al listado de facturas
            </a>
        </div>

        <div class="card shadow mb-5">

            <!-- Encabezado -->
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0">
                    <i class="bi bi-cash-stack"></i>
                    Listado de liquidaciones registradas
                </h4>
            </div>

            <div class="card-body">

                <?php if ($error !== ""): ?>

                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= esc($error) ?>
                    </div>

                <?php elseif (count($liquidaciones) > 0): ?>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Usuario liquidado</th>
                                    <th>Registrado por</th>
                                    <th>Fecha y hora</th>
                                    <th>Cantidad de pagos</th>
                                    <th>Total general</th>
                                    <th>Comprobante</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($liquidaciones as $liquidacion): ?>

                                    <?php
                                    $rol = strtolower(trim($liquidacion["rol_liquidado"]));

                                    if ($rol === "repartidor") {
                                        $enlace = "liquidacion_confirmada.php?id_liquidacion="
                                                . urlencode($liquidacion["id"]);

                                    } elseif ($rol === "cajero" || $rol === "cajera") {
                                        $enlace = "liquidacion_confirmada_cajero.php?id_liquidacion="
                                                . urlencode($liquidacion["id"]);

                                    } else {
                                        $enlace = "";
                                    }
                                    ?>

                                    <tr>
                                        <!-- Usuario liquidado -->
                                        <td>
                                            <i class="bi bi-person-fill"></i>
                                            <strong><?= esc($liquidacion["usuario_liquidado"]) ?></strong>
                                            -
                                            <span class="badge 
                                                <?php
                                                if ($rol === "cajero" || $rol === "cajera") {
                                                    echo "bg-primary";
                                                } elseif ($rol === "repartidor") {
                                                    echo "bg-info text-dark";
                                                } else {
                                                    echo "bg-secondary";
                                                }
                                                ?>">
                                                <?= esc($liquidacion["rol_liquidado"]) ?>
                                            </span>
                                        </td>

                                        <!-- Usuario que registró -->
                                        <td>
                                            <i class="bi bi-person-check-fill"></i>
                                            <?= esc($liquidacion["usuario_registro"]) ?>
                                        </td>

                                        <!-- Fecha y hora -->
                                        <td class="text-center text-nowrap">
                                            <i class="bi bi-calendar-event"></i>
                                            <?php
                                            $fecha = new DateTime($liquidacion["fecha_hora"]);
                                            echo esc($fecha->format("d/m/Y H:i"));
                                            ?>
                                        </td>

                                        <!-- Cantidad de pagos -->
                                        <td class="text-center">
                                            <span class="badge bg-secondary">
                                                <?= (int)$liquidacion["cantidad_pagos"] ?>
                                            </span>
                                        </td>

                                        <!-- Total general -->
                                        <td class="text-end text-nowrap fw-bold">
                                            <?= pesos($liquidacion["total_general"]) ?>
                                        </td>

                                        <!-- Comprobante -->
                                        <td class="text-center">
                                            <?php if ($enlace !== ""): ?>

                                                <a href="<?= esc($enlace) ?>"
                                                   class="btn btn-success btn-sm"
                                                   title="Ver comprobante">
                                                    
                                                   <i class="bi bi-receipt"></i>
                                                    Ver
                                                </a>

                                            <?php else: ?>

                                                <span class="text-muted">
                                                    No disponible
                                                </span>

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
                        No hay liquidaciones registradas en el sistema.
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- JavaScript de Bootstrap -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

</body>
</html>
