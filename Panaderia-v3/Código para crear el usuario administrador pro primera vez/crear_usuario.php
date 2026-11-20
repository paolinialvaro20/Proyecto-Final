<?php

require_once "conexion.php";

$usuario = "admin";
$contrasenia = "123456";
$id_ROL = 1;

$hash = password_hash($contrasenia, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuario
        (usuario, contrasenia, activo, id_ROL)
        VALUES (:usuario, :contrasenia, 1, :id_ROL)";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ":usuario" => $usuario,
    ":contrasenia" => $hash,
    ":id_ROL" => $id_ROL
]);

echo "Usuario creado correctamente.";