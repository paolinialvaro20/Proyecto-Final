<?php
require_once __DIR__ . '/permisos.php';
require_once __DIR__ . '/dd_ui.php';
$rolUsuario = obtenerRolUsuario($conexion, $_SESSION['id_usuario']);
$usuarioPuedeAdministrar = usuarioPuedeAdministrar($conexion, $_SESSION['id_usuario']);
$usuarioPuedeGestionarFacturas = usuarioPuedeGestionarFacturas($conexion, $_SESSION['id_usuario']);
$usuarioEsCajero = in_array($rolUsuario, ['cajero', 'cajera'], true);
$usuarioEsRepartidor = $rolUsuario === 'repartidor';
$ddPagina = basename($_SERVER['SCRIPT_NAME']);
$ddNombre = (string)($_SESSION['usuario'] ?? 'Usuario');
$ddIniciales = strtoupper(substr($ddNombre, 0, 2));
$ddGrupos = [
    'Facturas' => ['icono'=>'factura', 'enlaces'=>[
        ['facturas.php','Listar facturas'],
        ...($usuarioPuedeGestionarFacturas ? [['agregar_factura.php','Ingresar factura'],['editar_factura.php','Editar factura']] : []),
        ['cambiar_estado_factura.php','Cambiar estado'],
        ['listar_observaciones.php','Listar observaciones'],['agregar_observaciones.php','Agregar observaciones']
    ]],
    'Pagos' => ['icono'=>'pago', 'enlaces'=>[['listar_pagos.php','Listar pagos'],['registrar_pago.php','Registrar pago']]],
];
if (!$usuarioEsCajero && !$usuarioEsRepartidor) {
    $ddGrupos['Usuarios'] = ['icono'=>'usuarios','enlaces'=>[
        ['listar_usuario.php','Listar usuarios'],
        ...($usuarioPuedeAdministrar ? [['crear_usuario.php','Nuevo usuario'],['editar_usuario.php','Editar usuario'],['cambiar_estado_usuario.php','Cambiar estado de usuario']] : []),
        ['listar_roles.php','Listar roles'],
        ...($usuarioPuedeAdministrar ? [['crear_rol_usuario.php','Nuevo rol'],['editar_rol_usuario.php','Editar rol'],['eliminar_rol.php','Eliminar rol']] : [])
    ]];
}
$ddGrupos['Liquidaciones'] = ['icono'=>'liquidacion','enlaces'=>[
    ...(!$usuarioEsCajero ? [['liquidacion_diaria.php','Liquidación de repartidor']] : []),
    ...(!$usuarioEsRepartidor ? [['liquidacion_diaria_cajero.php','Liquidación de cajero']] : [])
]];
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
            <?php if ($usuarioPuedeAdministrar): ?><a class="dd-link <?= $ddPagina === 'auditoria.php' ? 'active' : '' ?>" href="auditoria.php"><?= ddIcono('auditoria') ?> Auditoría</a><?php endif; ?>
        </div>
        <div class="dd-account">
            <span class="dd-avatar" aria-hidden="true"><?= ddEsc($ddIniciales) ?></span>
            <details class="dd-drop dd-user"><summary><span><?= ddEsc($ddNombre) ?><small><?= ddEsc(ucfirst($rolUsuario ?? 'Usuario')) ?></small></span></summary><div class="dd-dropdown"><a href="cambiar_contraseña.php">Cambiar contraseña</a></div></details>
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
