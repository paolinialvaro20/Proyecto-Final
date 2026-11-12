<?php
session_start();

if (isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit;
}

$error = $_GET["error"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/estilo.css">

<style>
#dd-carga { display:none; position:fixed; inset:0; z-index:9999; isolation:isolate; background:#291e16; align-items:center; justify-content:center; padding:28px; box-sizing:border-box; font-family:Arial,sans-serif; }
#dd-carga::before { content:""; position:absolute; inset:0; z-index:-2; background:url('don-diego-fondo.jpg') center 32% / cover no-repeat; }
#dd-carga::after { content:""; position:absolute; inset:0; z-index:-1; background:linear-gradient(110deg,rgba(27,18,12,.82),rgba(41,27,17,.58)); }
html.dd-cargando #dd-carga { display:flex; }
html.dd-cargando #dd-login { visibility:hidden; }
html.dd-cargando body { overflow:hidden; }
.dd-bienvenida { display:grid; grid-template-columns:minmax(0,1fr) minmax(320px,440px); width:min(960px,100%); align-items:center; gap:80px; }
.dd-presentacion { color:#fff6e8; text-align:left; animation:dd-aparecer .65s ease both; }
.dd-etiqueta { display:inline-block; color:#f3d3a0; font-size:11px; font-weight:600; letter-spacing:3px; text-transform:uppercase; }
.dd-presentacion h1 { color:#fff6e8; font-family:Georgia,serif; font-size:clamp(40px,5.3vw,68px); font-weight:400; line-height:1.06; margin:24px 0; letter-spacing:-2px; }
.dd-presentacion h1 em { color:#edc58c; font-weight:400; }
.dd-presentacion p { color:#e0d1c0; font-size:15px; line-height:1.8; margin:0; }
.dd-linea { width:48px; height:2px; background:#d8aa67; margin:28px 0; }
.dd-carga-contenido { width:100%; box-sizing:border-box; padding:44px 36px 30px; text-align:center; color:#473121; background:#fffaf2; border:1px solid #e8d6b8; border-radius:24px; box-shadow:0 24px 80px #0006; animation:dd-aparecer .65s .1s ease both; }
#dd-carga img { display:block; width:270px; max-width:100%; height:auto; object-fit:contain; margin:0 auto 32px; border-radius:6px; }
.dd-bienvenido { display:block; font-family:Georgia,serif; font-size:29px; margin-bottom:10px; }
#dd-carga .dd-mensaje { margin:0 0 32px; font-size:13px; line-height:1.6; color:#8b7763; }
.dd-estado { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; font-size:12px; color:#79634e; }
.dd-estado p { margin:0; }
#dd-porcentaje { font-variant-numeric:tabular-nums; color:#916124; font-weight:700; }
.dd-pista { height:7px; border-radius:20px; background:#ebdfce; overflow:hidden; }
#dd-barra { position:relative; width:0; height:100%; border-radius:inherit; background:linear-gradient(90deg,#9e652b,#deb573); box-shadow:0 0 12px #dab17088; }
#dd-barra::after { content:""; position:absolute; inset:0; background:linear-gradient(100deg,transparent, #fff6,transparent); animation:dd-brillo 1.5s linear infinite; }
.dd-pie { display:block; margin-top:28px; border-top:1px solid #eadfce; padding-top:20px; font-size:10px; letter-spacing:1.7px; color:#a18d77; text-transform:uppercase; }
.dd-ubicacion { position:absolute; bottom:28px; left:0; right:0; text-align:center; font-size:10px; letter-spacing:2px; color:#ead8bf; text-transform:uppercase; }
@keyframes dd-aparecer { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
@keyframes dd-brillo { from { transform:translateX(-100%); } to { transform:translateX(100%); } }
@media(max-width:720px) { #dd-carga { padding:24px; } .dd-bienvenida { grid-template-columns:1fr; max-width:390px; gap:24px; } .dd-presentacion { text-align:center; } .dd-presentacion h1 { font-size:36px; margin:12px 0 0; letter-spacing:-1px; } .dd-presentacion p,.dd-linea { display:none; } .dd-etiqueta { font-size:9px; letter-spacing:2px; } .dd-carga-contenido { padding:30px 26px 24px; border-radius:20px; } #dd-carga img { width:230px; margin-bottom:24px; } .dd-ubicacion { bottom:15px; font-size:9px; } }
@media(max-height:650px) { .dd-ubicacion { display:none; } .dd-bienvenida { gap:18px; } .dd-carga-contenido { padding:22px 26px; } #dd-carga img { width:200px; margin-bottom:16px; } .dd-pie { margin-top:18px; padding-top:14px; } #dd-carga .dd-mensaje { margin-bottom:20px; } }
@media(prefers-reduced-motion:reduce) { .dd-presentacion,.dd-carga-contenido,#dd-barra::after { animation:none; } }
</style>
<script>
// El login sigue disponible si JavaScript está desactivado.
document.documentElement.classList.add('dd-cargando');
window.ddRespaldoCarga = setTimeout(function () {
    document.documentElement.classList.remove('dd-cargando');
    var contenido = document.getElementById('dd-login');
    if (contenido) contenido.inert = false;
}, 5000);
</script>

<style id="dd-fondo-login">
/* Fondo decorativo: no se superpone a los campos ni a los botones. */
body.bg-light, body {
    background: linear-gradient(rgba(14,18,22,.18), rgba(14,18,22,.18)), url('fondo-login.jpg') center center / cover no-repeat fixed !important;
    min-height:100vh;
}
#dd-login { width:100%; min-height:100vh; }
#dd-login .card, #dd-login .login {
    background:#fffaf5;
    border:1px solid rgba(230,211,185,.75) !important;
    box-shadow:0 20px 65px rgba(0,0,0,.38) !important;
    border-radius:20px;
}
#dd-login input { background-color:#fff; color:#212529; }
#dd-login input:focus { outline:2px solid #ad763d; outline-offset:2px; }
#dd-login .login { box-sizing:border-box; max-width:calc(100% - 32px); margin:24px auto; }
</style>
    <link rel="stylesheet" href="fondo-sistema.css?v=1">
    <link rel="stylesheet" href="tema-don-diego.css?v=2">
<link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
</head>
<body class="bg-light">
<div id="dd-carga" aria-label="Cargando Don Diego">
    <div class="dd-bienvenida">
        <div class="dd-presentacion">
            <span class="dd-etiqueta">Panadería &amp; confitería</span>
            <h1>La calidad<br>de <em>siempre.</em></h1>
            <div class="dd-linea"></div>
            <p>Bienvenido a Don Diego.<br>Un nuevo día comienza acá.</p>
        </div>
        <div class="dd-carga-contenido">
            <img src="logo-don-diego.png" alt="Panadería Don Diego" width="270">
            <span class="dd-bienvenido">¡Bienvenido!</span>
            <p class="dd-mensaje">Estamos preparando tu acceso<br>al sistema de gestión.</p>
            <div class="dd-estado"><p role="status">Cargando...</p><span id="dd-porcentaje" aria-hidden="true">0%</span></div>
            <div class="dd-pista" role="progressbar" aria-label="Carga del inicio de sesión" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="dd-progreso"><div id="dd-barra"></div></div>
            <span class="dd-pie">Calidad &amp; sabor · Don Diego</span>
        </div>
    </div>
    <span class="dd-ubicacion">Salto, Uruguay</span>
</div>
<div id="dd-login">
<script>document.getElementById('dd-login').inert = true;</script>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card border-0 shadow rounded-4 p-4 p-md-5"
             style="max-width: 420px; width: 100%;">

            <!-- Logo -->
            <div class="text-center mb-2">
                <img src="logo-don-diego.png" alt="Panadería" class="img-fluid" style="max-width: 170px;">
            </div>

            <!-- Nombre del sistema -->
            <div class="text-center mb-4">
                <h5 class="fw-bold mb-1">Sistema de gestión de pagos</h5>
                <p class="text-muted mb-0">Panadería</p>
            </div>

            <!-- Separador -->
            <hr class="mb-4">

            <!-- Título -->
            <h3 class="text-center fw-bold mb-4">Iniciar sesión</h3>

            <!-- Mensaje de error -->
            <?php if ($error !== ""): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form action="autenticar.php" method="POST">

                <!-- Usuario -->
                <div class="mb-3">
                    <label for="usuario" class="form-label fw-bold"><i class="bi bi-person"></i> Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="usuario" name="usuario" maxlength="32" required
                         autofocus placeholder="Ingrese su usuario">
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="mb-4">
                    <label for="contrasenia" class="form-label fw-bold"><i class="bi bi-lock"></i> Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="contrasenia" name="contrasenia" required 
                        placeholder="Ingrese su contraseña">
                    </div>
                </div>

                <!-- Botón -->
                <button type="submit" class="btn btn-dark w-100 py-2 fw-bold"><i class="bi bi-box-arrow-in-right"></i> Ingresar</button>
            </form>

            <!-- Pie -->
            <p class="text-center text-muted small mt-4 mb-0">
                Acceso exclusivo para usuarios del sistema
            </p>
        </div>
    </div>


    <?php include "pie_pagina.php"; ?>

</div>
<script>
(function () {
    var inicio = performance.now();
    var duracion = 2800; // Duración de la pantalla de bienvenida, en milisegundos.
    var barra = document.getElementById('dd-barra');
    var progreso = document.getElementById('dd-progreso');
    var porcentaje = document.getElementById('dd-porcentaje');
    function mostrarLogin() {
        clearTimeout(window.ddRespaldoCarga);
        document.documentElement.classList.remove('dd-cargando');
        document.getElementById('dd-login').inert = false;
        document.getElementById('dd-carga').hidden = true;
        var usuario = document.getElementById('usuario');
        if (usuario) usuario.focus({preventScroll:true});
    }
    function avanzar(ahora) {
        var valor = Math.min(100, Math.floor((ahora - inicio) / duracion * 100));
        barra.style.width = valor + '%';
        progreso.setAttribute('aria-valuenow', valor);
        porcentaje.textContent = valor + '%';
        if (valor < 100) requestAnimationFrame(avanzar);
        else setTimeout(mostrarLogin, 180);
    }
    requestAnimationFrame(avanzar);
})();
</script>
</body>
</html>

