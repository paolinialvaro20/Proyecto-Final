<?php

if (isset($_SESSION["id_usuario"])) {

    $sqlUsuario = "SELECT usuario
                   FROM usuario
                   WHERE id = ?";

    $stmtUsuario = $conexion->prepare($sqlUsuario);
    $stmtUsuario->execute([$_SESSION["id_usuario"]]);

    $usuarioLogueado = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

    if ($usuarioLogueado) {
        ?>

        <div style="
            position: fixed;
            bottom: 10px;
            right: 15px;
        ">
            Usuario:
            <?= htmlspecialchars($usuarioLogueado["usuario"]) ?>
        </div>

        <?php
    }
}
?>