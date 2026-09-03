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
    <title>Panadería - Iniciar sesión</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login {
            background: white;
            width: 320px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.15);
        }

        h1 {
            text-align: center;
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            cursor: pointer;
        }

        .error {
            color: #b00020;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="login">

    <h1>Iniciar sesión</h1>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="autenticar.php" method="POST">

        <label for="usuario">Usuario</label>
        <input
            type="text"
            id="usuario"
            name="usuario"
            maxlength="32"
            required
            autofocus
        >

        <label for="contrasenia">Contraseña</label>
        <input
            type="password"
            id="contrasenia"
            name="contrasenia"
            required
        >

        <button type="submit">Ingresar</button>

    </form>

</div>

</body>
</html>