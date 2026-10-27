<?php
/**
 * Filtros reutilizables para el proyecto Panadería.
 * PHP 7.4+; no modifica datos ni necesita JavaScript.
 *
 * INTEGRACIÓN (en cada pantalla, después de cargar el arreglo desde la BD):
 *   require_once __DIR__ . '/filtros_proyecto.php';
 *   filtros_aplicar($usuarios, 'usuarios'); // listar_usuario.php
 *   filtros_aplicar($roles, 'roles');       // listar_roles.php
 *   filtros_aplicar($facturas, 'facturas'); // facturas.php
 *   filtros_aplicar($facturas, 'pagos');    // registrar_pago.php (facturas pendientes)
 *
 * En el HTML, justo antes de la tabla (o del selector de facturas):
 *   <?php filtros_formulario('usuarios'); ?> // cambiar por la sección correspondiente
 *
 * NOTA: facturas.php ya contiene filtros propios; si usás estos, conviene quitar
 * el formulario anterior para no duplicar los campos.
 */

function filtros_valor(string $clave): string {
    $v = $_GET['fil_' . $clave] ?? '';
    return is_string($v) ? trim($v) : '';
}
function filtros_esc($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function filtros_contiene($valor, string $busqueda): bool {
    if ($busqueda === '') return true;
    return mb_stripos((string)($valor ?? ''), $busqueda, 0, 'UTF-8') !== false;
}
function filtros_fecha($valor, string $desde, string $hasta): bool {
    if ($desde === '' && $hasta === '') return true;
    if (!$valor) return false;
    $fecha = substr((string)$valor, 0, 10);
    return ($desde === '' || $fecha >= $desde) && ($hasta === '' || $fecha <= $hasta);
}
function filtros_aplicar(array &$filas, string $seccion): void {
    if (!in_array($seccion, ['usuarios','roles','facturas','pagos'], true)) return;
    $filas = array_values(array_filter($filas, static function ($fila) use ($seccion) {
        if ($seccion === 'usuarios') {
            $estado = filtros_valor('estado');
            return filtros_contiene($fila['usuario'] ?? '', filtros_valor('usuario'))
                && filtros_contiene($fila['rol'] ?? '', filtros_valor('rol'))
                && ($estado === '' || (string)($fila['activo'] ?? '') === $estado);
        }
        if ($seccion === 'roles') {
            return filtros_contiene($fila['nombre'] ?? '', filtros_valor('rol'));
        }
        $serie = filtros_valor('serie');
        $monto = filtros_valor('monto');
        $usuario = filtros_valor('usuario');
        if (!filtros_contiene($fila['numero_serie'] ?? '', $serie)
            || !filtros_contiene($fila['usuario'] ?? ($fila['usuario_asignado'] ?? ''), $usuario)) return false;
        if ($monto !== '' && (!is_numeric($monto) || (float)($fila['monto'] ?? 0) != (float)$monto)) return false;
        if ($seccion === 'pagos') return true;
        $estado = filtros_valor('estado');
        return ($estado === '' || ($fila['estado'] ?? '') === $estado)
            && filtros_fecha($fila['fecha_registro'] ?? null, filtros_valor('registro_desde'), filtros_valor('registro_hasta'))
            && filtros_fecha($fila['fecha_cobro'] ?? null, filtros_valor('cobro_desde'), filtros_valor('cobro_hasta'));
    }));
}
function filtros_formulario(string $seccion): void {
    $campos = [
        'usuarios' => ['usuario'=>'Usuario', 'rol'=>'Rol'],
        'roles' => ['rol'=>'Nombre del rol'],
        'pagos' => ['serie'=>'Número de serie', 'monto'=>'Monto exacto', 'usuario'=>'Usuario asignado'],
        'facturas' => ['serie'=>'Número de serie', 'monto'=>'Monto exacto', 'usuario'=>'Usuario'],
    ];
    if (!isset($campos[$seccion])) return;
    echo '<form method="get" class="border rounded bg-light p-3 mb-3"><div class="row g-3">';
    foreach ($campos[$seccion] as $clave => $etiqueta) {
        echo '<div class="col-12 col-md-4"><label class="form-label" for="fil_' . $clave . '">' . filtros_esc($etiqueta) . '</label>';
        echo '<input class="form-control" type="' . ($clave === 'monto' ? 'number' : 'search') . '" ';
        if ($clave === 'monto') echo 'step="0.01" min="0" ';
        echo 'id="fil_' . $clave . '" name="fil_' . $clave . '" value="' . filtros_esc(filtros_valor($clave)) . '"></div>';
    }
    if ($seccion === 'usuarios' || $seccion === 'facturas') {
        $opciones = $seccion === 'usuarios' ? ['1'=>'Activo','0'=>'Inactivo'] : ['Pendiente'=>'Pendiente','Cobrado'=>'Cobrado','Anulada'=>'Anulada'];
        echo '<div class="col-12 col-md-4"><label class="form-label" for="fil_estado">Estado</label><select class="form-select" id="fil_estado" name="fil_estado"><option value="">Todos</option>';
        foreach ($opciones as $valor => $etiqueta) {
            echo '<option value="' . filtros_esc($valor) . '"' . (filtros_valor('estado') === (string)$valor ? ' selected' : '') . '>' . filtros_esc($etiqueta) . '</option>';
        }
        echo '</select></div>';
    }
    if ($seccion === 'facturas') {
        foreach (['registro_desde'=>'Registro desde','registro_hasta'=>'Registro hasta','cobro_desde'=>'Cobro desde','cobro_hasta'=>'Cobro hasta'] as $clave=>$etiqueta) {
            echo '<div class="col-12 col-md-3"><label class="form-label" for="fil_' . $clave . '">' . $etiqueta . '</label><input class="form-control" type="date" id="fil_' . $clave . '" name="fil_' . $clave . '" value="' . filtros_esc(filtros_valor($clave)) . '"></div>';
        }
    }
    echo '</div><div class="mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Buscar</button><a class="btn btn-outline-secondary" href="' . filtros_esc(strtok($_SERVER['REQUEST_URI'] ?? '', '?')) . '">Limpiar filtros</a></div></form>';
}
