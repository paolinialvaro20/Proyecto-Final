<?php
// =====================================================
// Lista de permisos con casillas (checkbox), agrupada por sección.
// Se usa al crear y al editar un rol.
//
// $catalogo:        filas de catalogoPermisos()
// $idsMarcados:     IDs de permisos que aparecen tildados
// $soloLectura:     true = todas las casillas deshabilitadas
// $clavesEditables: null = todas editables; array = solo esas claves se pueden tocar
// =====================================================
function permisosCheckboxes(array $catalogo, array $idsMarcados, bool $soloLectura = false, ?array $clavesEditables = null): string
{
    $grupos = [];
    foreach ($catalogo as $p) {
        $grupos[$p["grupo"]][] = $p;
    }

    $marcados = array_map("intval", $idsMarcados);
    $html = "";

    foreach ($grupos as $nombreGrupo => $permisos) {
        $html .= '<fieldset class="border rounded p-3 mb-3">';
        $html .= '<legend class="float-none w-auto px-2 fs-6 fw-bold">' . h($nombreGrupo) . '</legend>';

        foreach ($permisos as $p) {
            $id = (int)$p["id"];
            $deshabilitado = $soloLectura
                || ($clavesEditables !== null && !in_array($p["clave"], $clavesEditables, true));

            $html .= '<div class="form-check mb-2">';
            $html .= '<input class="form-check-input" type="checkbox" name="permisos[]"'
                  . ' value="' . $id . '" id="permiso_' . $id . '"'
                  . (in_array($id, $marcados, true) ? " checked" : "")
                  . ($deshabilitado ? " disabled" : "") . '>';
            $html .= '<label class="form-check-label" for="permiso_' . $id . '">'
                  . '<strong>' . h($p["nombre"]) . '</strong>'
                  . '<small class="d-block text-muted">' . h($p["descripcion"]) . '</small>'
                  . '</label>';
            $html .= '</div>';
        }

        $html .= '</fieldset>';
    }

    return $html;
}
