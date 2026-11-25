<?php
require_once __DIR__ . "/seguridad.php";
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/registrar_auditoria.php";

const LOGIN_MAX_INTENTOS_USUARIO = 5;   // por usuario, en la ventana
const LOGIN_MAX_INTENTOS_IP      = 20;  // por IP, en la ventana
const LOGIN_VENTANA_MINUTOS      = 15;

function loginError(string $mensaje): void
{
    header("Location: login.php?error=" . urlencode($mensaje));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$usuario     = trim($_POST["usuario"] ?? "");
$contrasenia = $_POST["contrasenia"] ?? "";
$ip          = ipCliente();

if ($usuario === "" || $contrasenia === "") {
    loginError("Completá todos los campos.");
}

// ---------- Bloqueo temporal por intentos fallidos ----------
$stmt = $conexion->prepare(
    "SELECT
        SUM(usuario = :u) AS por_usuario,
        SUM(ip = :ip)     AS por_ip
     FROM intento_login
     WHERE fecha_hora > (NOW() - INTERVAL " . LOGIN_VENTANA_MINUTOS . " MINUTE)"
);
$stmt->execute([":u" => $usuario, ":ip" => $ip]);
$cuenta = $stmt->fetch();

if ((int)($cuenta["por_usuario"] ?? 0) >= LOGIN_MAX_INTENTOS_USUARIO
    || (int)($cuenta["por_ip"] ?? 0) >= LOGIN_MAX_INTENTOS_IP) {
    loginError("Demasiados intentos fallidos. Esperá " . LOGIN_VENTANA_MINUTOS . " minutos e intentá de nuevo.");
}

function registrarIntentoFallido(PDO $conexion, string $usuario, string $ip): void
{
    $stmt = $conexion->prepare("INSERT INTO intento_login (usuario, ip) VALUES (?, ?)");
    $stmt->execute([mb_substr($usuario, 0, 32), $ip]);
}

// ---------- Buscar el usuario ----------
$stmt = $conexion->prepare(
    "SELECT id, usuario, contrasenia, activo, id_ROL
     FROM usuario
     WHERE usuario = :usuario
     LIMIT 1"
);
$stmt->execute([":usuario" => $usuario]);
$datosUsuario = $stmt->fetch();

// Para que el tiempo de respuesta no revele si el usuario existe,
// siempre se verifica una contraseña (contra un hash de relleno si no existe).
$hashRelleno = '$2y$10$6N3PqHpmAFQ5u.WmDbxV4.PUItN187xRKC22xWBiaBgYlZhPcen8q';
$hashAVerificar = $datosUsuario["contrasenia"] ?? $hashRelleno;
$contraseniaOk = password_verify($contrasenia, $hashAVerificar);

// Usuario inexistente
if (!$datosUsuario) {
    registrarIntentoFallido($conexion, $usuario, $ip);
    registrarAuditoria(
        $conexion, null, "INICIO DE SESIÓN FALLIDO", "Usuario", null,
        "Intento de inicio de sesión con un usuario inexistente: " . $usuario
    );
    loginError("Usuario o contraseña incorrectos.");
}

// Contraseña incorrecta
if (!$contraseniaOk) {
    registrarIntentoFallido($conexion, $usuario, $ip);
    registrarAuditoria(
        $conexion, (int)$datosUsuario["id"], "INICIO DE SESIÓN FALLIDO", "Usuario",
        (int)$datosUsuario["id"],
        "Intento de inicio de sesión fallido para el usuario: " . $datosUsuario["usuario"]
    );
    loginError("Usuario o contraseña incorrectos.");
}

// Usuario inactivo (solo se informa si la contraseña era correcta,
// para no revelar qué usuarios existen)
if ((int)$datosUsuario["activo"] !== 1) {
    registrarAuditoria(
        $conexion, (int)$datosUsuario["id"], "INICIO DE SESIÓN FALLIDO", "Usuario",
        (int)$datosUsuario["id"],
        "Intento de inicio de sesión con el usuario inactivo: " . $datosUsuario["usuario"],
        "Activo", "Inactivo"
    );
    loginError("El usuario está inactivo. Consultá con el administrador.");
}

// ---------- Inicio de sesión correcto ----------
$conexion->prepare("DELETE FROM intento_login WHERE usuario = ?")->execute([$usuario]);

// Actualiza el hash si el algoritmo por defecto de PHP mejoró
if (password_needs_rehash($datosUsuario["contrasenia"], PASSWORD_DEFAULT)) {
    $conexion->prepare("UPDATE usuario SET contrasenia = ? WHERE id = ?")
        ->execute([password_hash($contrasenia, PASSWORD_DEFAULT), $datosUsuario["id"]]);
}

session_regenerate_id(true);
$_SESSION["id_usuario"]       = (int)$datosUsuario["id"];
$_SESSION["usuario"]          = $datosUsuario["usuario"];
$_SESSION["id_ROL"]           = $datosUsuario["id_ROL"];
$_SESSION["ultima_actividad"] = time();
unset($_SESSION["csrf_token"]); // token nuevo para la sesión autenticada

registrarAuditoria(
    $conexion, (int)$datosUsuario["id"], "INICIO DE SESIÓN", "Usuario",
    (int)$datosUsuario["id"],
    "El usuario " . $datosUsuario["usuario"] . " inició sesión correctamente",
    "-", "Sesión iniciada"
);

header("Location: index.php");
exit;
