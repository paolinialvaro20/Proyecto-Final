<?php
session_start();
require_once "conexion.php";
require_once "registrar_auditoria.php";
require_once "permisos.php";

// =====================================================
// VERIFICAR SESIÓN Y PERMISOS
// =====================================================
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}
exigirPermisoNoCajero($conexion, $_SESSION["id_usuario"]);

// =====================================================
// SOLO PERMITIR POST
// =====================================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: liquidacion_diaria.php");
    exit;
}

// =====================================================
// RECIBIR Y VALIDAR DATOS
// =====================================================
$id_repartidor = $_POST["id_repartidor"] ?? "";
$fecha = $_POST["fecha"] ?? "";

$fechaValida = DateTimeImmutable::createFromFormat("!Y-m-d", $fecha);

if (
    !ctype_digit((string)$id_repartidor) ||
    (int)$id_repartidor < 1 ||
    !$fechaValida ||
    $fechaValida->format("Y-m-d") !== $fecha
) {
    die("Datos de liquidación no válidos.");
}

try {
    $conexion->beginTransaction();

    // =================================================
    // VERIFICAR REPARTIDOR ACTIVO
    // =================================================
    $sqlRepartidor = "
        SELECT u.id, u.usuario
        FROM usuario u
        INNER JOIN rol r ON r.id = u.id_ROL
        WHERE u.id = :id
          AND u.activo = 1
          AND r.nombre = 'Repartidor'
    ";
    $stmt = $conexion->prepare($sqlRepartidor);
    $stmt->execute([":id" => $id_repartidor]);
    $repartidor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$repartidor) {
        throw new Exception("El repartidor seleccionado no es válido.");
    }

    // =================================================
    // ESTABLECER RANGO DE FECHAS
    // =================================================
    $inicio = $fecha . " 00:00:00";
    $fin = $fechaValida->modify("+1 day")->format("Y-m-d") . " 00:00:00";

    // =================================================
    // BUSCAR Y BLOQUEAR PAGOS PENDIENTES
    // =================================================
    $sqlPagos = "
        SELECT id, monto, forma_pago
        FROM pago
        WHERE id_repartidor = :id_repartidor
          AND fecha_hora >= :inicio
          AND fecha_hora < :fin
          AND liquidado = 0
          AND id_liquidacion IS NULL
        FOR UPDATE
    ";
    $stmt = $conexion->prepare($sqlPagos);
    $stmt->execute([
        ":id_repartidor" => $id_repartidor,
        ":inicio" => $inicio,
        ":fin" => $fin
    ]);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$pagos) {
        throw new Exception("No hay pagos pendientes para liquidar.");
    }

    // =================================================
    // CALCULAR CANTIDAD Y TOTALES POR FORMA DE PAGO
    // =================================================
    $formas_validas = ["Efectivo", "Transferencia", "Debito", "Credito", "Cheque"];

    $totales = [
        "Efectivo" => 0.00,
        "Transferencia" => 0.00,
        "Debito" => 0.00,
        "Credito" => 0.00,
        "Cheque" => 0.00
    ];

    $ids = [];

    foreach ($pagos as $pago) {
        $forma = trim((string)$pago["forma_pago"]);

        if (!in_array($forma, $formas_validas, true)) {
            throw new Exception(
                "El pago ID " . (int)$pago["id"] .
                " tiene una forma de pago no válida."
            );
        }

        $totales[$forma] += (float)$pago["monto"];
        $ids[] = (int)$pago["id"];
    }

    $total_general = array_sum($totales);

    // =================================================
    // INSERTAR CABECERA DE LIQUIDACIÓN
    // =================================================
    $sqlLiquidacion = "
        INSERT INTO liquidacion (
            id_usuario_liquidado,
            id_usuario_registro,
            cantidad_pagos,
            total_efectivo,
            total_transferencia,
            total_debito,
            total_credito,
            total_cheque,
            total_general
        ) VALUES (
            :id_usuario_liquidado,
            :id_usuario_registro,
            :cantidad_pagos,
            :total_efectivo,
            :total_transferencia,
            :total_debito,
            :total_credito,
            :total_cheque,
            :total_general
        )
    ";

    $stmt = $conexion->prepare($sqlLiquidacion);
    $stmt->execute([
        ":id_usuario_liquidado" => $id_repartidor,
        ":id_usuario_registro" => $_SESSION["id_usuario"],
        ":cantidad_pagos" => count($ids),
        ":total_efectivo" => $totales["Efectivo"],
        ":total_transferencia" => $totales["Transferencia"],
        ":total_debito" => $totales["Debito"],
        ":total_credito" => $totales["Credito"],
        ":total_cheque" => $totales["Cheque"],
        ":total_general" => $total_general
    ]);

    $id_liquidacion = (int)$conexion->lastInsertId();

    if ($id_liquidacion < 1) {
        throw new Exception("No se pudo obtener el identificador de la liquidación.");
    }

    // =================================================
    // VINCULAR LOS PAGOS A LA LIQUIDACIÓN
    // =================================================
    $marcadores = implode(",", array_fill(0, count($ids), "?"));
    $sqlActualizar = "
        UPDATE pago
        SET liquidado = 1,
            id_liquidacion = ?
        WHERE id IN ($marcadores)
          AND liquidado = 0
          AND id_liquidacion IS NULL
    ";

    $stmt = $conexion->prepare($sqlActualizar);
    $stmt->execute(array_merge([$id_liquidacion], $ids));

    if ($stmt->rowCount() !== count($ids)) {
        throw new Exception("No se pudieron asociar todos los pagos a la liquidación.");
    }

    // =================================================
    // REGISTRAR AUDITORÍA
    // =================================================
    registrarAuditoria(
        $conexion,
        $_SESSION["id_usuario"],
        "LIQUIDACION REPARTIDOR",
        "Liquidacion",
        $id_liquidacion,
        "Liquidación del repartidor " . $repartidor["usuario"],
        "Sin liquidar",
        "ID liquidación: " . $id_liquidacion .
        " | Fecha de pagos: " . $fecha .
        " | Cantidad: " . count($ids) .
        " | Total: $" . number_format($total_general, 2, ",", ".") .
        " | IDs de pagos: " . implode(",", $ids)
    );

    $conexion->commit();

    header(
        "Location: liquidacion_confirmada.php?" .
        http_build_query(["id_liquidacion" => $id_liquidacion])
    );
    exit;

} catch (Exception $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(400);
    echo "<h3>Error al confirmar liquidación</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8") . "</p>";
    echo '<a href="liquidacion_diaria.php">Volver a liquidaciones</a>';
}
?>
