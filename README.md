# Sistema de Gestión de Deudas y Pagos – Panadería Don Diego

## Descripción del proyecto
El **Sistema de Gestión de Deudas y Pagos de Panadería Don Diego** es una aplicación web desarrollada con el objetivo de facilitar la administración y el control de las facturas, deudas y pagos de los clientes.
El sistema permite reemplazar el registro manual de deudas en papel por una herramienta digital que centraliza la información, reduce errores y facilita el seguimiento de los movimientos realizados.
Está diseñado para ser utilizado por diferentes perfiles de usuarios, como administradores, cajeros y repartidores, cada uno con permisos y funcionalidades según su rol.

## Funcionalidades principales
- **Gestión de usuarios:** creación, edición, activación y desactivación de usuarios.
- **Gestión de roles:** administración de roles y permisos.
- **Gestión de facturas:** registro, asignación, consulta y modificación del estado de las facturas.
- **Gestión de pagos:** registro y consulta de pagos, con diferentes formas de pago.
- **Gestión de observaciones:** registro de comentarios asociados a las facturas.
- **Gestión de liquidaciones:** control y confirmación de liquidaciones diarias de cajeros y repartidores.
- **Auditoría:** registro de las acciones realizadas por los usuarios para mantener un historial de las operaciones del sistema.

## Tecnologías utilizadas
- **PHP:** desarrollo de la lógica del sistema.
- **MySQL:** almacenamiento y gestión de la base de datos.
- **HTML5:** estructura de las páginas web.
- **CSS3:** estilos y diseño visual.
- **JavaScript:** funcionalidades e interacciones.
- **Bootstrap 5.3.8:** diseño adaptable y componentes de interfaz.
- **Bootstrap Icons 1.13.1:** iconografía.

## Dependencias
El proyecto utiliza Bootstrap Icons 1.13.1 para mostrar los iconos de la interfaz.
Es necesario descargar la versión correspondiente desde el sitio oficial:
- **Bootstrap Icons 1.13.1:** [Descargar desde GitHub](https://github.com/twbs/icons/releases/tag/v1.13.1)
Una vez descargado, extraer los archivos y colocar la carpeta dentro del directorio `imagenes/` del proyecto, respetando la siguiente estructura:
```text
Panaderia/
├── imagenes/
│   └── bootstrap-icons-1.13.1/
│       ├── bootstrap-icons.min.css
│       └── fonts/
│           ├── bootstrap-icons.woff
│           └── bootstrap-icons.woff2
```
Es importante conservar la estructura de carpetas para que los iconos se visualicen correctamente.

## Requisitos
Para ejecutar el proyecto localmente se necesita:
- XAMPP.
- PHP.
- MySQL o MariaDB.
- Navegador web.
- Git (opcional, para clonar el repositorio).

## Instalación
1. Clonar el repositorio:

   ```bash
   git clone https://github.com/paolinialvaro20/Proyecto-Final.git
   ```

2. Copiar la carpeta del proyecto dentro del directorio `htdocs` de XAMPP.
3. Iniciar los servicios Apache y MySQL desde el panel de control de XAMPP.
4. Crear una base de datos en phpMyAdmin.
5. Importar el archivo SQL de la base de datos, si se encuentra incluido en el repositorio.
6. Configurar los datos de conexión en el archivo `conexion.php`.
7. Abrir el navegador y acceder a:

   ```text
   http://localhost/Panaderia/
   ```

## Seguridad y control de acceso
El sistema incorpora mecanismos de autenticación mediante usuario y contraseña, almacenamiento seguro de contraseñas mediante hash y control de acceso según los roles asignados.
Además, cuenta con un módulo de auditoría para registrar las operaciones realizadas por los usuarios.

## Objetivo del proyecto
Desarrollar una herramienta que permita mejorar la organización, el seguimiento y el control de las deudas y los pagos de Panadería Don Diego, facilitando las tareas administrativas y operativas del negocio.

## Autor
Desarrollado como proyecto de aplicación web para **Panadería Don Diego**.
