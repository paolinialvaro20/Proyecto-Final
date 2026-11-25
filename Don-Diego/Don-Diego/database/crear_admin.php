<?php
// =====================================================
// Crea el PRIMER administrador. Solo se ejecuta por consola:
//   php database/crear_admin.php
// La contraseña se pide por teclado (no queda en el código ni en el historial).
// =====================================================
if (PHP_SAPI !== "cli") {
    http_response_code(403);
    exit("Este script solo se puede ejecutar desde la consola.");
}

require_once __DIR__ . "/../conexion.php";

echo "Nombre de usuario: ";
$usuario = trim((string)fgets(STDIN));
echo "Contraseña (mínimo 8 caracteres): ";
$contrasenia = rtrim((string)fgets(STDIN), "\r\n");

if ($usuario === "" || mb_strlen($usuario) > 32 || strlen($contrasenia) < 8) {
    exit("Datos no válidos.\n");
}

$idRol = $conexion->query("SELECT id FROM rol WHERE LOWER(TRIM(nombre)) = 'administrador' LIMIT 1")->fetchColumn();
if ($idRol === false) {
    exit("No existe el rol Administrador en la tabla rol.\n");
}

$stmt = $conexion->prepare(
    "INSERT INTO usuario (usuario, contrasenia, activo, id_ROL) VALUES (?, ?, 1, ?)"
);
$stmt->execute([$usuario, password_hash($contrasenia, PASSWORD_DEFAULT), $idRol]);

echo "Administrador creado correctamente.\n";
