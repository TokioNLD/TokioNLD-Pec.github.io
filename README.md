# Depo Xona (PHP/MySQL/XAMPP)

Aplicación para Dirección de Deportes de Xonacatlán con:
- PHP, MySQL, HTML, CSS, JS (Bootstrap opcional)
- BD `depo_xona` creada en `/db/crear_base_datos.php`
- Tablas: `cursos`, `usuarios`, `inscripciones`, `eventos`, `archivos`,`administradores`
- Admin CRUD: cursos, eventos, archivos, inscripciones, usuarios
- Front: ver cursos, ver eventos, inscribirse, ver multimedia

## Ejecutar
1. Iniciar Apache y MySQL en XAMPP.
2. Navegar `http://localhost/Pec/db/crear_base_datos.php`.
3. Ir al sitio: `http://localhost/Pec` redirige a `public/index.php`.
4. Admin: `http://localhost/Pec/admin/login.php` (usuario: `Evan`, contraseña: `1234`).

## Estructura
- `config/conexion.php` - conexión PDO a `depo_xona`
- `config/helpers.php` - helpers auth y sanitización
- `db/crear_base_datos.php` - crea DB y tablas con relaciones
- `admin/` - CRUD y panel admin
- `public/` - vistas de usuario
- `assets/css/style.css` - estilos básicos
- `assets/app.js` - validación JS

## Recomendado
- Revisar/perfeccionar validaciones server-side (filter_input, prepared statements ya usas)
- Agregar CSRF tokens en formularios admin
- Manejar sesiones seguras
- Validar tamaño y tipo de archivos al subir multimedia
