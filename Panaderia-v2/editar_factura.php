<?php
session_start();
require_once "conexion.php";
require_once "registrar_auditoria.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";

// ID de la factura que se quiere editar
// Se utiliza internamente y no es editable.
$id_factura_editar = $_GET["id"] ?? $_POST["id_factura"] ?? "";


// =====================================================
// OBTENER USUARIOS ACTIVOS
// =====================================================
$sqlUsuarios = "SELECT id, usuario FROM usuario WHERE activo = 1 ORDER BY usuario";
$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);


 // =====================================================
 // PROCESAR FORMULARIO
 // =====================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_factura = $_POST["id_factura"] ?? "";
    $numero_serie = trim($_POST["numero_serie"] ?? "");
    $monto = $_POST["monto"] ?? "";
    $fecha_cobro = $_POST["fecha_cobro"] ?? "";
    $estado = $_POST["estado"] ?? "";
    $id_usuario_asignado = $_POST["id_usuario_asignado"] ?? "";

    if (
        $id_factura === "" ||
        !ctype_digit((string)$id_factura) ||
        (int)$id_factura <= 0 ||
        $numero_serie === "" ||
        $monto === "" ||
        $estado === ""
    ) {
        $mensaje = "Debe completar todos los campos obligatorios.";

    } elseif (!is_numeric($monto) || (float)$monto < 0) {
        $mensaje = "El monto ingresado no es válido.";

    } elseif (!in_array($estado, ["Cobrado", "Pendiente", "Anulada"], true)) {
        $mensaje = "El estado seleccionado no es válido.";

    } else {
        try {
            // Obtener los datos anteriores de la factura
            $sqlAnterior = "
                SELECT f.*, u.usuario AS nombre_asignado
                FROM factura f
                LEFT JOIN usuario u ON f.id_usuario_asignado = u.id
                WHERE f.id = ?
            ";

            $stmtAnterior = $conexion->prepare($sqlAnterior);
            $stmtAnterior->execute([$id_factura]);
            $facturaAnterior = $stmtAnterior->fetch(PDO::FETCH_ASSOC);

            if (!$facturaAnterior) {
                $mensaje = "La factura seleccionada no existe.";

            } else {
                // Validar usuario asignado
                if ($id_usuario_asignado !== "") {
                    $sqlVerificarUsuario = "
                        SELECT id, usuario
                        FROM usuario
                        WHERE id = ? AND activo = 1
                    ";

                    $stmtVerificarUsuario = $conexion->prepare($sqlVerificarUsuario);
                    $stmtVerificarUsuario->execute([$id_usuario_asignado]);
                    $usuarioNuevo = $stmtVerificarUsuario->fetch(PDO::FETCH_ASSOC);

                    if (!$usuarioNuevo) {
                        $mensaje = "El usuario asignado no existe o está inactivo.";
                    }
                } else {
                    $usuarioNuevo = null;
                }

                if ($mensaje === "") {
                    // Determinar fecha de cobro
                    if ($estado === "Cobrado") {
                        if ($fecha_cobro === "") {
                            $fecha_cobro = date("Y-m-d H:i:s");
                        } else {
                            $fecha_cobro = str_replace("T", " ", $fecha_cobro);

                            if (strlen($fecha_cobro) === 16) {
                                $fecha_cobro .= ":00";
                            }
                        }
                    } else {
                        $fecha_cobro = null;
                    }

                    $nombreAsignadoAnterior =
                        $facturaAnterior["nombre_asignado"] ?? "Sin asignar";

                    $nombreAsignadoNuevo =
                        $usuarioNuevo["usuario"] ?? "Sin asignar";

                    // Formato de los valores para la auditoría
                    $valorAnterior =
                        "Factura: " . $facturaAnterior["numero_serie"] .
                        " | Monto: $" . number_format((float)$facturaAnterior["monto"], 2, ",", ".") .
                        " | Estado: " . $facturaAnterior["estado"] .
                        " | Asignado a: " . $nombreAsignadoAnterior;

                    $valorNuevo =
                        "Factura: " . $numero_serie .
                        " | Monto: $" . number_format((float)$monto, 2, ",", ".") .
                        " | Estado: " . $estado .
                        " | Asignado a: " . $nombreAsignadoNuevo;

                    try {
                        $conexion->beginTransaction();

                        // Actualizar factura
                        $sql = "
                            UPDATE factura
                            SET numero_serie = ?,
                                monto = ?,
                                fecha_cobro = ?,
                                estado = ?,
                                id_usuario_asignado = ?
                            WHERE id = ?
                        ";

                        $stmt = $conexion->prepare($sql);
                        $stmt->execute([
                            $numero_serie,
                            $monto,
                            $fecha_cobro,
                            $estado,
                            ($id_usuario_asignado !== "")
                                ? $id_usuario_asignado
                                : null,
                            $id_factura
                        ]);

                        // Registrar auditoría
                        registrarAuditoria(
                            $conexion,
                            $_SESSION["id_usuario"],
                            "EDITAR",
                            "Factura",
                            $id_factura,
                            "Se modificó la factura " . $numero_serie,
                            $valorAnterior,
                            $valorNuevo
                        );

                        $conexion->commit();

                        $mensaje = "Factura actualizada correctamente.";
                        $id_factura_editar = $id_factura;

                    } catch (Exception $e) {
                        if ($conexion->inTransaction()) {
                            $conexion->rollBack();
                        }

                        $mensaje = "Error al actualizar la factura.";
                    }
                }
            }

        } catch (PDOException $e) {
            $mensaje = "Error al consultar los datos de la factura.";
        }
    }
}


// =====================================================
// OBTENER DATOS DE LA FACTURA
// =====================================================
$facturaEditar = null;
if ($id_factura_editar != "") {
    $sqlFactura = "SELECT id, numero_serie, monto, fecha_registro, fecha_cobro, estado, id_usuario_registro, id_usuario_asignado
                   FROM factura
                    WHERE id = ?";

    $stmtFactura = $conexion->prepare($sqlFactura);
    $stmtFactura->execute([$id_factura_editar]);
    $facturaEditar = $stmtFactura->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar factura</title>
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
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-8">

                <!-- =====================================================
                     CARD 1 - SELECCIONAR FACTURA
                     ===================================================== -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-receipt"></i> Editar factura</h4>
                    </div>

                    <div class="card-body">
                        <!-- Información -->
                        <div class="alert alert-info text-center"><i class="bi bi-info-circle-fill"></i> Seleccione una factura para editar.</div>
                        
                        <form method="get">
                            <div class="mb-3">
                                <label for="id" class="form-label"><i class="bi bi-receipt"></i> Factura a editar</label>
                                <select id="id" name="id" class="form-select" onchange="this.form.submit()" required>
                                    <option value="">Seleccione una factura</option>

                                    <?php
                                        $sqlListaFacturas = "SELECT id, numero_serie FROM factura ORDER BY id DESC";
                                        $stmtListaFacturas = $conexion->prepare($sqlListaFacturas);
                                        $stmtListaFacturas->execute();
                                        $facturas = $stmtListaFacturas->fetchAll(PDO::FETCH_ASSOC);
                                    ?>

                                    <?php foreach ($facturas as $factura): ?>
                                        <option value="<?= $factura["id"] ?>"
                                            <?= ($id_factura_editar == $factura["id"])
                                                ? "selected"
                                                : "" ?>
                                        >
                                            <?= htmlspecialchars($factura["numero_serie"]) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </form>

                    </div>
                </div>

                <!-- =====================================================
                     MENSAJE
                     ===================================================== -->
                <?php if ($mensaje != ""): ?>
                    <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill"></i>
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($facturaEditar): ?>

                    <!-- =================================================
                         CARD 2 - EDITAR FACTURA
                         ================================================= -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Datos de factura</h4>
                        </div>

                        <div class="card-body">
                            <form method="post">

                                <!-- ID interno -->
                                <input type="hidden" name="id_factura" value="<?= $facturaEditar["id"] ?>">

                                <!-- Número de serie -->
                                <div class="mb-3">
                                    <label for="numero_serie" class="form-label"><i class="bi bi-upc"></i> Número de serie</label>
                                    <input type="text" id="numero_serie" name="numero_serie" class="form-control"
                                        value="<?= htmlspecialchars($facturaEditar["numero_serie"]) ?>"
                                        maxlength="20"
                                        required
                                    >
                                </div>

                                <!-- Monto -->
                                <div class="mb-3">
                                    <label for="monto" class="form-label"><i class="bi bi-currency-dollar"></i> Monto</label>
                                    <input type="number" id="monto" name="monto" class="form-control"
                                        value="<?= htmlspecialchars($facturaEditar["monto"]) ?>"
                                        step="0.01"
                                        min="0"
                                        required
                                    >
                                </div>

                                <!-- Fecha de registro -->
                                <div class="mb-3">
                                    <label for="fecha_registro" class="form-label"><i class="bi bi-calendar3"></i> Fecha y hora de registro</label>
                                    <input type="text" id="fecha_registro" class="form-control"
                                        value="<?= htmlspecialchars($facturaEditar["fecha_registro"]) ?>"
                                        readonly
                                    >
                                </div>

                                <!-- Fecha de cobro -->
                                <div class="mb-3">
                                    <label for="fecha_cobro" class="form-label"><i class="bi bi-calendar-check"></i> Fecha y hora de cobro</label>
                                    <input type="datetime-local" id="fecha_cobro" name="fecha_cobro" class="form-control"
                                         value="<?php
                                            if (!empty($facturaEditar["fecha_cobro"])) {
                                                echo date("Y-m-d\TH:i", strtotime($facturaEditar["fecha_cobro"]));
                                            }
                                        ?>"
                                    >
                                </div>

                                <!-- Estado -->
                                <div class="mb-3">
                                    <label for="estado" class="form-label"><i class="bi bi-clipboard-check"></i> Estado</label>
                                    <select id="estado" name="estado" class="form-select" required>
                                        <option value="Pendiente"
                                            <?= ($facturaEditar["estado"] == "Pendiente")
                                                ? "selected"
                                                : "" ?>
                                        >
                                            Pendiente
                                        </option>
                                        <option value="Cobrado"
                                            <?= ($facturaEditar["estado"] == "Cobrado")
                                                ? "selected"
                                                : "" ?>
                                        >
                                            Cobrado
                                        </option>
                                        <option value="Anulada"
                                            <?= ($facturaEditar["estado"] == "Anulada")
                                                ? "selected"
                                                : "" ?>
                                        >
                                            Anulada
                                        </option>

                                    </select>
                                </div>

                                <!-- Usuario que registró -->
                                <div class="mb-3">
                                    <label for="id_usuario_registro" class="form-label"><i class="bi bi-person-fill"></i> Usuario que registró</label>
                                    <input type="text" id="id_usuario_registro" class="form-control"
                                        value="<?php
                                            $sqlUsuarioRegistro = "SELECT usuario FROM usuario WHERE id = ?";
                                            $stmtUsuarioRegistro = $conexion->prepare($sqlUsuarioRegistro);
                                            $stmtUsuarioRegistro->execute([$facturaEditar["id_usuario_registro"]]);
                                            $usuarioRegistro = $stmtUsuarioRegistro->fetch(PDO::FETCH_ASSOC);

                                            echo htmlspecialchars($usuarioRegistro["usuario"] ?? "Usuario no encontrado");
                                        ?>"
                                        readonly
                                    >
                                </div>

                                <!-- Usuario asignado -->
                                <div class="mb-4">
                                    <label for="id_usuario_asignado" class="form-label"><i class="bi bi-person-check-fill"></i> Usuario asignado</label>
                                    <select id="id_usuario_asignado" name="id_usuario_asignado" class="form-select">
                                        <option value="">Sin usuario asignado</option>

                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?= $usuario["id"] ?>"
                                                <?= ($facturaEditar["id_usuario_asignado"] == $usuario["id"])
                                                    ? "selected"
                                                    : "" ?>
                                            >
                                                <?= htmlspecialchars($usuario["usuario"]) ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>

                                <!-- Botones -->
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save-fill"></i> Guardar cambios</button>
                                    <a href="index.php" class="btn btn-secondary px-4"><i class="bi bi-x-circle-fill"></i> Cancelar</a>
                                </div>
                            </form>

                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
     
    
    <!-- JavaScript  para fecha de cobro -->
    <script src="js/editar_factura.js"></script>
    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>