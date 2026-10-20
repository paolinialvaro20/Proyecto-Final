<?php

// Conexión a la base de datos
require_once "conexion.php";


// ==========================================
// OBTENER LOS REGISTROS DE AUDITORÍA
// ==========================================

$sql = "SELECT 
            auditoria.id,
            usuario.usuario,
            auditoria.accion,
            auditoria.entidad,
            auditoria.id_entidad,
            auditoria.descripcion,
            auditoria.valor_anterior,
            auditoria.valor_nuevo,
            auditoria.fecha_hora
        FROM auditoria
        INNER JOIN usuario
            ON auditoria.id_usuario = usuario.id
        ORDER BY auditoria.fecha_hora DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute();

$registros = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Auditoría del Sistema</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 30px;
        }

        .contenedor {
            width: 95%;
            max-width: 1400px;
            margin: auto;
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .descripcion {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        .volver {
            display: inline-block;
            padding: 10px 18px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .volver:hover {
            background-color: #555;
        }

        .tabla-contenedor {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #333;
            color: white;
            padding: 12px;
            text-align: center;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        .accion {
            font-weight: bold;
        }

        .fecha {
            white-space: nowrap;
        }

        .sin-registros {
            text-align: center;
            padding: 30px;
            color: #666;
            font-size: 18px;
        }

    </style>

</head>


<body>

<div class="contenedor">

    <!-- Cambiá index.php si tu menú principal tiene otro nombre -->
    <a href="index.php" class="volver">
        ← Volver
    </a>


    <h1>Auditoría del Sistema</h1>

    <p class="descripcion">
        Registro de las acciones realizadas por los usuarios del sistema.
    </p>


    <?php if (count($registros) > 0): ?>


        <div class="tabla-contenedor">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Usuario</th>

                        <th>Acción</th>

                        <th>Entidad</th>

                        <th>ID Registro</th>

                        <th>Descripción</th>

                        <th>Valor anterior</th>

                        <th>Valor nuevo</th>

                        <th>Fecha y hora</th>

                    </tr>

                </thead>


                <tbody>


                <?php foreach ($registros as $registro): ?>


                    <tr>


                        <!-- ID DE AUDITORÍA -->

                        <td>

                            <?php
                            echo $registro["id"];
                            ?>

                        </td>


                        <!-- USUARIO -->

                        <td>

                            <?php
                            echo htmlspecialchars($registro["usuario"]);
                             ?>

                        </td>


                        <!-- ACCIÓN -->

                        <td class="accion">

                            <?php
                            echo htmlspecialchars($registro["accion"]);
                            ?>

                        </td>


                        <!-- ENTIDAD -->

                        <td>

                            <?php
                            echo htmlspecialchars($registro["entidad"]);
                            ?>

                        </td>


                        <!-- ID DEL REGISTRO MODIFICADO -->

                        <td>

                            <?php

                            if ($registro["id_entidad"] != null) {

                                echo $registro["id_entidad"];

                            } else {

                                echo "-";

                            }

                            ?>

                        </td>


                        <!-- DESCRIPCIÓN -->

                        <td>

                            <?php

                            if (!empty($registro["descripcion"])) {

                                echo htmlspecialchars(
                                    $registro["descripcion"]
                                );

                            } else {

                                echo "-";

                            }

                            ?>

                        </td>


                        <!-- VALOR ANTERIOR -->

                        <td>

                            <?php

                            if (!empty($registro["valor_anterior"])) {

                                echo htmlspecialchars(
                                    $registro["valor_anterior"]
                                );

                            } else {

                                echo "-";

                            }

                            ?>

                        </td>


                        <!-- VALOR NUEVO -->

                        <td>

                            <?php

                            if (!empty($registro["valor_nuevo"])) {

                                echo htmlspecialchars(
                                    $registro["valor_nuevo"]
                                );

                            } else {

                                echo "-";

                            }

                            ?>

                        </td>


                        <!-- FECHA Y HORA -->

                        <td class="fecha">

                            <?php

                            $fecha = new DateTime(
                                $registro["fecha_hora"]
                            );

                            echo $fecha->format(
                                "d/m/Y H:i:s"
                            );

                            ?>

                        </td>


                    </tr>


                <?php endforeach; ?>


                </tbody>

            </table>

        </div>


    <?php else: ?>


        <div class="sin-registros">

            Todavía no existen registros de auditoría.

        </div>


    <?php endif; ?>


</div>


</body>

</html>