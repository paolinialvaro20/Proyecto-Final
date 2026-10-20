<?php
session_start();
require_once "conexion.php";

// =====================================================
// VERIFICAR QUE HAYA UN USUARIO LOGUEADO
// =====================================================
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "menu.php";
$error = "";
$auditorias = [];

// =====================================================
// OBTENER LOS REGISTROS DE AUDITORÍA
// =====================================================
try {
    $sql = "
        SELECT
            a.id,
            a.id_usuario,
            u.usuario,
            a.accion,
            a.entidad,
            a.id_entidad,
            a.descripcion,
            a.valor_anterior,
            a.valor_nuevo,
            a.fecha_hora
        FROM auditoria a
        INNER JOIN usuario u ON a.id_usuario = u.id
        ORDER BY a.fecha_hora DESC, a.id DESC
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $auditorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "No se pudieron cargar los registros de auditoría.";
}

// =====================================================
// FUNCIONES PARA MOSTRAR LOS DATOS DE FORMA SEGURA
// =====================================================

function escapar($valor) {
    return htmlspecialchars(
        (string)($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

function mostrarValor($valor) {
    if ($valor === null || $valor === "") {
        return '<span class="text-muted">Sin datos</span>';
    }

    return escapar($valor);
}

// =====================================================
// COLORES SEGÚN LA ACCIÓN REALIZADA
// =====================================================

function colorAccion($accion) {
    switch (strtolower($accion)) {
        case "crear":
        case "insertar":
            return "success";

        case "editar":
        case "actualizar":
        case "modificar":
            return "warning";

        case "eliminar":
        case "borrar":
            return "danger";

        case "cambiar_estado":
        case "cambiar estado":
            return "primary";

        case "login":
        case "iniciar_sesion":
            return "info";

        default:
            return "secondary";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Auditoría</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/estilo.css">
    
</head>

<body>

<div class="container-fluid mt-4">

    <!-- ENCABEZADO -->

    <div class="d-flex justify-content-between
                align-items-center flex-wrap gap-2 mb-4">

        <div>
            <h2>
                <i class="bi bi-journal-text"></i>
                Registro de auditoría
            </h2>

            <p class="text-muted mb-0">
                Historial de acciones realizadas en el
                Sistema de Gestión de Deudas.
            </p>
        </div>

        <div>
            <a href="facturas.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Volver al menú
            </a>
        </div>

    </div>

    <!-- MENSAJE DE ERROR -->

    <?php if ($error !== ""): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <?= escapar($error) ?>
        </div>

    <?php else: ?>

        <!-- TABLA DE AUDITORÍA -->

        <div class="card shadow-sm">

            <div class="card-header bg-dark text-white">
                <i class="bi bi-clock-history"></i>
                Historial de operaciones
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <span class="badge bg-dark">
                        Total de registros:
                        <?= count($auditorias) ?>
                    </span>
                </div>

                <div class="table-responsive">

                    <table class="table table-striped
                                  table-hover table-bordered
                                  align-middle">

                        <thead class="table-dark">

                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Entidad</th>
                                <th>ID del registro</th>
                                <th>Descripción</th>
                                <th>Valor anterior</th>
                                <th>Valor nuevo</th>
                                <th>Fecha y hora</th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php if (count($auditorias) > 0): ?>

                            <?php foreach ($auditorias as $fila): ?>

                                <tr>

                                    <!-- ID DE AUDITORÍA -->

                                    <td>
                                        <?= escapar($fila["id"]) ?>
                                    </td>

                                    <!-- USUARIO -->

                                    <td>
                                        <i class="bi bi-person-circle"></i>

                                        <?= escapar(
                                            $fila["usuario"]
                                            ?? ("Usuario #" . $fila["id_usuario"])
                                        ) ?>
                                    </td>

                                    <!-- ACCIÓN -->

                                    <td>
                                        <span class="badge bg-<?= colorAccion(
                                            $fila["accion"]
                                        ) ?>">
                                            <?= escapar($fila["accion"]) ?>
                                        </span>
                                    </td>

                                    <!-- ENTIDAD -->

                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= escapar($fila["entidad"]) ?>
                                        </span>
                                    </td>

                                    <!-- ID DE LA ENTIDAD -->

                                    <td>
                                        <?= $fila["id_entidad"] !== null
                                            ? escapar($fila["id_entidad"])
                                            : '<span class="text-muted">N/A</span>' ?>
                                    </td>

                                    <!-- DESCRIPCIÓN -->

                                    <td>
                                        <?= mostrarValor(
                                            $fila["descripcion"]
                                        ) ?>
                                    </td>

                                    <!-- VALOR ANTERIOR -->

                                    <td style="min-width: 160px;
                                               max-width: 280px;
                                               overflow-wrap: anywhere;">

                                        <?= mostrarValor(
                                            $fila["valor_anterior"]
                                        ) ?>

                                    </td>

                                    <!-- VALOR NUEVO -->

                                    <td style="min-width: 160px;
                                               max-width: 280px;
                                               overflow-wrap: anywhere;">

                                        <?= mostrarValor(
                                            $fila["valor_nuevo"]
                                        ) ?>

                                    </td>

                                    <!-- FECHA Y HORA -->

                                    <td style="min-width: 150px;">
                                        <i class="bi bi-calendar-event"></i>

                                        <?= escapar(
                                            date(
                                                "d/m/Y H:i:s",
                                                strtotime($fila["fecha_hora"])
                                            )
                                        ) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="9"
                                    class="text-center text-muted py-4">

                                    <i class="bi bi-inbox fs-3"></i>

                                    <p class="mb-0">
                                        No hay registros de auditoría
                                        para mostrar.
                                    </p>

                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    <?php endif; ?>

</div>


<!-- JavaScript de Bootstrap 5.3.8 -->
<script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>
