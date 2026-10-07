<?php
session_start();
require_once "conexion.php";

// Verificar que haya un usuario logueado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$error = "";

// Valores de los campos
$id_factura_seleccionada = "";
$detalle_ingresado = "";

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_factura = $_POST["id_factura"] ?? "";
    $detalle = trim($_POST["detalle"] ?? "");

    // Guardar los valores por si ocurre un error
    $id_factura_seleccionada = $id_factura;
    $detalle_ingresado = $detalle;

    if ($id_factura === "" || !is_numeric($id_factura)) {
        $error = "Debés seleccionar una factura.";
    } elseif ($detalle === "") {
        $error = "Debés ingresar una observación.";

    } else {

        try {
            // Verificar que la factura exista
            $sql = "SELECT id, numero_serie FROM factura WHERE id = :id";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([":id" => $id_factura]);
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$factura) {
                $error = "La factura seleccionada no existe.";

            } else {
                // Obtener fecha y hora actual del sistema
                $fecha_hora = date("Y-m-d H:i:s");

                // Insertar la observación
                $sql = "INSERT INTO observacion(detalle, fecha_hora, id_FACTURA_PENDIENTE, id_USUARIO)
                        VALUES(:detalle, :fecha_hora, :id_factura, :id_usuario)";

                $stmt = $conexion->prepare($sql);
                $stmt->execute([ ":detalle" => $detalle, ":fecha_hora" => $fecha_hora, ":id_factura" => $id_factura, ":id_usuario" => $_SESSION["id_usuario"]]);
                $mensaje = "Observación agregada correctamente.";

                // Limpiar los campos después de guardar correctamente
                $id_factura_seleccionada = "";
                $detalle_ingresado = "";
            }
            
        } catch (PDOException $e) {
            $error = "Error al guardar la observación.";
        }
    }
}

// Obtener las facturas
try {
    $sql = "SELECT id, numero_serie, monto, estado FROM factura ORDER BY id DESC";

    $stmt = $conexion->query($sql);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $facturas = [];
    $error = "No se pudieron cargar las facturas.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar observaciones</title>
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
            <div class="col-md-8 col-lg-7">
                <div class="card shadow">

                    <!-- Encabezado -->
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-chat-left-text-fill"></i> Agregar observación</h4>
                    </div>

                    <div class="card-body">
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

                        <form method="POST">
                            <!-- Factura -->
                            <div class="mb-3">
                                <label for="id_factura" class="form-label"><i class="bi bi-receipt"></i> Factura:</label>
                                <select name="id_factura" id="id_factura" class="form-select" required>
                                    <option value="">-- Seleccionar factura --</option>

                                    <?php foreach ($facturas as $factura): ?>
                                        <option
                                            value="<?= htmlspecialchars($factura["id"]) ?>"
                                            <?= ($id_factura_seleccionada == $factura["id"]) ? "selected" : "" ?>
                                        >
                                            <?= htmlspecialchars($factura["numero_serie"]) ?>
                                            -
                                            $<?= htmlspecialchars($factura["monto"]) ?>
                                            -
                                            <?= htmlspecialchars($factura["estado"]) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <!-- Observación -->
                            <div class="mb-4">
                                <label for="detalle" class="form-label"><i class="bi bi-pencil-square"></i> Observación:</label>
                                <textarea id="detalle" name="detalle" class="form-control" rows="5" maxlength="255"
                                    placeholder="Escriba la observación..." required
                                ><?= htmlspecialchars($detalle_ingresado) ?></textarea>

                                <div class="form-text">Máximo 255 caracteres.</div>
                            </div>

                            <!-- Botón -->
                            <div class="d-flex justify-content-center gap-2">
                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-plus-circle-fill"></i> Agregar observación</button>
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