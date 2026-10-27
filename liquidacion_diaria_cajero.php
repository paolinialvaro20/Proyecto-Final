
<?php
session_start();
require_once 'conexion.php';

// VERIFICAR SESIÓN
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// VARIABLES
$error = '';
$pagos = [];
$pagos_sin_forma = [];
$cajeros = [];

$totales = [
    'Efectivo' => 0,
    'Transferencia' => 0,
    'Credito' => 0,
    'Debito' => 0
];

$id_cajero = $_GET['id_cajero'] ?? '';
$fecha = $_GET['fecha'] ?? date('Y-m-d');

// FUNCIONES
function esc($v) {
    return htmlspecialchars(
        (string)$v,
        ENT_QUOTES,
        'UTF-8'
    );
}

function pesos($n) {
    return '$' . number_format(
        (float)$n,
        2,
        ',',
        '.'
    );
}

try {

    // OBTENER CAJEROS ACTIVOS
    $sqlCajeros = "
        SELECT u.id, u.usuario
        FROM usuario u
        INNER JOIN rol r ON r.id = u.id_ROL
        WHERE u.activo = 1
        AND LOWER(r.nombre) IN ('cajero', 'cajera')
        ORDER BY u.usuario
    ";

    $stmt = $conexion->query($sqlCajeros);
    $cajeros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($id_cajero !== '') {

        // VALIDAR CAJERO Y FECHA
        $validaFecha = DateTime::createFromFormat(
            '!Y-m-d',
            $fecha
        );

        if (
            !ctype_digit((string)$id_cajero) ||
            (int)$id_cajero < 1 ||
            !$validaFecha ||
            $validaFecha->format('Y-m-d') !== $fecha
        ) {
            throw new Exception(
                'Seleccioná un cajero y una fecha válidos.'
            );
        }

        $ids = array_column($cajeros, 'id');

        if (!in_array(
            (string)$id_cajero,
            array_map('strval', $ids),
            true
        )) {
            throw new Exception(
                'El cajero seleccionado no está activo.'
            );
        }

        // BUSCAR PAGOS PENDIENTES
        $sqlPagos = "
            SELECT
                p.id,
                f.numero_serie,
                p.fecha_hora,
                p.forma_pago,
                p.monto
            FROM pago p
            INNER JOIN factura f
                ON f.id = p.id_factura
            WHERE p.id_repartidor = :id
            AND p.fecha_hora >= :inicio
            AND p.fecha_hora < :fin
            AND p.liquidado = 0
            ORDER BY p.fecha_hora, p.id
        ";

        $inicio = $fecha . ' 00:00:00';

        $fin = (new DateTimeImmutable($fecha))
            ->modify('+1 day')
            ->format('Y-m-d') . ' 00:00:00';

        $stmt = $conexion->prepare($sqlPagos);

        $stmt->execute([
            ':id' => $id_cajero,
            ':inicio' => $inicio,
            ':fin' => $fin
        ]);

        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // CALCULAR TOTALES Y DETECTAR ERRORES
        foreach ($pagos as $p) {

            $forma = trim((string)$p['forma_pago']);

            if (array_key_exists($forma, $totales)) {

                $totales[$forma] += (float)$p['monto'];

            } else {

                $pagos_sin_forma[] = $p['id'];
            }
        }
    }

} catch (Exception $e) {

    $error = $e->getMessage();
    $pagos = [];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Liquidación diaria de cajeros</title>

    <link rel="stylesheet"
          href="css/bootstrap-5.3.8/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">

    <link rel="stylesheet"
          href="css/estilo.css">
</head>

<body>

<?php require_once 'menu.php'; ?>

<div class="container my-5">

    <!-- FORMULARIO DE BÚSQUEDA -->
    <div class="card shadow mb-4">

        <div class="card-header bg-dark text-white text-center">
            <h4 class="mb-0">
                Liquidación diaria por cajero
            </h4>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <form method="get"
                  action="liquidacion_diaria_cajero.php">

                <div class="row g-3 align-items-end">

                    <div class="col-md-6">

                        <label class="form-label">
                            Cajero
                        </label>

                        <select
                            class="form-select"
                            name="id_cajero"
                            required
                        >

                            <option value="">
                                -- Seleccionar cajero --
                            </option>

                            <?php foreach ($cajeros as $c): ?>

                                <option
                                    value="<?= esc($c['id']) ?>"
                                    <?= (string)$id_cajero ===
                                        (string)$c['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($c['usuario']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="col-md-4">

                        <label class="form-label">
                            Fecha de liquidación
                        </label>

                        <input
                            type="date"
                            name="fecha"
                            class="form-control"
                            value="<?= esc($fecha) ?>"
                            required
                        >
                    </div>

                    <div class="col-md-2 d-grid">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Buscar
                        </button>

                    </div>

                </div>
            </form>

        </div>
    </div>

    <?php if ($id_cajero !== '' && !$error): ?>

        <div class="card shadow">

            <div class="card-header bg-dark text-white text-center">
                <h5 class="mb-0">
                    Pagos pendientes de liquidación
                </h5>
            </div>

            <div class="card-body">

                <p>
                    <strong>Fecha:</strong>
                    <?= esc($fecha) ?>

                    &nbsp;

                    <strong>Cajero:</strong>

                    <?php
                    $nombreCajero = '';

                    foreach ($cajeros as $c) {
                        if (
                            (string)$c['id'] ===
                            (string)$id_cajero
                        ) {
                            $nombreCajero = $c['usuario'];
                            break;
                        }
                    }
                    ?>

                    <?= esc($nombreCajero) ?>
                </p>

                <?php if ($pagos): ?>

                    <!-- TABLA DE PAGOS -->
                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th>ID pago</th>
                                    <th>Número de factura</th>
                                    <th>Fecha y hora</th>
                                    <th>Forma de pago</th>
                                    <th class="text-end">
                                        Monto
                                    </th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($pagos as $p): ?>

                                    <?php
                                    $forma = trim(
                                        (string)$p['forma_pago']
                                    );

                                    $nombreForma = [
                                        'Credito' => 'Crédito',
                                        'Debito' => 'Débito'
                                    ][$forma] ?? $forma;

                                    if (
                                        !array_key_exists(
                                            $forma,
                                            $totales
                                        )
                                    ) {
                                        $nombreForma =
                                            'Sin identificar';
                                    }
                                    ?>

                                    <tr>

                                        <td>
                                            <?= esc($p['id']) ?>
                                        </td>

                                        <td>
                                            <?= esc($p['numero_serie']) ?>
                                        </td>

                                        <td>
                                            <?= esc($p['fecha_hora']) ?>
                                        </td>

                                        <td>
                                            <?= esc($nombreForma) ?>
                                        </td>

                                        <td class="text-end">
                                            <?= pesos($p['monto']) ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>

                    <!-- RESUMEN -->
                    <h5 class="mt-4">
                        Resumen de la liquidación
                    </h5>

                    <div class="row g-3">

                        <?php foreach ($totales as $forma => $monto): ?>

                            <div class="col-sm-6 col-lg-3">

                                <div class="card h-100">

                                    <div class="card-body">

                                        <h6 class="text-muted">
                                            <?= esc([
                                                'Credito' => 'Crédito',
                                                'Debito' => 'Débito'
                                            ][$forma] ?? $forma) ?>
                                        </h6>

                                        <h5>
                                            <?= pesos($monto) ?>
                                        </h5>

                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>

                    <!-- TOTAL GENERAL -->
                    <div class="card bg-light mt-4">

                        <div class="card-body text-center">

                            <p>
                                Cantidad de pagos:
                                <?= count($pagos) ?>
                            </p>

                            <h4>
                                TOTAL A LIQUIDAR:

                                <span class="text-success">
                                    <?= pesos(
                                        array_sum(
                                            array_map(
                                                fn($p) =>
                                                    (float)$p['monto'],
                                                $pagos
                                            )
                                        )
                                    ) ?>
                                </span>
                            </h4>

                        </div>
                    </div>

                    <!-- ADVERTENCIA -->
                    <?php if (!empty($pagos_sin_forma)): ?>

                        <div class="alert alert-danger mt-4 text-center">

                            <strong>Atención:</strong>

                            No se puede confirmar esta liquidación
                            porque hay pagos sin una forma de pago válida.

                            <p class="mt-2 mb-0">

                                Pagos a revisar:

                                <?= esc(
                                    implode(', ', $pagos_sin_forma)
                                ) ?>

                            </p>

                        </div>

                    <?php endif; ?>

                    <!-- CONFIRMAR LIQUIDACIÓN -->
                    <form
                        method="post"
                        action="confirmar_liquidacion_cajero.php"
                        class="mt-4 text-center"
                        onsubmit="return confirm('¿Confirmar la liquidación de estos pagos?');"
                    >

                        <input
                            type="hidden"
                            name="id_cajero"
                            value="<?= esc($id_cajero) ?>"
                        >

                        <input
                            type="hidden"
                            name="fecha"
                            value="<?= esc($fecha) ?>"
                        >

                        <button
                            type="submit"
                            class="btn btn-success btn-lg"
                            <?= !empty($pagos_sin_forma)
                                ? 'disabled'
                                : '' ?>
                        >
                            Confirmar liquidación
                        </button>

                    </form>

                <?php else: ?>

                    <div class="alert alert-secondary">

                        No hay pagos pendientes de liquidación
                        para este cajero en la fecha seleccionada.

                    </div>

                <?php endif; ?>

            </div>
        </div>

    <?php endif; ?>

</div>

<script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

</body>
</html>
