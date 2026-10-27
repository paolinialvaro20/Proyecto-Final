
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

try {

    // CONSULTAR MOVIMIENTOS DE AUDITORÍA
    $sql = "
        SELECT
            a.id,
            a.fecha_hora,
            u.usuario,
            a.accion,
            a.entidad,
            a.id_entidad,
            a.descripcion,
            a.valor_anterior,
            a.valor_nuevo
        FROM auditoria a
        LEFT JOIN usuario u
            ON u.id = a.id_usuario
        ORDER BY a.fecha_hora DESC, a.id DESC
    ";

    $stmt = $conexion->query($sql);
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = "No se pudieron cargar los movimientos de auditoría.";
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

            <?php if ($error): ?>

                <div class="alert alert-danger">
                    <?= esc($error) ?>
                </div>

            <?php elseif (empty($movimientos)): ?>

                <div class="alert alert-info">
                    Todavía no hay movimientos registrados.
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
