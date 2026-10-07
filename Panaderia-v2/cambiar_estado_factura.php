<?php
session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

require_once "conexion.php";
$mensaje = "";
$error = "";
$id_factura_seleccionada = "";
$estado_seleccionado = "";

/* =========================================================
   CAMBIAR ESTADO DE LA FACTURA
   ========================================================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_factura = $_POST["id_factura"] ?? "";
    $estado = $_POST["estado"] ?? "";
    $id_factura_seleccionada = $id_factura;
    $estado_seleccionado = $estado;
    $estados_validos = ["Pendiente", "Cobrado", "Anulada"];

    /* Verificar factura */
    if ($id_factura === "" || !is_numeric($id_factura)) {
        $error = "Debés seleccionar una factura.";
    } elseif (!in_array($estado, $estados_validos, true)) {
        $error = "El estado seleccionado no es válido.";

    } else {

        try {
            /* Buscar la factura */
            $sql = "SELECT id, estado FROM factura WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([":id" => $id_factura]);
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$factura) {
                $error = "La factura seleccionada no existe.";

            } else {
                /* =====================================================
                   SI LA FACTURA PASA A COBRADO
                   SE GUARDA LA FECHA Y HORA ACTUAL DE MYSQL
                   ===================================================== */
                if ($estado === "Cobrado") {
                    $sql = "UPDATE factura SET estado = :estado, fecha_cobro = NOW() WHERE id = :id";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([ ":estado" => $estado, ":id" => $id_factura]);
                }

                /* =====================================================
                   SI PASA A PENDIENTE O ANULADA
                   SE ELIMINA LA FECHA DE COBRO
                   ===================================================== */
                else {
                    $sql = "UPDATE factura SET estado = :estado, fecha_cobro = NULL WHERE id = :id";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([":estado" => $estado, ":id" => $id_factura]);
                }
                /* Verificar que realmente se modificó */
                if ($stmt->rowCount() > 0) {
                    $mensaje = "Estado de la factura actualizado correctamente.";

                } else {
                    /*
                     * rowCount() puede devolver 0 si el valor ya era
                     * el mismo. De todas formas, la operación pudo
                     * ejecutarse correctamente.
                     */
                    $mensaje = "Estado de la factura actualizado correctamente.";
                }
                /* Limpiar formulario después de actualizar */
                $id_factura_seleccionada = "";
                $estado_seleccionado = "";
            }

        } catch (PDOException $e) {
            $error = "Error al actualizar la factura: " . $e->getMessage();
        }
    }
}

/* =========================================================
   OBTENER LAS FACTURAS
   INNER JOIN CON USUARIO
   ========================================================= */
try {
    $sql = "SELECT factura.id, factura.numero_serie, factura.monto, factura.fecha_registro, factura.fecha_cobro, factura.estado, usuario.usuario AS nombre_usuario
            FROM factura
            INNER JOIN usuario ON factura.id_usuario_registro = usuario.id
            ORDER BY factura.id DESC";

    $stmt = $conexion->query($sql);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $facturas = [];
    $error = "Error al obtener las facturas: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar estado de factura</title>
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

        <!-- =====================================================
             CARD - CAMBIAR ESTADO
             ===================================================== -->
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow mb-5">

                    <!-- Encabezado -->
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-arrow-repeat"></i> Cambiar estado de factura</h4>
                    </div>

                    <div class="card-body">
                        <!-- Mensaje de éxito -->
                        <?php if ($mensaje !== ""): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill"></i>
                                <?= htmlspecialchars($mensaje) ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Mensaje de error -->
                        <?php if ($error !== ""): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle-fill"></i>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Información -->
                        <div class="alert alert-info"><i class="bi bi-info-circle-fill"></i>
                            Seleccione una factura y el nuevo estado
                            que desea asignarle.
                        </div>

                        <form method="POST" action="cambiar_estado_factura.php">
                        
                            <!-- Factura -->
                            <div class="mb-4">
                                <label for="id_factura" class="form-label"><i class="bi bi-receipt"></i> Factura</label>
                                <select name="id_factura" id="id_factura" class="form-select" required>
                                    <option value="">Seleccionar factura</option>

                                    <?php foreach ($facturas as $factura): ?>
                                        <option value="<?= htmlspecialchars($factura["id"]) ?>"
                                            <?= ($id_factura_seleccionada == $factura["id"])
                                                ? "selected"
                                                : "" ?>
                                        >
                                            <?= htmlspecialchars($factura["numero_serie"]) ?>
                                            -
                                            $<?= htmlspecialchars($factura["monto"]) ?>
                                            -
                                            <?= htmlspecialchars($factura["estado"]) ?>
                                            -
                                            Registrada por:
                                            <?= htmlspecialchars($factura["nombre_usuario"]) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <!-- Nuevo estado -->
                            <div class="mb-4">
                                <label for="estado" class="form-label"><i class="bi bi-clipboard-check"></i> Nuevo estado</label>
                                <select name="estado" id="estado" class="form-select" required>
                                    <option value="">Seleccionar estado</option>
                                    <option value="Pendiente"
                                        <?= ($estado_seleccionado === "Pendiente")
                                            ? "selected"
                                            : "" ?>
                                    >
                                        Pendiente
                                    </option>
                                    <option value="Cobrado"
                                        <?= ($estado_seleccionado === "Cobrado")
                                            ? "selected"
                                            : "" ?>
                                    >
                                        Cobrado
                                    </option>
                                    <option value="Anulada"
                                        <?= ($estado_seleccionado === "Anulada")
                                            ? "selected"
                                            : "" ?>
                                    >
                                        Anulada
                                    </option>

                                </select>
                            </div>

                            <!-- Botón -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-arrow-repeat"></i> Cambiar estado</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- JavaScript de Bootstrap 5.3.8 -->
    <script src="js/bootstrap-5.3.8/js/bootstrap.bundle.min.js"></script>
</body>
</html>