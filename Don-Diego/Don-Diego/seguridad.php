<?php
// =====================================================
// SEGURIDAD COMÚN: sesión, cabeceras, CSRF y timeout.
// Se incluye al principio de cada página (reemplaza a session_start()).
// =====================================================

const SESION_INACTIVIDAD_MAX = 1800; // 30 minutos

if (session_status() === PHP_SESSION_NONE) {
    $https = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
    session_set_cookie_params([
        "lifetime" => 0,
        "path"     => "/",
        "secure"   => $https,
        "httponly" => true,
        "samesite" => "Lax",
    ]);
    session_start();
}

if (!headers_sent()) {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: same-origin");
}

// Cierre automático por inactividad
if (isset($_SESSION["id_usuario"])) {
    $ahora = time();
    if (isset($_SESSION["ultima_actividad"])
        && ($ahora - (int)$_SESSION["ultima_actividad"]) > SESION_INACTIVIDAD_MAX) {
        $_SESSION = [];
        session_destroy();
        header("Location: login.php?error=" . urlencode("Tu sesión expiró por inactividad."));
        exit;
    }
    $_SESSION["ultima_actividad"] = $ahora;

    // Si un administrador desactivó al usuario, la sesión abierta deja de servir.
    require_once __DIR__ . "/conexion.php";
    $stmtActivo = $conexion->prepare("SELECT activo FROM usuario WHERE id = ?");
    $stmtActivo->execute([(int)$_SESSION["id_usuario"]]);
    if ((int)$stmtActivo->fetchColumn() !== 1) {
        $_SESSION = [];
        session_destroy();
        header("Location: login.php?error=" . urlencode("Tu usuario fue desactivado."));
        exit;
    }
}

// ---------- CSRF ----------
function csrfToken(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function csrfCampo(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrfToken(), ENT_QUOTES, "UTF-8") . '">';
}

function csrfVerificar(): void
{
    $enviado = $_POST["csrf_token"] ?? "";
    $valido  = $_SESSION["csrf_token"] ?? "";

    if (is_string($enviado) && $valido !== "" && hash_equals($valido, $enviado)) {
        return;
    }

    if (basename($_SERVER["SCRIPT_NAME"]) === "autenticar.php") {
        header("Location: login.php?error=" . urlencode("El formulario expiró. Intentá de nuevo."));
        exit;
    }

    http_response_code(419);
    exit("La página expiró o la solicitud no es válida. Volvé atrás, recargá y reintentá.");
}

// Todo POST debe traer un token válido.
if (($_SERVER["REQUEST_METHOD"] ?? "") === "POST") {
    csrfVerificar();
}

// ---------- Utilidades ----------
function h($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

function ipCliente(): string
{
    return substr((string)($_SERVER["REMOTE_ADDR"] ?? ""), 0, 45);
}
