<?php

require_once "conexion.php";

// Obtener usuarios junto con su rol
$sql = "SELECT 
            usuario.id,
            usuario.usuario,
            usuario.activo,
            rol.nombre AS rol
        FROM usuario
        INNER JOIN rol
            ON usuario.id_ROL = rol.id
        ORDER BY usuario.id";

$stmt = $conexion->prepare($sql);
$stmt->execute();

$usuarios = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Lista de usuarios</title>

</head>

<body>

<?php require_once "menu.php"; ?>


<h1>Lista de usuarios</h1>

<table border="1">

    <thead>

        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>

    </thead>

    <tbody>

        <?php foreach ($usuarios as $usuario): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($usuario["id"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($usuario["usuario"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($usuario["rol"]) ?>
                </td>

                <td>

                    <?php if ($usuario["activo"] == 1): ?>

                        Activo

                    <?php else: ?>

                        Inactivo

                    <?php endif; ?>

                </td>

                <td>

                    <a href="editar_usuario.php?id=<?= $usuario["id"] ?>">
                        Editar
                    </a>

                    <?php if ($usuario["activo"] == 1): ?>

                        |

                        <a href="eliminar_usuario.php?id=<?= $usuario["id"] ?>">
                            Desactivar
                        </a>

                    <?php endif; ?>

                </td>

            </tr>

        <?php endforeach; ?>

    </tbody>

</table>
<br>
<br>
<a href="crear_usuario.php">Crear Usuario</a>

</body>

</html>