<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit;
}

// Si inició sesión correctamente, ir directamente a facturas.php
header("Location: facturas.php");
exit;

?>