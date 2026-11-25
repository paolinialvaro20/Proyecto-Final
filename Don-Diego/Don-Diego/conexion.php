<?php
// =====================================================
// CONEXIÓN A LA BASE DE DATOS
// Los valores se pueden sobrescribir con variables de entorno
// (DB_HOST, DB_NAME, DB_USER, DB_PASS) sin tocar el código.
// =====================================================
$servidor      = getenv("DB_HOST") ?: "localhost";
$baseDatos     = getenv("DB_NAME") ?: "panaderia_deudas";
$usuarioBD     = getenv("DB_USER") ?: "root";
$contraseniaBD = getenv("DB_PASS") !== false ? getenv("DB_PASS") : "";

try {
    $conexion = new PDO(
        "mysql:host=$servidor;dbname=$baseDatos;charset=utf8mb4",
        $usuarioBD,
        $contraseniaBD,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // El detalle técnico va al log, nunca a la pantalla.
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    http_response_code(500);
    exit("No se pudo conectar con la base de datos. Avisá al administrador.");
}
