<?php

session_start();

require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "menu.php";


// =====================================================
// RECIBIR DATOS DE LA LIQUIDACIÓN
// =====================================================

$id_repartidor = $_GET["id_repartidor"] ?? "";
$fecha = $_GET["fecha"] ?? "";
$total = $_GET["total"] ?? "0";


// =====================================================
// VALIDAR REPARTIDOR
// =====================================================

$repartidor = null;

if ($id_repartidor !== "" && is_numeric($id_repartidor)) {

    try {

        $sql = "SELECT id, usuario
                FROM usuario
                WHERE id = :id";

        $stmt = $conexion->prepare($sql);

        $stmt->execute([
            ":id" => $id_repartidor
        ]);

        $repartidor = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {

        $repartidor = null;
    }
}


// =====================================================
// SI NO EXISTE EL REPARTIDOR
// =====================================================

if (!$repartidor) {

    die("No se pudo obtener la información del repartidor.");
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Liquidación confirmada</title>

    <link
        rel="stylesheet"
        href="estilo.css"
    >

</head>


<body>


<h1>
    Liquidación confirmada
</h1>


<hr>


<h2>
    La liquidación fue realizada correctamente.
</h2>


<p>

    <strong>
        Repartidor:
    </strong>

    <?= htmlspecialchars($repartidor["usuario"]) ?>

</p>


<p>

    <strong>
        Fecha:
    </strong>

    <?= htmlspecialchars($fecha) ?>

</p>


<p>

    <strong>
        Total liquidado:
    </strong>

    $<?= number_format(
        (float)$total,
        2,
        ",",
        "."
    ) ?>

</p>


<hr>


<p>

    Los pagos correspondientes a esta liquidación
    fueron marcados como

    <strong>
        LIQUIDADOS.
    </strong>

</p>


<p>

    Estos pagos ya no aparecerán nuevamente
    entre los pagos pendientes de liquidación.

</p>


<br>


<a
    href="liquidacion_diaria.php?id_repartidor=<?= urlencode($id_repartidor) ?>&fecha=<?= urlencode($fecha) ?>"
>
    Volver a liquidación diaria
</a>


&nbsp; | &nbsp;


<a href="auditoria.php">
    Ver auditoría
</a>


&nbsp; | &nbsp;


<a href="index.php">
    Volver al inicio
</a>


</body>

</html>