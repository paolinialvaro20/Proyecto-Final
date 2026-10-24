<!--
<nav>
    <a href="crear_usuario.php">Nuevo usuario</a> |
    <a href="crear_rol_usuario.php">Crear ROL</a> |
    <a href="editar_rol_usuario.php">Editar ROL usuario</a> |
    <a href="eliminar_rol.php">Eliminar ROL usuario</a> |
    <a href="editar_usuario.php">Editar usuario</a> |
    <a href="eliminar_usuario.php">Eliminar Usuario</a> |
    <a href="cambiar_estado_usuario.php">Cambiar estado usuario</a> |
    <a href="facturas.php">Listar facturas</a> |
    <a href="agregar_factura.php">Ingresar factura</a> |
    <a href="listar_observaciones.php">Listar observaciones</a> |
    <a href="editar_factura.php">Editar factura</a> |
    <a href="agregar_observaciones.php">Agregar observaciones</a> |
    <a href="cambiar_estado_factura.php">Cambiar estado factura</a> |
    <a href="cerrar_sesion.php">Cerrar sesión</a>
</nav>

<hr>
-->
<?php
if (isset($_SESSION["id_usuario"])) {
    $sqlUsuario = "SELECT usuario FROM usuario WHERE id = ?";
    $stmtUsuario = $conexion->prepare($sqlUsuario);
    $stmtUsuario->execute([$_SESSION["id_usuario"]]);
    $usuarioLogueado = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
}
?>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <!-- Nombre / Logo -->
        <a class="navbar-brand flex-grow-1" href="https://www.dondiegouy.com/"><img src="imagenes/logo.png" alt="Panaderia" class="d-inline-block align-text-top logo-menu"></a>
        <!-- Botón que aparece en celulares -->
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuNavegacion" aria-controls="menuNavegacion" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menú lateral -->
        <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="menuNavegacion" aria-labelledby="menuNavegacionLabel">
            <!-- Encabezado del menú lateral -->
            <div class="offcanvas-header">
                <h5 class="offcanvas-title fw-bold position-absolute start-50 translate-middle-x" id="menuNavegacionLabel">MENÚ</h5>
                <button  type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
            </div>

            <!-- Contenido del menú -->
            <div class="offcanvas-body justify-content-center">
                <ul class="navbar-nav flex-grow-1 pe-3 gap-4 fs-5">

                    <!-- Menú Usuario -->
                    <li class="nav-item dropdown nav-underline">
                        <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person"></i> Usuario</a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="listar_usuario.php"><i class="bi bi-people"></i> Listar usuarios</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="crear_usuario.php"><i class="bi bi-person-plus"></i> Nuevo usuario</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="editar_usuario.php"><i class="bi bi-pencil"></i> Editar usuario</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="cambiar_estado_usuario.php"><i class="bi bi-arrow-repeat"></i> Cambiar estado</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            
                            <!-- Menú Rol -->
                             <li>
                                <a class="dropdown-item" href="listar_roles.php"><i class="bi bi-list-check"></i> Listar roles</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="crear_rol_usuario.php"><i class="bi bi-person-plus"></i> Nuevo rol</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="editar_rol_usuario.php"><i class="bi bi-pencil"></i> Editar rol</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="eliminar_rol.php"><i class="bi bi-person-x"></i> Eliminar rol</a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- Menú Facturas -->
                    <li class="nav-item dropdown nav-underline">
                        <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-receipt"></i> Facturas</a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="facturas.php"><i class="bi bi-list"></i> Listar factura</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="agregar_factura.php"><i class="bi bi-plus-circle"></i> Ingresar factura</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="editar_factura.php"><i class="bi bi-pencil"></i> Editar factura</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="cambiar_estado_factura.php"><i class="bi bi-arrow-repeat"></i> Cambiar estado</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>

                             <!-- Menú Observaciones -->
                            <li>
                                <a class="dropdown-item" href="listar_observaciones.php"><i class="bi bi-chat-left-text"></i> Listar observaciones</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="agregar_observaciones.php"><i class="bi bi-journal-plus"></i> Agregar observaciones</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            
                            <!-- Menú Pagos -->
                            <li>
                                <a class="dropdown-item" href="listar_pagos.php"><i class="bi bi-list-ul"></i> Listar pagos</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="registrar_pago.php"><i class="bi bi-cash-stack"></i> Registrar pagos</a>
                            </li>
                        </ul>
                    </li>

                    <!-- Menú Liquidaciones -->
                    <li class="nav-item dropdown nav-underline">
                        <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-calculator"></i> Liquidaciones</a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="liquidacion_diaria.php"><i class="bi bi-calendar-check"></i> Liquidación diaria</a>
                            </li>
                        </ul>
                    </li> 
                                                         
                    <!-- Menú Auditoría -->
                    <li class="nav-item nav-underline">
                        <a class="nav-link fw-bold d-inline-flex text-nowrap" href="auditoria.php"><i class="bi bi-journal-text me-2"></i> Auditoría</a>
                    </li>

                    <!-- Usuario logueado -->
                    <li class="nav-item dropdown ms-auto d-none d-xxl-block">
                       <a class="nav-link dropdown-toggle fw-bold usuario-menu" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-fill"></i>
                            <?= htmlspecialchars($usuarioLogueado["usuario"]) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                             <li>
                                <a href="cambiar_contraseña.php" class="dropdown-item"><i class="bi bi-shield-lock-fill"></i> Cambiar contraseña</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="cerrar_sesion.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
                            </li>
                        </ul>
                    </li>
                    <li><hr class="border-light"></li>

                    <!-- Usuario logueado en la parte inferior del Offcanvas -->
                    <li class="nav-item dropdown mt-auto pe-3 fs-5 d-xxl-none">
                       <a class="nav-link dropdown-toggle fw-bold usuario-menu" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-fill"></i>
                            <?= htmlspecialchars($usuarioLogueado["usuario"]) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="cambiar_contraseña.php" class="dropdown-item"><i class="bi bi-shield-lock-fill"></i> Cambiar contraseña</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="cerrar_sesion.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
                            </li>   
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>