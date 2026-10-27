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

$id_repartidor = $_POST["id_repartidor"] ?? "";
$fecha = $_POST["fecha"] ?? "";

// =====================================================
// VALIDAR DATOS
// =====================================================

$fechaValida = DateTimeImmutable::createFromFormat(
    "!Y-m-d",
    $fecha
);

if (
    !ctype_digit((string)$id_repartidor) ||
    (int)$id_repartidor < 1 ||
    !$fechaValida ||
    $fechaValida->format("Y-m-d") !== $fecha
) {
    die("Datos de liquidación no válidos.");
}

try {

    // =================================================
    // INICIAR TRANSACCIÓN
    // =================================================

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

    $stmt->execute([
        ":id" => $id_repartidor
    ]);

    $repartidor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$repartidor) {
        throw new Exception(
            "El repartidor seleccionado no es válido."
        );
    }

    // =================================================
    // ESTABLECER RANGO DE FECHAS
    // =================================================

    $inicio = $fecha . " 00:00:00";

    $fin = $fechaValida
        ->modify("+1 day")
        ->format("Y-m-d") . " 00:00:00";

    // =================================================
    // BUSCAR PAGOS PENDIENTES Y BLOQUEARLOS
    // =================================================

    $sqlPagos = "
        SELECT id, monto, forma_pago
        FROM pago
        WHERE id_repartidor = :id_repartidor
          AND fecha_hora >= :inicio
          AND fecha_hora < :fin
          AND liquidado = 0
        FOR UPDATE
    ";

    $stmt = $conexion->prepare($sqlPagos);

    $stmt->execute([
        ":id_repartidor" => $id_repartidor,
        ":inicio" => $inicio,
        ":fin" => $fin
    ]);

    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =================================================
    // VERIFICAR QUE HAYA PAGOS
    // =================================================

    if (count($pagos) === 0) {
        throw new Exception(
            "No hay pagos pendientes para liquidar."
        );
    }

    // =================================================
    // VALIDAR FORMAS DE PAGO
    // =================================================

    $formas_validas = [
        "Efectivo",
        "Transferencia",
        "Debito",
        "Credito",
        "Cheque"
    ];

    $pagos_invalidos = [];

    foreach ($pagos as $pago) {

        $forma_pago = trim(
            (string)$pago["forma_pago"]
        );

        if (!in_array(
            $forma_pago,
            $formas_validas,
            true
        )) {
            $pagos_invalidos[] = $pago["id"];
        }
    }

    if (!empty($pagos_invalidos)) {
        throw new Exception(
            "No se puede confirmar la liquidación. " .
            "Los siguientes pagos tienen una forma " .
            "de pago vacía o incorrecta: " .
            implode(", ", $pagos_invalidos)
        );
    }

    // =================================================
    // CALCULAR TOTAL E IDENTIFICAR PAGOS
    // =================================================

    $total = 0;
    $ids = [];

    foreach ($pagos as $pago) {

        $total += (float)$pago["monto"];

        $ids[] = (int)$pago["id"];
    }

    // =================================================
    // MARCAR PAGOS COMO LIQUIDADOS
    // =================================================

    $marcadores = implode(
        ",",
        array_fill(0, count($ids), "?")
    );

    $sqlActualizar = "
        UPDATE pago
        SET liquidado = 1
        WHERE id IN ($marcadores)
          AND liquidado = 0
    ";

    $stmt = $conexion->prepare($sqlActualizar);
    $stmt->execute($ids);

    if ($stmt->rowCount() !== count($ids)) {
        throw new Exception(
            "No se pudieron liquidar todos los pagos."
        );
    }

    // =================================================
    // REGISTRAR AUDITORÍA
    // =================================================

    registrarAuditoria(
        $conexion,
        $_SESSION["id_usuario"],
        "LIQUIDACION REPARTIDOR",
        "Pago",
        $ids[0],
        "Liquidación del repartidor " .
            $repartidor["usuario"],
        "Pagos pendientes de liquidación",
        "Fecha: " . $fecha .
        " | Cantidad: " . count($ids) .
        " | Total: $" . number_format(
            $total,
            2,
            ",",
            "."
        ) .
        " | IDs: " . implode(",", $ids)
    );

    // =================================================
    // CONFIRMAR CAMBIOS
    // =================================================

    $conexion->commit();

    // =================================================
    // REDIRIGIR AL COMPROBANTE
    // =================================================

    header(
        "Location: liquidacion_confirmada.php?" .
        http_build_query([
            "id_repartidor" => $id_repartidor,
            "fecha" => $fecha
        ])
    );

    exit;

} catch (Exception $e) {

    // =================================================
    // CANCELAR TODO SI HAY ERROR
    // =================================================

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(400);

    echo "<h3>Error al confirmar liquidación</h3>";

    echo "<p>" .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            "UTF-8"
        ) .
        "</p>";

    echo '<a href="liquidacion_diaria.php">
            Volver a liquidaciones
          </a>';
}
?>