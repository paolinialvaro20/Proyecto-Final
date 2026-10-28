
<?php
session_start();

require_once "conexion.php";

// VERIFICAR SESIÓN
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$error = "";
$movimientos = [];
$desde = trim((string)($_GET['desde'] ?? ''));
$hasta = trim((string)($_GET['hasta'] ?? ''));
$usuario = trim((string)($_GET['usuario'] ?? ''));
$accion = trim((string)($_GET['accion'] ?? ''));

// datetime-local acepta YYYY-MM-DDTHH:MM; no permitir fechas inválidas.
function validarFechaHoraAuditoria($valor) {
    if ($valor === '') return null;
    $fecha = DateTime::createFromFormat('!Y-m-d\TH:i', $valor);
    if (!$fecha || $fecha->format('Y-m-d\TH:i') !== $valor) return false;
    return $fecha->format('Y-m-d H:i:s');
}

$fechaDesde = validarFechaHoraAuditoria($desde);
$fechaHasta = validarFechaHoraAuditoria($hasta);

if ($fechaDesde === false || $fechaHasta === false) {
    $error = 'Ingresá fechas y horas válidas.';
} elseif ($fechaDesde !== null && $fechaHasta !== null && $fechaDesde > $fechaHasta) {
    $error = 'La fecha inicial no puede ser posterior a la fecha final.';
} else {
    try {
        $sql = "SELECT a.id, a.fecha_hora, u.usuario, a.accion, a.entidad,
                       a.id_entidad, a.descripcion, a.valor_anterior, a.valor_nuevo
                FROM auditoria a
                LEFT JOIN usuario u ON u.id = a.id_usuario
                WHERE 1=1";
        $params = [];
        if ($fechaDesde !== null) {
            $sql .= " AND a.fecha_hora >= :desde";
            $params[':desde'] = $fechaDesde;
        }
        if ($fechaHasta !== null) {
            // Incluir todos los segundos del minuto indicado.
            $sql .= " AND a.fecha_hora < :hasta";
            $params[':hasta'] = date('Y-m-d H:i:s', strtotime($fechaHasta . ' +1 minute'));
        }
        if ($usuario !== '') {
            $sql .= " AND u.usuario LIKE :usuario";
            $params[':usuario'] = '%' . $usuario . '%';
        }
        if ($accion !== '') {
            $sql .= " AND a.accion LIKE :accion";
            $params[':accion'] = '%' . $accion . '%';
        }
        $sql .= " ORDER BY a.fecha_hora DESC, a.id DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);
        $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'No se pudieron cargar los movimientos de auditoría.';
    }
}

// PROTEGER TEXTOS
function esc($valor) {
    return htmlspecialchars(
        (string)($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

// FORMATEAR FECHA
function fechaAuditoria($fecha) {
    if (empty($fecha)) {
        return "";
    }

    try {
        return (new DateTime($fecha))->format("d/m/Y H:i:s");
    } catch (Exception $e) {
        return $fecha;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Auditoría - Panadería Don Diego</title>

    <link rel="stylesheet"
          href="css/bootstrap-5.3.8/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">

    <link rel="stylesheet"
          href="css/estilo.css">

    <style>
        .auditoria-contenedor {
            max-width: 1400px;
            margin: 35px auto;
            padding: 0 15px;
        }

        .auditoria-tabla {
            overflow-x: auto;
            width: 100%;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .auditoria-tabla table {
            min-width: 1450px;
            margin-bottom: 0;
        }

        .auditoria-tabla th {
            background-color: #212529;
            color: white;
            padding: 14px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .auditoria-tabla td {
            padding: 12px;
            vertical-align: middle;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .columna-id {
            min-width: 65px;
        }

        .columna-fecha {
            min-width: 155px;
            white-space: nowrap !important;
        }

        .columna-usuario {
            min-width: 140px;
        }

        .columna-accion {
            min-width: 220px;
        }

        .columna-entidad {
            min-width: 100px;
        }

        .columna-descripcion {
            min-width: 240px;
        }

        .columna-valor {
            min-width: 260px;
            max-width: 380px;
            white-space: pre-wrap !important;
        }

        .etiqueta-accion {
            display: inline-block;
            background: #0d6efd;
            color: white;
            padding: 7px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            white-space: normal;
        }

        .texto-ayuda {
            font-size: 13px;
            color: #6c757d;
        }
    </style>
</head>

<body>

<?php require_once "menu.php"; ?>

<div class="auditoria-contenedor">

    <div class="card shadow">

        <div class="card-header bg-dark text-white text-center">

            <h4 class="mb-0">
                <i class="bi bi-journal-text"></i>
                Registro de Auditoría
            </h4>

        </div>

        <div class="card-body">

            <p class="text-muted">
                Historial de movimientos realizados
                en el sistema de Panadería Don Diego.
            </p>

            <h5 class="mb-3">Filtrar auditoría</h5>
            <form method="get" action="listar_auditoria.php" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label" for="desde">Fecha y hora</label>
                    <input class="form-control" type="datetime-local" id="desde" name="desde" value="<?= esc($desde) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="hasta">Fecha y hora (hasta)</label>
                    <input class="form-control" type="datetime-local" id="hasta" name="hasta" value="<?= esc($hasta) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="usuario">Usuarios</label>
                    <input class="form-control" type="search" id="usuario" name="usuario" placeholder="Buscar usuario" value="<?= esc($usuario) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="accion">Acción</label>
                    <input class="form-control" type="search" id="accion" name="accion" placeholder="Buscar acción" value="<?= esc($accion) ?>">
                </div>
                <div class="col-12 d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar movimientos</button>
                    <a class="btn btn-outline-secondary" href="listar_auditoria.php">Limpiar filtros</a>
                </div>
            </form>

            <?php if ($error): ?>

                <div class="alert alert-danger">
                    <?= esc($error) ?>
                </div>

            <?php elseif (empty($movimientos)): ?>

                <div class="alert alert-info">
                    No se encontraron movimientos con los filtros seleccionados.
                </div>

            <?php else: ?>

                <p class="texto-ayuda">
                    <i class="bi bi-arrows"></i>
                    Deslizá la tabla hacia los costados
                    para consultar todas las columnas.
                </p>

                <div class="auditoria-tabla">

                    <table class="table table-bordered table-striped table-hover align-middle">

                        <thead>

                            <tr>
                                <th class="columna-id">ID</th>
                                <th class="columna-fecha">Fecha y hora</th>
                                <th class="columna-usuario">Usuario</th>
                                <th class="columna-accion">Acción</th>
                                <th class="columna-entidad">Entidad</th>
                                <th class="columna-id">ID entidad</th>
                                <th class="columna-descripcion">Descripción</th>
                                <th class="columna-valor">Valor anterior</th>
                                <th class="columna-valor">Valor nuevo</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($movimientos as $m): ?>

                                <tr>

                                    <td>
                                        <?= esc($m["id"]) ?>
                                    </td>

                                    <td class="columna-fecha">
                                        <?= esc(fechaAuditoria($m["fecha_hora"])) ?>
                                    </td>

                                    <td>
                                        <?= esc($m["usuario"] ?? "Sin usuario") ?>
                                    </td>

                                    <td>
                                        <span class="etiqueta-accion">
                                            <?= esc($m["accion"]) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= esc($m["entidad"]) ?>
                                    </td>

                                    <td>
                                        <?= esc($m["id_entidad"]) ?>
                                    </td>

                                    <td>
                                        <?= esc($m["descripcion"]) ?>
                                    </td>

                                    <td class="columna-valor">
                                        <?= esc($m["valor_anterior"]) ?>
                                    </td>

                                    <td class="columna-valor">
                                        <?= esc($m["valor_nuevo"]) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <div class="mt-3 text-end">
                    <strong>
                        Total de movimientos:
                        <?= count($movimientos) ?>
                    </strong>
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

</body>
</html>
