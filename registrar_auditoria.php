<?php

// =====================================================
// FUNCIÓN PARA REGISTRAR ACCIONES EN LA AUDITORÍA
// =====================================================

function registrarAuditoria(
    $conexion,
    $idUsuario,
    $accion,
    $entidad,
    $idEntidad,
    $descripcion,
    $valorAnterior = null,
    $valorNuevo = null
) {

    // Consulta para insertar el movimiento
    $sql = "INSERT INTO auditoria (
                id_usuario,
                accion,
                entidad,
                id_entidad,
                descripcion,
                valor_anterior,
                valor_nuevo
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)";


    // Preparamos la consulta
    $stmt = $conexion->prepare($sql);


    // Ejecutamos la consulta enviando los datos
    $stmt->execute([
        $idUsuario,
        $accion,
        $entidad,
        $idEntidad,
        $descripcion,
        $valorAnterior,
        $valorNuevo
    ]);

}

?>