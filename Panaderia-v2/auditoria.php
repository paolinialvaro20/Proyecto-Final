<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// Obtener los registros de auditoría
$sql = "SELECT a.id, u.usuario, a.accion, a.entidad, a.id_entidad, a.descripcion, a.valor_anterior, a.valor_nuevo, a.fecha_hora
        FROM auditoria a LEFT JOIN usuario u ON a.id_usuario = u.id
        ORDER BY a.fecha_hora DESC, a.id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría del sistema</title>
    <!-- Bootstrap 5.3.8 -->
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
            <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
        </div>

        <!-- Tarjeta principal -->
        <div class="card shadow mb-5">

            <!-- Encabezado -->
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0"><i class="bi bi-journal-text"></i> Registro de auditoría</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">

                    <!-- Descripción -->
                    <p class="text-muted mb-0">Registro de las acciones realizadas por los usuarios del sistema.</p>

                    <!-- Contador -->
                    <span class="badge bg-dark">
                        Total de registros: <?= count($registros) ?>
                    </span>
                </div>
                <?php if (count($registros) > 0): ?>

                    <!-- Tabla responsive -->
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
                                    // Color para identificar el tipo de acción
                                    $accion = mb_strtoupper(trim($registro["accion"]), "UTF-8");

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
                                        <!-- Usuario -->
                                        <td class="text-center text-nowrap">
                                            <i class="bi bi-person-fill"></i>
                                            <?= htmlspecialchars($registro["usuario"] ?? "Usuario no disponible") ?>
                                        </td>

                                        <!-- Acción -->
                                        <td class="text-center text-nowrap">
                                            <span class="badge <?= $claseAccion ?>">
                                                <?= htmlspecialchars($registro["accion"]) ?>
                                            </span>
                                        </td>

                                        <!-- Entidad -->
                                        <td class="text-center fw-bold text-uppercase">
                                            <?= htmlspecialchars($registro["entidad"] ?? "-") ?>
                                        </td>

                                        <!-- Descripción -->
                                        <td>
                                            <?= !empty($registro["descripcion"])
                                                ? htmlspecialchars($registro["descripcion"])
                                                : "-" ?>
                                        </td>

                                        <!-- Valor anterior -->
                                        <td>
                                            <?= !empty($registro["valor_anterior"])
                                                ? htmlspecialchars($registro["valor_anterior"])
                                                : "-" ?>
                                        </td>

                                        <!-- Valor nuevo -->
                                        <td>
                                            <?= !empty($registro["valor_nuevo"])
                                                ? htmlspecialchars($registro["valor_nuevo"])
                                                : "-" ?>
                                        </td>

                                        <!-- Fecha y hora -->
                                        <td class="text-center text-nowrap">
                                            <i class="bi bi-calendar-event"></i>
                                            <?= htmlspecialchars($fecha->format("d/m/Y H:i:s")) ?>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>
                            </tbody>

                        </table>
                    </div>

                <?php else: ?>

                    <!-- Mensaje cuando no hay registros -->
                    <div class="alert alert-info text-center mb-0">
                        <i class="bi bi-info-circle-fill"></i>
                        Todavía no existen registros de auditoría.
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>

</body>
</html>