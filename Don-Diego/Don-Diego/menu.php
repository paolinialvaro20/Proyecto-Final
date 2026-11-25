<?php
require_once __DIR__ . '/permisos.php';
require_once __DIR__ . '/dd_ui.php';
$ddUid = $_SESSION['id_usuario'];
$rolUsuario = obtenerRolUsuario($conexion, $ddUid);
$usuarioEsAdmin = usuarioPuedeAdministrar($conexion, $ddUid);
$ddPuede = static fn(string $permiso): bool => usuarioTienePermiso($conexion, $ddUid, $permiso);
$ddPagina = basename($_SERVER['SCRIPT_NAME']);
$ddNombre = (string)($_SESSION['usuario'] ?? 'Usuario');
$ddIniciales = strtoupper(substr($ddNombre, 0, 2));
// [archivo, texto, permiso requerido]  (null = cualquier usuario, '@admin' = solo Administrador)
$ddMenuDef = [
    'Facturas' => ['icono'=>'factura', 'enlaces'=>[
        ['facturas.php','Listar facturas','facturas.listar'],
        ['agregar_factura.php','Ingresar factura','facturas.crear'],
        ['editar_factura.php','Editar factura','facturas.editar'],
        ['cambiar_estado_factura.php','Cambiar estado','facturas.cambiar_estado'],
        ['listar_observaciones.php','Listar observaciones','observaciones.listar'],
        ['agregar_observaciones.php','Agregar observaciones','observaciones.agregar'],
    ]],
    'Pagos' => ['icono'=>'pago', 'enlaces'=>[
        ['listar_pagos.php','Listar pagos','pagos.listar'],
        ['registrar_pago.php','Registrar pago','pagos.registrar'],
    ]],
    'Usuarios' => ['icono'=>'usuarios', 'enlaces'=>[
        ['listar_usuario.php','Listar usuarios','usuarios.listar'],
        ['crear_usuario.php','Nuevo usuario','usuarios.crear'],
        ['editar_usuario.php','Editar usuario','usuarios.editar'],
        ['cambiar_estado_usuario.php','Cambiar estado de usuario','usuarios.cambiar_estado'],
        ['listar_roles.php','Listar roles','roles.listar'],
        ['crear_rol_usuario.php','Nuevo rol','roles.crear'],
        ['editar_rol_usuario.php','Editar rol','roles.editar'],
        ['eliminar_rol.php','Eliminar rol','roles.eliminar'],
    ]],
    'Liquidaciones' => ['icono'=>'liquidacion', 'enlaces'=>[
        ['listar_liquidacion.php','Listar liquidaciones','liquidaciones.listar'],
        ['liquidacion_diaria.php','Liquidación de repartidor','liquidaciones.repartidor'],
        ['liquidacion_diaria_cajero.php','Liquidación de cajero','liquidaciones.cajero'],
    ]],
];
$ddGrupos = [];
foreach ($ddMenuDef as $ddMEtiqueta => $ddMGrupo) {
    $ddMVisibles = [];
    foreach ($ddMGrupo['enlaces'] as [$ddMHref, $ddMTexto, $ddMPermiso]) {
        $ddMPermitido = $ddMPermiso === null
            || ($ddMPermiso === '@admin' ? $usuarioEsAdmin : $ddPuede($ddMPermiso));
        if ($ddMPermitido) { $ddMVisibles[] = [$ddMHref, $ddMTexto]; }
    }
    if ($ddMVisibles) { $ddGrupos[$ddMEtiqueta] = ['icono'=>$ddMGrupo['icono'], 'enlaces'=>$ddMVisibles]; }
}
?>
<nav class="dd-nav" aria-label="Navegación principal" id="dd-menu">
    <div class="dd-nav-inner">
        <a class="dd-logo" href="index.php" aria-label="Don Diego, inicio"><img src="logo-don-diego.png" alt="Don Diego · Panadería" width="177" height="48"></a>
        <button class="dd-mobile" type="button" aria-expanded="false" aria-controls="dd-enlaces" id="dd-menu-boton"><?= ddIcono('menu') ?> Menú</button>
        <div class="dd-links" id="dd-enlaces">
            <a class="dd-link <?= $ddPagina === 'index.php' ? 'active' : '' ?>" <?= $ddPagina === 'index.php' ? 'aria-current="page"' : '' ?> href="index.php"><?= ddIcono('inicio') ?> Inicio</a>
            <?php foreach ($ddGrupos as $ddEtiqueta => $ddGrupo): ?>
                <details class="dd-drop <?= in_array($ddPagina, array_column($ddGrupo['enlaces'], 0), true) ? 'active' : '' ?>">
                    <summary><?= ddIcono($ddGrupo['icono']) ?> <?= ddEsc($ddEtiqueta) ?></summary>
                    <div class="dd-dropdown">
                        <?php foreach ($ddGrupo['enlaces'] as [$ddHref,$ddTexto]): ?>
                            <a href="<?= ddEsc($ddHref) ?>" <?= $ddPagina === $ddHref ? 'aria-current="page"' : '' ?>><?= ddEsc($ddTexto) ?></a>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
            <?php if ($ddPuede('auditoria.ver')): ?><a class="dd-link <?= $ddPagina === 'auditoria.php' ? 'active' : '' ?>" href="auditoria.php"><?= ddIcono('auditoria') ?> Auditoría</a><?php endif; ?>
        </div>
        <div class="dd-account">
            <span class="dd-avatar" aria-hidden="true"><?= ddEsc($ddIniciales) ?></span>
            <details class="dd-drop dd-user"><summary><span><?= ddEsc($ddNombre) ?><small><?= ddEsc(ucfirst($rolUsuario ?? 'Usuario')) ?></small></span></summary><div class="dd-dropdown"><a href="cambiar_contrasenia.php">Cambiar contraseña</a></div></details>
            <a class="dd-logout" href="cerrar_sesion.php"><?= ddIcono('salir') ?> Cerrar sesión</a>
        </div>
    </div>
</nav>
<script>
(function(){
const menu=document.getElementById('dd-menu'), boton=document.getElementById('dd-menu-boton');
boton.addEventListener('click',function(){const abierto=menu.toggleAttribute('data-open');boton.setAttribute('aria-expanded',String(abierto));});
menu.querySelectorAll('details').forEach(function(actual){actual.addEventListener('toggle',function(){if(actual.open)menu.querySelectorAll('details').forEach(function(otro){if(otro!==actual)otro.open=false;});});});
document.addEventListener('click',function(e){if(!menu.contains(e.target))menu.querySelectorAll('details').forEach(function(d){d.open=false;});});
document.addEventListener('keydown',function(e){if(e.key==='Escape')menu.querySelectorAll('details').forEach(function(d){if(d.open){d.open=false;d.querySelector('summary').focus();}});});
})();
</script>
