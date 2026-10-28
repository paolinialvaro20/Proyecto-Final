<?php
session_start();
require_once "conexion.php";

// =====================================================
// VERIFICAR SESIÓN
// =====================================================
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// =====================================================
// VARIABLES
// =====================================================
$error = "";
$pagos = [];
$cajeros = [];
$id_cajero_seleccionado = $_GET["id_cajero"] ?? "";
$fecha_seleccionada = $_GET["fecha"] ?? date("Y-m-d");

// Totales
$total_general = 0;
$total_efectivo = 0;
$total_transferencia = 0;
$total_credito = 0;
$total_debito = 0;
$total_cheque = 0;

// =====================================================
// OBTENER CAJEROS ACTIVOS
// =====================================================
try {
    $sqlCajeros = "
        SELECT u.id, u.usuario
        FROM usuario u
        INNER JOIN rol r ON u.id_ROL = r.id
        WHERE u.activo = 1
        AND LOWER(r.nombre) IN ('cajero', 'cajera')
        ORDER BY u.usuario
    ";

    $stmtCajeros = $conexion->prepare($sqlCajeros);
    $stmtCajeros->execute();

    $cajeros = $stmtCajeros->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "No se pudieron cargar los cajeros.";
}

// =====================================================
// BUSCAR PAGOS PENDIENTES DE LIQUIDACIÓN
// =====================================================
if (
    $id_cajero_seleccionado !== ""
    && $fecha_seleccionada !== ""
    && $error === ""
) {
    try {

        // =============================================
        // VALIDAR CAJERO Y FECHA
        // =============================================
        $fechaValida = DateTime::createFromFormat(
            '!Y-m-d',
            $fecha_seleccionada
        );

        if (
            !ctype_digit((string)$id_cajero_seleccionado)
            || (int)$id_cajero_seleccionado < 1
            || !$fechaValida
            || $fechaValida->format("Y-m-d") !== $fecha_seleccionada
        ) {
            throw new Exception(
                "Seleccioná un cajero y una fecha válidos."
            );
        }

        $idsCajeros = array_map(
            "strval",
            array_column($cajeros, "id")
        );

        if (!in_array(
            (string)$id_cajero_seleccionado,
            $idsCajeros,
            true
        )) {
            throw new Exception(
                "El cajero seleccionado no está activo."
            );
        }

        // =============================================
        // OBTENER PAGOS PENDIENTES
        // =============================================
        $sqlPagos = "
            SELECT
                p.id,
                p.id_factura,
                p.id_repartidor,
                p.monto,
                p.forma_pago,
                p.fecha_hora,
                p.liquidado,
                f.numero_serie,
                u.usuario AS nombre_cajero
            FROM pago p
            INNER JOIN factura f ON p.id_factura = f.id
            INNER JOIN usuario u ON p.id_repartidor = u.id
            WHERE p.id_repartidor = :id_cajero
            AND p.fecha_hora >= :inicio
            AND p.fecha_hora < :fin
            AND p.liquidado = 0
            ORDER BY p.fecha_hora ASC, p.id ASC
        ";

        $inicio = $fecha_seleccionada . " 00:00:00";

        $fin = (new DateTimeImmutable($fecha_seleccionada))
            ->modify("+1 day")
            ->format("Y-m-d") . " 00:00:00";

        $stmtPagos = $conexion->prepare($sqlPagos);

        $stmtPagos->execute([
            ":id_cajero" => $id_cajero_seleccionado,
            ":inicio" => $inicio,
            ":fin" => $fin
        ]);

        $pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

        // =============================================
        // CALCULAR TOTALES POR FORMA DE PAGO
        // =============================================
        foreach ($pagos as $pago) {

            $monto = (float)$pago["monto"];
            $forma = trim((string)$pago["forma_pago"]);

            $total_general += $monto;

            switch ($forma) {

                case "Efectivo":
                    $total_efectivo += $monto;
                    break;

                case "Transferencia":
                    $total_transferencia += $monto;
                    break;

                case "Credito":
                    $total_credito += $monto;
                    break;

                case "Debito":
                    $total_debito += $monto;
                    break;

                case "Cheque":
                    $total_cheque += $monto;
                    break;

                default:
                    $pagos_sin_forma[] = $pago["id"];
                    break;
            }
        }

    } catch (Exception $e) {
        $pagos = [];
        $pagos_sin_forma = [];
        $error = $e->getMessage();
    } catch (PDOException $e) {
        $pagos = [];
        $pagos_sin_forma = [];
        $error = "Error al obtener los pagos del cajero.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Liquidación diaria de cajeros</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/estilo.css">
</head>

<body>

    <?php require_once "menu.php"; ?>

    <div class="container mt-5 mb-5">

        <!-- ================================================
            FORMULARIO DE BÚSQUEDA
            ================================================ -->
        <div class="card shadow mb-4">

            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0">
                    <i class="bi bi-cash-stack"></i>
                    Liquidación diaria por cajero
                </h4>
            </div>

            <div class="card-body">

                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                    </div>
                <?php endif; ?>

                <form method="GET" action="liquidacion_diaria_cajero.php">

                    <div class="row g-3 align-items-end">

                        <!-- Cajero -->
                        <div class="col-md-6">

                            <label for="id_cajero" class="form-label">
                                <i class="bi bi-person-fill"></i>
                                Cajero
                            </label>

                            <select
                                name="id_cajero"
                                id="id_cajero"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    -- Seleccionar cajero --
                                </option>

                                <?php foreach ($cajeros as $cajero): ?>
                                    <option
                                        value="<?= htmlspecialchars((string)$cajero["id"], ENT_QUOTES, "UTF-8") ?>"
                                        <?= (string)$id_cajero_seleccionado ===
                                            (string)$cajero["id"] ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($cajero["usuario"], ENT_QUOTES, "UTF-8") ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- Fecha -->
                        <div class="col-md-4">

                            <label for="fecha" class="form-label">
                                <i class="bi bi-calendar-event"></i>
                                Fecha de liquidación
                            </label>

                            <input
                                type="date"
                                name="fecha"
                                id="fecha"
                                class="form-control"
                                value="<?= htmlspecialchars($fecha_seleccionada, ENT_QUOTES, "UTF-8") ?>"
                                required
                            >

                        </div>

                        <!-- Botón buscar -->
                        <div class="col-md-2 d-grid">

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                                Buscar
                            </button>

                        </div>

                    </div>

                </form>

            </div>
        </div>

        <!-- ================================================
            RESULTADOS
            ================================================ -->
        <?php if (
            $id_cajero_seleccionado !== ""
            && $fecha_seleccionada !== ""
            && $error === ""
        ): ?>

            <div class="card shadow mb-4">

                <div class="card-header bg-dark text-white text-center">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt"></i>
                        Pagos pendientes de liquidación
                    </h5>
                </div>

                <div class="card-body">

                    <!-- Fecha y cajero -->
                    <div class="row mb-3">

                        <div class="col-md-6">
                            <strong>
                                <i class="bi bi-calendar-check"></i>
                                Fecha:
                            </strong>

                            <?= htmlspecialchars($fecha_seleccionada, ENT_QUOTES, "UTF-8") ?>
                        </div>

                        <?php if (count($pagos) > 0): ?>
                            <div class="col-md-6">

                                <strong>
                                    <i class="bi bi-person-badge"></i>
                                    Cajero:
                                </strong>

                                <?= htmlspecialchars($pagos[0]["nombre_cajero"], ENT_QUOTES, "UTF-8") ?>

                            </div>
                        <?php else: ?>

                            <?php
                            $nombreCajero = "";

                            foreach ($cajeros as $cajero) {
                                if (
                                    (string)$cajero["id"] ===
                                    (string)$id_cajero_seleccionado
                                ) {
                                    $nombreCajero = $cajero["usuario"];
                                    break;
                                }
                            }
                            ?>

                            <div class="col-md-6">
                                <strong>
                                    <i class="bi bi-person-badge"></i>
                                    Cajero:
                                </strong>

                                <?= htmlspecialchars($nombreCajero, ENT_QUOTES, "UTF-8") ?>
                            </div>

                        <?php endif; ?>

                    </div>

                    <?php if (count($pagos) > 0): ?>

                        <!-- =================================
                            TABLA DE PAGOS
                            ================================= -->
                        <div class="table-responsive">

                            <table class="table table-bordered table-hover align-middle">

                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>ID pago</th>
                                        <th>Número de factura</th>
                                        <th>Fecha y hora</th>
                                        <th>Forma de pago</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($pagos as $pago): ?>

                                        <tr>

                                            <td class="text-center">
                                                <?= htmlspecialchars((string)$pago["id"]) ?>
                                            </td>

                                            <td class="text-center fw-bold">
                                                <?= htmlspecialchars($pago["numero_serie"]) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($pago["fecha_hora"]) ?>
                                            </td>

                                            <td class="text-center">

                                                <?php
                                                // Configurar la presentación de la forma de pago
                                                $forma = trim((string)$pago["forma_pago"]);

                                                switch ($forma) {

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

                                                    case "Cheque":
                                                        $clase = "bg-warning text-dark";
                                                        $icono = "bi-file-earmark-text";
                                                        $nombre = "Cheque";
                                                        break;

                                                    default:
                                                        $clase = "bg-dark";
                                                        $icono = "bi-exclamation-circle";
                                                        $nombre = "Sin identificar";
                                                        break;
                                                }
                                                ?>

                                                <span class="badge <?= $clase ?>">
                                                    <i class="bi <?= $icono ?>"></i>
                                                    <?= htmlspecialchars($nombre) ?>
                                                </span>

                                            </td>

                                            <td class="text-end text-nowrap">
                                                $<?= number_format((float)$pago["monto"], 2, ",", ".") ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                        <!-- =================================
                            RESUMEN DE PAGOS
                            ================================= -->
                        <h5 class="mt-4 mb-3">
                            <i class="bi bi-bar-chart-fill"></i>
                            Resumen de la liquidación
                        </h5>

                        <div class="row g-3">

                            <!-- Efectivo -->
                            <div class="col">

                                <div class="card border-success h-100">
                                    <div class="card-body">

                                        <div class="text-success mb-2">
                                            <i class="bi bi-cash fs-3"></i>
                                        </div>

                                        <h6 class="text-muted">Total efectivo</h6>

                                        <h5 class="mb-0 text-success">
                                            $<?= number_format($total_efectivo, 2, ",", ".") ?>
                                        </h5>

                                    </div>
                                </div>

                            </div>

                            <!-- Transferencia -->
                            <div class="col">

                                <div class="card border-primary h-100">
                                    <div class="card-body">

                                        <div class="text-primary mb-2">
                                            <i class="bi bi-bank fs-3"></i>
                                        </div>

                                        <h6 class="text-muted">Total transferencia</h6>

                                        <h5 class="mb-0 text-primary">
                                            $<?= number_format($total_transferencia, 2, ",", ".") ?>
                                        </h5>

                                    </div>
                                </div>

                            </div>

                            <!-- Débito -->
                            <div class="col">

                                <div class="card border-info h-100">
                                    <div class="card-body">

                                        <div class="text-info mb-2">
                                            <i class="bi bi-credit-card fs-3"></i>
                                        </div>

                                        <h6 class="text-muted">Total débito</h6>

                                        <h5 class="mb-0 text-info">
                                            $<?= number_format($total_debito, 2, ",", ".") ?>
                                        </h5>

                                    </div>
                                </div>

                            </div>

                            <!-- Crédito -->
                            <div class="col">

                                <div class="card border-secondary h-100">
                                    <div class="card-body">

                                        <div class="text-secondary mb-2">
                                            <i class="bi bi-credit-card-2-front fs-3"></i>
                                        </div>

                                        <h6 class="text-muted">Total crédito</h6>

                                        <h5 class="mb-0 text-secondary">
                                            $<?= number_format($total_credito, 2, ",", ".") ?>
                                        </h5>

                                    </div>
                                </div>

                            </div>

                            <!-- Cheque -->
                            <div class="col">

                                <div class="card border-warning h-100">
                                    <div class="card-body">

                                        <div class="text-warning mb-2">
                                            <i class="bi bi-file-earmark-text fs-3"></i>
                                        </div>

                                        <h6 class="text-muted">Total cheque</h6>

                                        <h5 class="mb-0 text-dark">
                                            $<?= number_format($total_cheque, 2, ",", ".") ?>
                                        </h5>

                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- =================================
                            TOTAL GENERAL
                            ================================= -->
                        <div class="card bg-light border-dark mt-4">

                            <div class="card-body text-center">

                                <p class="text-muted mb-1">
                                    <i class="bi bi-calculator"></i>
                                    Cantidad de pagos: <?= count($pagos) ?>
                                </p>

                                <h4 class="mb-0">
                                    TOTAL A LIQUIDAR:

                                    <span class="text-success">
                                        $<?= number_format($total_general, 2, ",", ".") ?>
                                    </span>
                                </h4>

                            </div>

                        </div>

                        

                        <!-- =================================
                            CONFIRMAR LIQUIDACIÓN
                            ================================= -->
                        <form
                            method="POST"
                            action="confirmar_liquidacion_cajero.php"
                            class="mt-4 text-center"
                            onsubmit="return confirm('¿Está seguro de confirmar esta liquidación? Los pagos seleccionados quedarán liquidados.');"
                        >

                            <input
                                type="hidden"
                                name="id_cajero"
                                value="<?= htmlspecialchars($id_cajero_seleccionado) ?>"
                            >

                            <input
                                type="hidden"
                                name="fecha"
                                value="<?= htmlspecialchars($fecha_seleccionada) ?>"
                            >

                            <button
                                type="submit"
                                class="btn btn-success btn-lg"
                                <?= !empty($pagos_sin_forma) ? "disabled" : "" ?>
                            >
                                <i class="bi bi-check-circle-fill"></i>
                                Confirmar liquidación
                            </button>

                        </form>

                    <?php else: ?>

                        <div class="alert alert-secondary text-center mb-0">

                            <i class="bi bi-info-circle-fill"></i>

                            No hay pagos pendientes de liquidación para este
                            cajero en la fecha seleccionada.

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>

    </div>

    <!-- JavaScript de Bootstrap -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

</body>
</html>