<?php
$servidor = "localhost";
$usuarioBD = "root";
$contraseniaBD = "";
$baseDatos = "panaderia_deudas";

try {
    $conexion = new PDO(
        "mysql:host=$servidor;dbname=$baseDatos;charset=utf8mb4",
        $usuarioBD,
        $contraseniaBD
    );

    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$conexion->exec(
    "SET SESSION sql_mode = CONCAT_WS(',', @@SESSION.sql_mode, 'STRICT_TRANS_TABLES')"
);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
