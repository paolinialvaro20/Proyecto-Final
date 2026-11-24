<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="5;url=login.php">
<title>¡Hasta la próxima! · Don Diego</title>
<style>
* { box-sizing:border-box; }
body { margin:0; min-height:100vh; min-height:100svh; display:flex; align-items:center; justify-content:center; padding:28px 20px; font-family:Arial,sans-serif; color:#392c25; background:linear-gradient(#14191dcc,#14191dcc),url('fondo-login.jpg') center / cover fixed; }
.despedida { width:100%; max-width:460px; text-align:center; background:#fffaf3; border:1px solid #e4d3bb; border-radius:28px; padding:36px 40px 28px; box-shadow:0 28px 90px #0006; animation:entrada .65s ease both; }
.marca { display:block; width:190px; height:190px; max-width:70%; object-fit:contain; margin:0 auto 26px; border-radius:50%; box-shadow:0 8px 24px #5c39251a; }
.etiqueta { display:inline-flex; align-items:center; gap:7px; padding:7px 12px; border-radius:30px; background:#eaf0e6; color:#516c42; font-size:11px; letter-spacing:.3px; }
.etiqueta svg { width:14px; height:14px; }
h1 { font-family:Georgia,serif; font-weight:400; font-size:38px; letter-spacing:-1px; line-height:1.15; margin:22px 0 14px; }
.mensaje { color:#897462; font-size:14px; line-height:1.75; margin:0; }
.linea { height:1px; background:#e9ddcc; width:42px; margin:24px auto; }
.volver { display:block; width:100%; padding:14px 18px; border-radius:12px; background:#382a23; color:#fff7ec; font-size:13px; font-weight:600; text-decoration:none; transition:background .2s; }
.volver:hover { background:#62432e; }
.volver:focus-visible { outline:3px solid #b88345; outline-offset:4px; }
.aviso { margin:18px 0 10px; color:#9b8774; font-size:11px; }
.pista { width:90px; height:3px; border-radius:5px; background:#eadfce; margin:0 auto; overflow:hidden; }
.barra { height:100%; width:100%; background:#bd8a4d; transform-origin:left; animation:tiempo 5s linear both; }
.firma { display:block; margin-top:25px; color:#aa9380; font-size:9px; letter-spacing:2px; text-transform:uppercase; }
@keyframes entrada { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
@keyframes tiempo { from { transform:scaleX(0); } to { transform:scaleX(1); } }
@media(max-width:480px) { .despedida { padding:28px 26px 24px; border-radius:22px; } h1 { font-size:32px; } .marca { width:160px; height:160px; margin-bottom:22px; } }
@media(prefers-reduced-motion:reduce) { .despedida,.barra { animation:none; } }
</style>
    <link rel="stylesheet" href="tema-don-diego.css?v=2">
<link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
</head>
<body>
<main class="despedida">
    <img class="marca" src="logo-despedida.jpg" alt="Don Diego · Panadería · Calidad y sabor" width="190" height="190">
    <span class="etiqueta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Sesión cerrada correctamente</span>
    <h1>¡Hasta la próxima!</h1>
    <p class="mensaje">Gracias por ser parte de Don Diego.<br>Te esperamos para un nuevo día.</p>
    <div class="linea" aria-hidden="true"></div>
    <a class="volver" href="login.php">Volver al inicio de sesión <span aria-hidden="true">→</span></a>
    <p class="aviso">En unos segundos volverás al login automáticamente.</p>
    <div class="pista" aria-hidden="true"><div class="barra"></div></div>
    <span class="firma">Don Diego · Calidad &amp; sabor</span>
</main>
</body>
</html>
