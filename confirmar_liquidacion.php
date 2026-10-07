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

    header("Location: liquidacion_diaria.php");
    exit;
}


// =====================================================
// RECIBIR DATOS
// =====================================================

$id_repartidor =
    $_POST["id_repartidor"] ?? "";

$fecha =
    $_POST["fecha"] ?? "";


// =====================================================
// VALIDAR DATOS
// =====================================================

if (
    $id_repartidor === ""
    ||
    !is_numeric($id_repartidor)
    ||
    $fecha === ""
) {

    die("Datos de liquidación no válidos.");
}


try {

    // =================================================
    // INICIAR TRANSACCIÓN
    // =================================================

    $conexion->beginTransaction();


    // =================================================
    // BUSCAR REPARTIDOR
    // =================================================

    $sqlRepartidor = "
        SELECT
            u.id,
            u.usuario

        FROM usuario u

        INNER JOIN rol r
            ON u.id_ROL = r.id

        WHERE u.id = :id
        AND u.activo = 1
        AND r.nombre = 'Repartidor'
    ";


    $stmtRepartidor =
        $conexion->prepare($sqlRepartidor);


    $stmtRepartidor->execute([
        ":id" => $id_repartidor
    ]);


    $repartidor =
        $stmtRepartidor->fetch(PDO::FETCH_ASSOC);


    if (!$repartidor) {

        throw new Exception(
            "El repartidor seleccionado no es válido."
        );
    }


    // =================================================
    // BUSCAR PAGOS PENDIENTES DE LIQUIDACIÓN
    // =================================================

    $sqlPagos = "
        SELECT
            p.id,
            p.id_factura,
            p.monto,
            p.forma_pago,
            p.fecha_hora,
            f.numero_serie

        FROM pago p

        INNER JOIN factura f
            ON p.id_factura = f.id

        WHERE p.id_repartidor = :id_repartidor

        AND DATE(p.fecha_hora) = :fecha

        AND p.liquidado = 0

        ORDER BY p.fecha_hora ASC
    ";


    $stmtPagos =
        $conexion->prepare($sqlPagos);


    $stmtPagos->execute([

        ":id_repartidor" =>
            $id_repartidor,

        ":fecha" =>
            $fecha

    ]);


    $pagos =
        $stmtPagos->fetchAll(PDO::FETCH_ASSOC);


    // =================================================
    // VERIFICAR QUE HAYA PAGOS
    // =================================================

    if (count($pagos) === 0) {

        throw new Exception(
            "No hay pagos pendientes para liquidar."
        );
    }


    // =================================================
    // CALCULAR TOTAL
    // =================================================

    $total = 0;


    foreach ($pagos as $pago) {

        $total +=
            (float)$pago["monto"];
    }


    // =================================================
    // MARCAR LOS PAGOS COMO LIQUIDADOS
    // =================================================

    $sqlActualizar = "
        UPDATE pago

        SET liquidado = 1

        WHERE id_repartidor = :id_repartidor

        AND DATE(fecha_hora) = :fecha

        AND liquidado = 0
    ";


    $stmtActualizar =
        $conexion->prepare($sqlActualizar);


    $stmtActualizar->execute([

        ":id_repartidor" =>
            $id_repartidor,

        ":fecha" =>
            $fecha

    ]);


    // =================================================
    // REGISTRAR CADA PAGO EN AUDITORÍA
    // =================================================

    foreach ($pagos as $pago) {


        registrarAuditoria(

            $conexion,

            // Usuario que confirmó la liquidación
            $_SESSION["id_usuario"],

            // Acción
            "LIQUIDAR",

            // Entidad
            "Pago",

            // ID del pago
            $pago["id"],

            // Descripción
            "Se liquidó el pago de la factura " .
            $pago["numero_serie"] .
            " correspondiente al repartidor " .
            $repartidor["usuario"],

            // Valor anterior
            "Liquidado: No",

            // Valor nuevo
            "Liquidado: Sí | Fecha de liquidación: " .
            $fecha .
            " | Monto: $" .
            $pago["monto"]
        );

    }


    // =================================================
    // CONFIRMAR TRANSACCIÓN
    // =================================================

    $conexion->commit();


    // =================================================
    // IR A PANTALLA DE RESULTADO
    // =================================================

    header(
        "Location: liquidacion_confirmada.php?" .
        "id_repartidor=" .
        urlencode($id_repartidor) .
        "&fecha=" .
        urlencode($fecha) .
        "&total=" .
        urlencode($total)
    );

    exit;


} catch (Exception $e) {


    // =================================================
    // CANCELAR TODO SI HAY ERROR
    // =================================================

    if ($conexion->inTransaction()) {

        $conexion->rollBack();
    }


    die(
        "Error al confirmar la liquidación: " .
        htmlspecialchars($e->getMessage())
    );
}