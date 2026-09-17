<?php
require_once "conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $numero_serie = trim($_POST["numero_serie"]);
    $monto = $_POST["monto"];

    $sql = "INSERT INTO factura
            (numero_serie, monto, fecha_registro, estado, id_USUARIO)
            VALUES (?, ?, NOW(), 'Pendiente', ?)";

    $stmt = $conexion->prepare($sql);

    // Usuario fijo para las pruebas
    $idUsuario = 1;

    if ($stmt->execute([$numero_serie, $monto, $idUsuario])) {
        $mensaje = "<div class='alert alert-success'>Felicidades. Factura agregada correctamente.</div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>Error al guardar la factura.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<title>Nueva factura</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4>Nueva factura</h4>

</div>

<div class="card-body">

<?= $mensaje ?>

<form method="post">

<div class="mb-3">

<label class="form-label">

Número de serie

</label>

<input
type="text"
name="numero_serie"
class="form-control"
maxlength="20"
required>

</div>

<div class="mb-3">

<label class="form-label">

Monto

</label>

<input
type="number"
name="monto"
step="0.01"
min="0"
class="form-control"
required>

</div>

<button
type="submit"
class="btn btn-success">

Guardar factura

</button>

<a
href="index.php"
class="btn btn-secondary">

Cancelar

</a>

</form>

</div>

</div>

</div>

</div>

</div>

</body>
</html>