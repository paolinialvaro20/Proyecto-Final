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
// RECIBIR DATOS
// =====================================================

$id_cajero = $_POST["id_cajero"] ?? "";
$fecha = $_POST["fecha"] ?? "";

// =====================================================
// VALIDAR DATOS
// =====================================================

$fechaValida = DateTimeImmutable::createFromFormat(
    "!Y-m-d",
    $fecha
);

if (
    !ctype_digit((string)$id_cajero) ||
    (int)$id_cajero < 1 ||
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

    $stmt->execute([
        ":id" => $id_cajero
    ]);

    $cajero = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cajero) {
        throw new Exception(
            "El cajero seleccionado no es válido."
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
        WHERE id_repartidor = :id_cajero
          AND fecha_hora >= :inicio
          AND fecha_hora < :fin
          AND liquidado = 0
        FOR UPDATE
    ";

    $stmt = $conexion->prepare($sqlPagos);

    $stmt->execute([
        ":id_cajero" => $id_cajero,
        ":inicio" => $inicio,
        ":fin" => $fin
    ]);

    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    // =================================================
    // IMPEDIR LIQUIDACIÓN SI HAY ERRORES
    // =================================================

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
        "LIQUIDACION CAJERO",
        "Pago",
        $ids[0],
        "Liquidación del cajero " . $cajero["usuario"],
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
        "Location: liquidacion_confirmada_cajero.php?" .
        http_build_query([
            "id_cajero" => $id_cajero,
            "fecha" => $fecha
        ])
    );

    exit;

} catch (Exception $e) {

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

    echo '<a href="liquidacion_diaria_cajero.php">
            Volver a liquidaciones
          </a>';
}
?>