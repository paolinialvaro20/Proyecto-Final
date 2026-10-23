<?php
session_start();
require_once "conexion.php";
require_once "registrar_auditoria.php";

// =====================================================
// VERIFICAR SESIÓN
// =====================================================
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$error = "";
$id_factura_seleccionada = "";
$id_usuario_cobro_seleccionado = "";
$forma_pago_seleccionada = "";

// =====================================================
// PROCESAR PAGO
// =====================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_factura = $_POST["id_factura"] ?? "";
    $id_usuario_cobro = $_POST["id_usuario_cobro"] ?? "";
    $forma_pago = $_POST["forma_pago"] ?? "";

    // Mantener los valores seleccionados si hay error
    $id_factura_seleccionada = $id_factura;
    $id_usuario_cobro_seleccionado = $id_usuario_cobro;
    $forma_pago_seleccionada = $forma_pago;

    // Formas de pago permitidas
    $formas_pago_validas = [
        "Efectivo",
        "Transferencia",
        "Debito",
        "Credito",
        "Cheque"
    ];
    // =================================================
    // VALIDACIONES
    // =================================================
    if (
        $id_factura === "" ||
        !ctype_digit((string)$id_factura) ||
        (int)$id_factura < 1
    ) {
        $error = "Debés seleccionar una factura.";

    } elseif (
        $id_usuario_cobro === "" ||
        !ctype_digit((string)$id_usuario_cobro) ||
        (int)$id_usuario_cobro < 1
    ) {
        $error = "Debés seleccionar quién realizó el cobro.";

    } elseif (
        !in_array($forma_pago, $formas_pago_validas, true)
    ) {
        $error = "Debés seleccionar una forma de pago válida.";

    } else {
        try {

            // Iniciar transacción
            $conexion->beginTransaction();

            // =========================================
            // BUSCAR FACTURA
            // =========================================
            $sqlFactura = "SELECT f.id, f.numero_serie, f.monto, f.estado, f.id_usuario_asignado, u.usuario AS usuario_asignado
                FROM factura f LEFT JOIN usuario u ON f.id_usuario_asignado = u.id
                WHERE f.id = :id FOR UPDATE
            ";
            $stmtFactura = $conexion->prepare($sqlFactura);
            $stmtFactura->execute([":id" => $id_factura]);
            $factura = $stmtFactura->fetch(PDO::FETCH_ASSOC);
            if (!$factura) {
                throw new Exception(
                    "La factura seleccionada no existe."
                );
            }

            // Verificar que esté pendiente
            if ($factura["estado"] !== "Pendiente") {
                throw new Exception(
                    "Solamente se pueden cobrar facturas pendientes."
                );
            }

            // =========================================
            // VERIFICAR USUARIO QUE REALIZÓ EL COBRO
            // =========================================
            $sqlUsuario = " SELECT id, usuario, activo FROM usuario WHERE id = :id";
            $stmtUsuario = $conexion->prepare($sqlUsuario);
            $stmtUsuario->execute([":id" => $id_usuario_cobro]);
            $usuarioCobro = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

            if (!$usuarioCobro) {
                throw new Exception(
                    "El usuario que realizó el cobro no existe."
                );
            }

            if ((int)$usuarioCobro["activo"] !== 1) {
                throw new Exception(
                    "El usuario seleccionado está inactivo."
                );
            }

            // =========================================
            // VERIFICAR SI YA EXISTE UN PAGO
            // =========================================

            $sqlExistePago = "SELECT id FROM pago WHERE id_factura = :id_factura LIMIT 1";
            $stmtExistePago = $conexion->prepare($sqlExistePago);
            $stmtExistePago->execute([":id_factura" => $id_factura]);

            if ($stmtExistePago->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception(
                    "Esta factura ya tiene un pago registrado."
                );
            }

            // =========================================
            // REGISTRAR PAGO
            // =========================================
            // Se conserva id_repartidor porque es el nombre
            // actual de la columna en la tabla pago.
            $sqlPago = "INSERT INTO pago ( id_factura, id_repartidor, monto, forma_pago, fecha_hora, liquidado)
                VALUES ( :id_factura, :id_usuario_cobro, :monto, :forma_pago, NOW(), 0)
            ";
            $stmtPago = $conexion->prepare($sqlPago);
            $stmtPago->execute([
                ":id_factura" => $id_factura,
                ":id_usuario_cobro" => $id_usuario_cobro,
                ":monto" => $factura["monto"],
                ":forma_pago" => $forma_pago
            ]);
            $id_pago = $conexion->lastInsertId();

            // =========================================
            // ACTUALIZAR ESTADO DE LA FACTURA
            // =========================================
            $sqlActualizarFactura = "UPDATE factura SET estado = 'Cobrado', fecha_cobro = NOW() WHERE id = :id";
            $stmtActualizarFactura = $conexion->prepare($sqlActualizarFactura);
            $stmtActualizarFactura->execute([":id" => $id_factura]);

            
            // =========================================
            // REGISTRAR AUDITORÍA
            // =========================================
            $asignadoA = $factura["usuario_asignado"]
                ?? "Sin asignar";

            $valorNuevo =
                "Factura: " . $factura["numero_serie"] .
                " | Asignada a: " . $asignadoA .
                " | Cobrado por: " . $usuarioCobro["usuario"] .
                " | Monto: $" . number_format(
                    (float)$factura["monto"], 2, ".", ""
                ) .
                " | Forma de pago: " . $forma_pago .
                " | Liquidado: No";

            registrarAuditoria(
                $conexion,
                $_SESSION["id_usuario"],
                "REGISTRAR",
                "Pago",
                $id_pago,
                "Se registró el pago de la factura " .
                    $factura["numero_serie"] .
                    " cobrado por " .
                    $usuarioCobro["usuario"],
                "Factura pendiente de cobro",
                $valorNuevo
            );


            // Confirmar transacción
            $conexion->commit();
            $mensaje =
                "Pago registrado correctamente. Cobrado por: " .
                $usuarioCobro["usuario"] . ".";

            // Limpiar formulario
            $id_factura_seleccionada = "";
            $id_usuario_cobro_seleccionado = "";
            $forma_pago_seleccionada = "";

        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $error = $e->getMessage();
        }
    }
}

// =====================================================
// OBTENER FACTURAS PENDIENTES
// =====================================================
try {

    $sqlFacturas = "SELECT f.id, f.numero_serie, f.monto, f.id_usuario_asignado, u.usuario AS usuario_asignado
        FROM factura f LEFT JOIN usuario u ON f.id_usuario_asignado = u.id
        WHERE f.estado = 'Pendiente'
        ORDER BY f.id DESC
    ";
    $stmtFacturas = $conexion->prepare($sqlFacturas);
    $stmtFacturas->execute();
    $facturas = $stmtFacturas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $facturas = [];
    $error = "No se pudieron cargar las facturas pendientes.";
}

// =====================================================
// OBTENER USUARIOS ACTIVOS
// =====================================================
try {

    $sqlUsuarios = "SELECT id, usuario FROM usuario WHERE activo = 1 ORDER BY usuario";
    $stmtUsuarios = $conexion->prepare($sqlUsuarios);
    $stmtUsuarios->execute();
    $usuariosCobro = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $usuariosCobro = [];
    $error = "No se pudieron cargar los usuarios.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar pagos</title>
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
            <div class="col-lg-8 col-xl-7">

                <!-- Tarjeta de registro -->
                <div class="card shadow mb-5">

                    <!-- Encabezado -->
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-cash-stack"></i> Registrar pago</h4>
                    </div>
                    <div class="card-body p-4">

                        <!-- Mensaje de éxito -->
                        <?php if ($mensaje !== ""): ?>
                            <div class="alert alert-success text-center">
                                <i class="bi bi-check-circle-fill"></i>
                                <?= htmlspecialchars($mensaje) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Mensaje de error -->
                        <?php if ($error !== ""): ?>
                            <div class="alert alert-danger text-center">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (count($facturas) > 0): ?>
                            <form method="POST">

                                <!-- Factura -->
                                <div class="mb-3">
                                    <label for="id_factura" class="form-label"><i class="bi bi-receipt"></i> Factura pendiente</label>
                                    <select name="id_factura" id="id_factura" class="form-select" required>
                                        <option value="">-- Seleccionar factura --</option>
                                        <?php foreach ($facturas as $factura): ?>
                                            <option
                                                value="<?= htmlspecialchars((string)$factura["id"]) ?>"
                                                <?= ($id_factura_seleccionada == $factura["id"]) ? "selected" : "" ?>
                                            >
                                                <?= htmlspecialchars($factura["numero_serie"]) ?>
                                                -
                                                $<?= number_format((float)$factura["monto"], 2, ",", ".") ?>
                                                - Asignada a:
                                                <?= htmlspecialchars($factura["usuario_asignado"] ?? "Sin asignar") ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                    <div class="form-text">
                                        Seleccioná la factura que se va a cobrar.
                                    </div>
                                </div>

                                <!-- Usuario que realizó el cobro -->
                                <div class="mb-3">
                                    <label for="id_usuario_cobro" class="form-label">
                                        <i class="bi bi-person-check-fill"></i>
                                        Cobrado por
                                    </label>
                                    <select name="id_usuario_cobro" id="id_usuario_cobro" class="form-select" required>
                                        <option value="">-- Seleccionar usuario --</option>
                                        <?php foreach ($usuariosCobro as $usuario): ?>
                                            <option
                                                value="<?= htmlspecialchars((string)$usuario["id"]) ?>"
                                                <?= ($id_usuario_cobro_seleccionado == $usuario["id"]) ? "selected" : "" ?>
                                            >
                                                <?= htmlspecialchars($usuario["usuario"]) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        Indicá qué usuario recibió el pago.
                                    </div>
                                </div>

                                <!-- Forma de pago -->
                                <div class="mb-4">
                                    <label for="forma_pago" class="form-label"><i class="bi bi-wallet2"></i> Forma de pago</label>
                                    <select name="forma_pago" id="forma_pago" class="form-select" required>
                                        <option value="">-- Seleccionar forma de pago --</option>
                                        <option
                                            value="Efectivo"
                                            <?= $forma_pago_seleccionada === "Efectivo"
                                                ? "selected" : "" ?>
                                        >
                                            Efectivo
                                        </option>
                                        <option
                                            value="Transferencia"
                                            <?= $forma_pago_seleccionada === "Transferencia"
                                                ? "selected" : "" ?>
                                        >
                                            Transferencia
                                        </option>
                                        <option
                                            value="Debito"
                                            <?= $forma_pago_seleccionada === "Debito"
                                                ? "selected" : "" ?>
                                        >
                                            Débito
                                        </option>
                                        <option
                                            value="Credito"
                                            <?= $forma_pago_seleccionada === "Credito"
                                                ? "selected" : "" ?>
                                        >
                                            Crédito
                                        </option>
                                        <option
                                            value="Cheque"
                                            <?= $forma_pago_seleccionada === "Cheque"
                                                ? "selected" : "" ?>
                                        >
                                            Cheque
                                        </option>
                                    </select>
                                </div>

                                <!-- Botones -->
                                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                    <a href="listar_pagos.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left-circle"></i>
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Registrar pago
                                    </button>
                                </div>
                            </form>

                        <?php else: ?>
                            <div class="alert alert-secondary text-center mb-0">
                                <i class="bi bi-info-circle-fill"></i>
                                No hay facturas pendientes para cobrar.
                            </div>
                            <div class="text-center mt-3">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left-circle"></i>
                                    Volver al inicio
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <!-- JavaScript de Bootstrap -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>