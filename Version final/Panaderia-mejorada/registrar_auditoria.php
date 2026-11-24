<?php
// =====================================================
// FUNCIÓN PARA REGISTRAR ACCIONES EN LA AUDITORÍA
// - $idUsuario puede ser null (intentos con usuarios inexistentes).
// - Si falla el registro, se deja en el log y NO se rompe la operación
//   que se estaba realizando (por ejemplo, el inicio de sesión).
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
    $ip = substr((string)($_SERVER["REMOTE_ADDR"] ?? ""), 0, 45);

    try {
        $stmt = $conexion->prepare(
            "INSERT INTO auditoria
                (id_usuario, accion, entidad, id_entidad, descripcion,
                 valor_anterior, valor_nuevo, ip)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $idUsuario,
            $accion,
            $entidad,
            $idEntidad,
            mb_substr((string)$descripcion, 0, 255),
            $valorAnterior,
            $valorNuevo,
            $ip,
        ]);
    } catch (PDOException $e) {
        error_log("No se pudo registrar la auditoría: " . $e->getMessage());
        // Dentro de una transacción de negocio, hay que dejar que falle:
        if ($conexion->inTransaction()) {
            throw $e;
        }
    }
}
