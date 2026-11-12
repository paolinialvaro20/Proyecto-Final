<?php
session_start();
if (!isset($_SESSION['id_usuario'])) { header('Location: login.php'); exit; }
require_once __DIR__.'/conexion.php';
require_once __DIR__.'/permisos.php';
require_once __DIR__.'/dd_ui.php';
$ddId = (int)$_SESSION['id_usuario'];
$ddRol = obtenerRolUsuario($conexion, $ddId);
if ($ddRol === null) { header('Location: cerrar_sesion.php'); exit; }
$ddRepartidor = $ddRol === 'repartidor';
$ddPuedeEditar = usuarioPuedeGestionarFacturas($conexion, $ddId);
$ddFiltro = [
    'buscar'=>trim((string)($_GET['buscar'] ?? '')),
    'estado'=>trim((string)($_GET['estado'] ?? '')),
    'fecha'=>trim((string)($_GET['fecha'] ?? ''))
];
$ddErrores=[];
$ddEstados=['Pendiente','Cobrado','Anulada'];
if ($ddFiltro['estado'] !== '' && !in_array($ddFiltro['estado'],$ddEstados,true)) $ddErrores[]='Seleccioná un estado válido.';
if ($ddFiltro['fecha'] !== '') {
    $ddFecha=DateTime::createFromFormat('!Y-m-d',$ddFiltro['fecha']);
    if (!$ddFecha || $ddFecha->format('Y-m-d') !== $ddFiltro['fecha']) $ddErrores[]='La fecha ingresada no es válida.';
}
$ddDonde=$ddRepartidor ? ['f.id_usuario_asignado = ?'] : [];
$ddParametros=$ddRepartidor ? [$ddId] : [];
$ddAlcance=$ddDonde ? ' WHERE '.implode(' AND ',$ddDonde) : '';
$ddResumen=$conexion->prepare("SELECT COUNT(*) AS cantidad, COALESCE(SUM(f.monto),0) AS total FROM factura f".($ddAlcance ? $ddAlcance." AND f.estado = 'Pendiente'" : " WHERE f.estado = 'Pendiente'"));
$ddResumen->execute($ddParametros);
$ddPendientes=$ddResumen->fetch();
// Fechas calculadas en Uruguay, sin depender de la zona horaria del servidor.
$ddHoy=(new DateTimeImmutable('now',new DateTimeZone('America/Montevideo')))->format('Y-m-d');
$ddManana=(new DateTimeImmutable($ddHoy))->modify('+1 day')->format('Y-m-d');
$ddPagos=$conexion->prepare('SELECT COALESCE(SUM(monto),0) FROM pago WHERE fecha_hora >= ? AND fecha_hora < ?'.($ddRepartidor ? ' AND id_repartidor = ?' : ''));
$ddPagos->execute($ddRepartidor ? [$ddHoy,$ddManana,$ddId] : [$ddHoy,$ddManana]);
$ddPagosHoy=$ddPagos->fetchColumn();
if ($ddFiltro['buscar']!=='') {
    $ddDonde[]='(f.numero_serie LIKE ? OR u.usuario LIKE ? OR CAST(f.id AS CHAR) = ?)';
    $ddParametros[]='%'.$ddFiltro['buscar'].'%';$ddParametros[]='%'.$ddFiltro['buscar'].'%';$ddParametros[]=$ddFiltro['buscar'];
}
if ($ddFiltro['estado']!=='') { $ddDonde[]='f.estado = ?';$ddParametros[]=$ddFiltro['estado']; }
if ($ddFiltro['fecha']!=='' && !$ddErrores) {
    $ddDonde[]='f.fecha_registro >= ? AND f.fecha_registro < ?';
    $ddParametros[]=$ddFiltro['fecha'];$ddParametros[]=(new DateTimeImmutable($ddFiltro['fecha']))->modify('+1 day')->format('Y-m-d');
}
$ddDesde=' FROM factura f INNER JOIN usuario u ON u.id=f.id_usuario_registro'.($ddDonde ? ' WHERE '.implode(' AND ',$ddDonde) : '');
$ddTotal=0;$ddFilas=[];$ddTamano=4;$ddPagina=1;$ddPaginas=1;
if (!$ddErrores) {
    $ddCuenta=$conexion->prepare('SELECT COUNT(*)'.$ddDesde);$ddCuenta->execute($ddParametros);$ddTotal=(int)$ddCuenta->fetchColumn();
    $ddPaginas=max(1,(int)ceil($ddTotal/$ddTamano));
    $ddPagina=max(1,min($ddPaginas,(int)($_GET['pagina'] ?? 1)));
    $ddOffset=($ddPagina-1)*$ddTamano;
    $ddLista=$conexion->prepare('SELECT f.id,f.numero_serie,f.monto,f.fecha_registro,f.estado,u.usuario'.$ddDesde.' ORDER BY f.fecha_registro DESC,f.id DESC LIMIT '.$ddTamano.' OFFSET '.$ddOffset);
    $ddLista->execute($ddParametros);$ddFilas=$ddLista->fetchAll();
}
$ddNumeroPagina=$ddPagina; // menu.php utiliza su propia variable de página activa.
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Panel de inicio · Don Diego</title>
<link rel="stylesheet" href="css/bootstrap-5.3.8/css/bootstrap.min.css">
<link rel="stylesheet" href="tema-don-diego.css?v=2">
<link rel="stylesheet" href="imagenes/bootstrap-icons-1.13.1/bootstrap-icons.min.css">
</head>
<body>
<?php require __DIR__.'/menu.php'; ?>
<main class="dd-panel">
    <header class="dd-heading">
        <div><h1>Panel de inicio</h1><p>Bienvenido, <?= ddEsc($_SESSION['usuario'] ?? 'Usuario') ?>. <?= $ddRepartidor ? 'Este es el resumen de tus facturas asignadas y tus cobros.' : 'Este es el resumen de tu panadería.' ?></p></div>
        <?php if ($ddPuedeEditar): ?><a class="dd-primary" href="agregar_factura.php"><span aria-hidden="true">＋</span> Nueva factura</a><?php endif; ?>
    </header>
    <section class="dd-stats" aria-label="Resumen de gestión">
        <div class="dd-stat"><div class="dd-stat-icon"><?= ddIcono('factura') ?></div><div><span>Facturas pendientes</span><strong><?= (int)$ddPendientes['cantidad'] ?></strong></div></div>
        <div class="dd-stat"><div class="dd-stat-icon"><?= ddIcono('pago') ?></div><div><span>Pagos del día</span><strong><?= ddDinero($ddPagosHoy) ?></strong></div></div>
        <div class="dd-stat"><div class="dd-stat-icon"><?= ddIcono('cartera') ?></div><div><span>Total por cobrar</span><strong><?= ddDinero($ddPendientes['total']) ?></strong></div></div>
    </section>
    <section class="dd-section" aria-labelledby="dd-facturas-titulo">
        <div class="dd-section-heading"><h2 id="dd-facturas-titulo">Últimas facturas</h2><a href="facturas.php">Ver todas <span aria-hidden="true">›</span></a></div>
        <form class="dd-filters" method="get" action="index.php">
            <div><label for="buscar">Buscar factura</label><input class="form-control" type="search" id="buscar" name="buscar" value="<?= ddEsc($ddFiltro['buscar']) ?>" placeholder="N.º de factura, serie o usuario" maxlength="100"></div>
            <div><label for="estado">Estado</label><select class="form-select" id="estado" name="estado"><option value="">Todos</option><?php foreach ($ddEstados as $ddEstado): ?><option <?= $ddFiltro['estado']===$ddEstado ? 'selected' : '' ?> value="<?= ddEsc($ddEstado) ?>"><?= ddEsc($ddEstado) ?></option><?php endforeach; ?></select></div>
            <div><label for="fecha">Fecha de registro</label><input class="form-control" type="date" id="fecha" name="fecha" value="<?= ddEsc($ddFiltro['fecha']) ?>"></div>
            <button class="btn btn-dark" type="submit"><?= ddIcono('buscar') ?> Buscar</button><a class="btn btn-outline-secondary" href="index.php">Limpiar</a>
        </form>
        <?php if ($ddErrores): ?><div class="alert alert-danger" role="alert"><?= ddEsc(implode(' ',$ddErrores)) ?></div><?php endif; ?>
        <?php if ($ddFilas): ?>
        <div class="table-responsive"><table class="table table-hover align-middle"><caption class="visually-hidden">Últimas facturas registradas</caption><thead><tr><th scope="col">Factura</th><th scope="col">Usuario</th><th scope="col">Fecha</th><th scope="col" class="text-end">Monto</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead><tbody>
        <?php foreach ($ddFilas as $ddFila): ?>
            <tr><td><span class="dd-serial"><?= ddEsc($ddFila['numero_serie']) ?></span><small class="d-block text-muted">N.º <?= (int)$ddFila['id'] ?></small></td><td><?= ddEsc($ddFila['usuario']) ?></td><td class="text-nowrap"><?= ddEsc(date('d/m/Y',strtotime($ddFila['fecha_registro']))) ?></td><td class="text-end text-nowrap"><?= ddDinero($ddFila['monto']) ?></td><td><span class="badge <?= $ddFila['estado']==='Cobrado' ? 'bg-success' : ($ddFila['estado']==='Pendiente' ? 'bg-warning' : 'bg-danger') ?>"><?= ddEsc($ddFila['estado']) ?></span></td><td><div class="dd-actions"><a class="dd-action" href="facturas.php?numero_factura=<?= (int)$ddFila['id'] ?>" aria-label="Ver factura <?= (int)$ddFila['id'] ?>" title="Ver factura"><?= ddIcono('ver') ?></a><?php if ($ddPuedeEditar): ?><a class="dd-action" href="editar_factura.php?id=<?= (int)$ddFila['id'] ?>" aria-label="Editar factura <?= (int)$ddFila['id'] ?>" title="Editar factura"><?= ddIcono('editar') ?></a><?php endif; ?></div></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
        <div class="dd-table-bottom"><span>Mostrando <?= ($ddNumeroPagina-1)*$ddTamano+1 ?>–<?= min($ddNumeroPagina*$ddTamano,$ddTotal) ?> de <?= $ddTotal ?> facturas</span>
        <nav class="dd-pagination" aria-label="Páginas de facturas"><?php if ($ddNumeroPagina>1): ?><a href="?<?= ddEsc(http_build_query(array_merge($ddFiltro,['pagina'=>$ddNumeroPagina-1]))) ?>" aria-label="Página anterior">‹</a><?php endif; ?><?php for ($ddP=max(1,$ddNumeroPagina-1);$ddP<=min($ddPaginas,$ddNumeroPagina+1);$ddP++): ?><?php if ($ddP===$ddNumeroPagina): ?><span aria-current="page"><?= $ddP ?></span><?php else: ?><a href="?<?= ddEsc(http_build_query(array_merge($ddFiltro,['pagina'=>$ddP]))) ?>"><?= $ddP ?></a><?php endif; ?><?php endfor; ?><?php if ($ddNumeroPagina<$ddPaginas): ?><a href="?<?= ddEsc(http_build_query(array_merge($ddFiltro,['pagina'=>$ddNumeroPagina+1]))) ?>" aria-label="Página siguiente">›</a><?php endif; ?></nav></div>
        <?php elseif (!$ddErrores): ?><div class="dd-empty"><?= ddIcono('factura') ?><p class="mt-3 mb-0">No hay facturas para mostrar<?= array_filter($ddFiltro) ? ' con estos filtros' : '' ?>.</p></div><?php endif; ?>
    </section>
</main>
<?php require __DIR__.'/pie_pagina.php'; ?>
</body></html>
