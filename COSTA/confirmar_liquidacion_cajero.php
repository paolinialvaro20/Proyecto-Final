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

// =====================================================
// SOLO PERMITIR POST
// =====================================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: liquidacion_diaria_cajero.php");
    exit;
}

// =====================================================
// RECIBIR Y VALIDAR DATOS
// =====================================================
$id_cajero = $_POST["id_cajero"] ?? "";
$fecha = $_POST["fecha"] ?? "";

$fechaValida = DateTimeImmutable::createFromFormat("!Y-m-d", $fecha);

if (
    !ctype_digit((string)$id_cajero) ||
    (int)$id_cajero < 1 ||
    !$fechaValida ||
    $fechaValida->format("Y-m-d") !== $fecha
) {
    die("Datos de liquidación no válidos.");
}

try {
    $conexion->beginTransaction();

    // =================================================
    // VERIFICAR CAJERO ACTIVO
    // =================================================
    $sqlCajero = "
        SELECT u.id, u.usuario
        FROM usuario u
        INNER JOIN rol r ON r.id = u.id_ROL
        WHERE u.id = :id
          AND u.activo = 1
          AND LOWER(r.nombre) IN ('cajero', 'cajera')
    ";
    $stmt = $conexion->prepare($sqlCajero);
    $stmt->execute([":id" => $id_cajero]);
    $cajero = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cajero) {
        throw new Exception("El cajero seleccionado no es válido.");
    }

    // =================================================
    // ESTABLECER RANGO DE FECHAS
    // =================================================
    $inicio = $fecha . " 00:00:00";
    $fin = $fechaValida->modify("+1 day")->format("Y-m-d") . " 00:00:00";

    // =================================================
    // BUSCAR Y BLOQUEAR PAGOS PENDIENTES
    // =================================================
    // En el diseño actual, pago.id_repartidor también identifica
    // al cajero para los pagos registrados por este módulo.
    $sqlPagos = "
        SELECT id, monto, forma_pago
        FROM pago
        WHERE id_repartidor = :id_cajero
          AND fecha_hora >= :inicio
          AND fecha_hora < :fin
          AND liquidado = 0
          AND id_liquidacion IS NULL
        FOR UPDATE
    ";
    $stmt = $conexion->prepare($sqlPagos);
    $stmt->execute([
        ":id_cajero" => $id_cajero,
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
        ":id_usuario_liquidado" => $id_cajero,
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
        "LIQUIDACION CAJERO",
        "Liquidacion",
        $id_liquidacion,
        "Liquidación del cajero " . $cajero["usuario"],
        "Sin liquidar",
        "ID liquidación: " . $id_liquidacion .
        " | Fecha de pagos: " . $fecha .
        " | Cantidad: " . count($ids) .
        " | Total: $" . number_format($total_general, 2, ",", ".") .
        " | IDs de pagos: " . implode(",", $ids)
    );

    $conexion->commit();

    header(
        "Location: liquidacion_confirmada_cajero.php?" .
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
    echo '<a href="liquidacion_diaria_cajero.php">Volver a liquidaciones</a>';
}
?>
